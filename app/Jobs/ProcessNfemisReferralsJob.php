<?php

namespace App\Jobs;

use App\Models\Admission;
use App\Models\School;
use App\Models\SchoolSeat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Polls NFEMIS `StudentAdmissionRegister` every 5 minutes.
 *
 * Picks up rows where:
 *   - Status = 'Approved'  (NFEMIS has approved the referral)
 *   - Remarks IS NULL      (we store our fde_ref_id in Remarks after pickup)
 *
 * Joins Student for child info, School for EMIS code.
 *
 * NOTE: ContactNumber (for SMS) lives in OutOfSchoolChild, not Student.
 * We attempt a lookup by StudentName + VillageID. If not found, SMS is
 * sent to the school principal only.
 */
class ProcessNfemisReferralsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $count = 0;

        try {
            // ── Pull approved referrals with child + school info ──────────────
            $referrals = DB::connection('nfemis')
                ->table('StudentAdmissionRegister as sar')
                ->join('Student as st',   'st.StudentID', '=', 'sar.StudentID')
                ->join('School  as sc',   'sc.SchoolID',  '=', 'sar.SchoolID')
                ->leftJoin('OutOfSchoolChild as oosc', function ($join) {
                    // Best-effort match for ContactNumber (NFEMIS has no direct FK)
                    $join->on('oosc.StudentName', '=', 'st.StudentName')
                         ->on('oosc.VillageId',   '=', 'st.VillageID');
                })
                ->where('sar.Status', 'Approved')
                ->whereNull('sar.Remarks')          // Remarks = NULL means not yet picked up
                ->orderBy('sar.DateOfAdmission')
                ->select([
                    // Enrollment
                    'sar.StudentEnrollmentID',
                    'sar.SchoolID       as nfemis_school_id',
                    'sar.StudentID      as nfemis_student_id',
                    'sar.AdmissionNo',
                    'sar.DateOfAdmission',
                    'sar.ClassID',
                    'sar.Status',
                    // Child details
                    'st.StudentName     as child_name',
                    'st.GurdianName     as parent_name',    // Note: NFEMIS typo — GurdianName
                    'st.Gender          as child_gender',
                    'st.DOBDigits       as child_dob',
                    'st.ParentCNIC      as parent_cnic',
                    'st.VillageID       as village_id',
                    // School
                    'sc.SchoolCode      as emis_code',
                    'sc.SchoolName      as school_name',
                    // Contact (from OutOfSchoolChild — may be NULL)
                    'oosc.ContactNumber as parent_contact',
                ])
                ->get();

            foreach ($referrals as $referral) {
                try {
                    // a. Idempotency: skip if already imported
                    if (Admission::where('nfemis_referral_id', $referral->StudentEnrollmentID)->exists()) {
                        // Mark it so we don't keep picking it up
                        DB::connection('nfemis')
                            ->table('StudentAdmissionRegister')
                            ->where('StudentEnrollmentID', $referral->StudentEnrollmentID)
                            ->update(['Remarks' => 'DUPLICATE_SKIP']);
                        continue;
                    }

                    // b. Look up FDE school by EMIS code (SchoolCode in NFEMIS)
                    $school = School::where('emis_code', $referral->emis_code)->first();
                    if (!$school) {
                        Log::channel('nfemis_sync')->warning('ProcessNfemisReferralsJob: FDE school not found for EMIS code', [
                            'enrollment_id' => $referral->StudentEnrollmentID,
                            'emis_code'     => $referral->emis_code,
                            'school_name'   => $referral->school_name,
                        ]);
                        continue;
                    }

                    // c. Check vacancy — match by ClassID
                    $seat = SchoolSeat::where('school_id', $school->id)
                        ->where('class_name', $referral->ClassID)
                        ->vacant()
                        ->first();

                    if (!$seat) {
                        Log::channel('nfemis_sync')->warning('ProcessNfemisReferralsJob: no vacancy', [
                            'enrollment_id' => $referral->StudentEnrollmentID,
                            'school_id'     => $school->id,
                            'class_id'      => $referral->ClassID,
                        ]);
                        continue;
                    }

                    // d. Create Admission in FDE
                    $admission = new Admission();
                    $refId     = $admission->generateRefId();

                    $admission = Admission::create([
                        'ref_id'             => $refId,
                        'nfemis_referral_id' => $referral->StudentEnrollmentID,
                        'child_name'         => $referral->child_name,
                        'child_dob'          => $referral->child_dob,
                        'child_gender'       => $referral->child_gender,
                        'parent_name'        => $referral->parent_name,
                        'parent_contact'     => $referral->parent_contact,   // may be null
                        'school_id'          => $school->id,
                        'class_name'         => $referral->ClassID,
                        'referral_date'      => $referral->DateOfAdmission,
                        'status'             => 'pending',
                    ]);

                    // e. Increment occupied seats
                    $seat->increment('occupied_seats');

                    // f. Dispatch SMS to principal + parent
                    SendAdmissionSmsJob::dispatch($admission);

                    // g. Write fde_ref_id back to NFEMIS (Remarks column)
                    //    and update Status → 'Submitted'
                    DB::connection('nfemis')
                        ->table('StudentAdmissionRegister')
                        ->where('StudentEnrollmentID', $referral->StudentEnrollmentID)
                        ->update([
                            'Remarks'     => $refId,        // Store our ref_id here
                            'Status'      => 'Submitted',   // Tell NFEMIS we picked it up
                            'LastUpdated' => now(),
                        ]);

                    $count++;

                    Log::channel('nfemis_sync')->info('ProcessNfemisReferralsJob: referral imported', [
                        'enrollment_id' => $referral->StudentEnrollmentID,
                        'ref_id'        => $refId,
                        'child_name'    => $referral->child_name,
                        'school'        => $referral->school_name,
                    ]);

                } catch (\Exception $e) {
                    Log::channel('nfemis_sync')->error('ProcessNfemisReferralsJob: error on referral', [
                        'enrollment_id' => $referral->StudentEnrollmentID ?? null,
                        'error'         => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // NFEMIS unavailability must not crash the scheduler
            Log::channel('nfemis_sync')->error('ProcessNfemisReferralsJob: NFEMIS connection error', [
                'error' => $e->getMessage(),
            ]);
        }

        Log::channel('nfemis_sync')->info("ProcessNfemisReferralsJob: processed {$count} referrals");
    }
}
