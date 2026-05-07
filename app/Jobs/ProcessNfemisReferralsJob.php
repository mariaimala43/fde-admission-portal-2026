<?php

namespace App\Jobs;

use App\Enums\NfemisStatus;
use App\Models\Admission;
use App\Models\Institution;
use App\Models\InstitutionClass;
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
 *   Status = 20  (NfemisStatus::APPROVED — NFEMIS has approved the referral for FDE)
 *   Remarks IS NULL  (we store fde_ref_id in Remarks after pickup to prevent re-processing)
 *
 * On success writes back:
 *   Status  → 21 (NfemisStatus::RECEIVED)
 *   Remarks → FDE ref_id  e.g. "FDE-20260508-ABC12345"
 */
class ProcessNfemisReferralsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $count = 0;

        try {
            $referrals = DB::connection('nfemis')
                ->table('StudentAdmissionRegister as sar')
                ->join('Student as st',  'st.StudentID',  '=', 'sar.StudentID')
                ->join('School  as sc',  'sc.SchoolID',   '=', 'sar.SchoolID')
                ->leftJoin('OutOfSchoolChild as oosc', function ($join) {
                    // Best-effort phone lookup — no direct FK between Student and OutOfSchoolChild
                    $join->on('oosc.StudentName', '=', 'st.StudentName')
                         ->on('oosc.VillageId',   '=', 'st.VillageID');
                })
                ->where('sar.Status', NfemisStatus::APPROVED)   // Status = 20
                ->whereNull('sar.Remarks')                       // Not yet picked up
                ->orderBy('sar.DateOfAdmission')
                ->select([
                    'sar.StudentEnrollmentID',
                    'sar.SchoolID       as nfemis_school_id',
                    'sar.StudentID      as nfemis_student_id',
                    'sar.AdmissionNo',
                    'sar.DateOfAdmission',
                    'sar.ClassID',
                    'st.StudentName     as child_name',
                    'st.GurdianName     as parent_name',
                    'st.Gender          as child_gender',
                    'st.DOBDigits       as child_dob',
                    'st.ParentCNIC      as parent_cnic',
                    'st.VillageID       as village_id',
                    'sc.SchoolCode      as emis_code',
                    'sc.SchoolName      as school_name',
                    'oosc.ContactNumber as parent_contact',
                ])
                ->get();

            foreach ($referrals as $referral) {
                try {
                    // a. Idempotency: skip if already imported
                    if (Admission::where('nfemis_referral_id', $referral->StudentEnrollmentID)->exists()) {
                        DB::connection('nfemis')
                            ->table('StudentAdmissionRegister')
                            ->where('StudentEnrollmentID', $referral->StudentEnrollmentID)
                            ->update(['Remarks' => 'DUPLICATE', 'Status' => NfemisStatus::RECEIVED]);
                        continue;
                    }

                    // b. Look up FDE institution by NFEMIS SchoolID (unique integer — reliable match)
                    $institution = Institution::where('nfemis_school_id', $referral->nfemis_school_id)
                        ->where('is_active', true)
                        ->first();

                    if (!$institution) {
                        Log::channel('nfemis_sync')->warning('ProcessNfemisReferralsJob: FDE institution not found for NFEMIS school', [
                            'enrollment_id'    => $referral->StudentEnrollmentID,
                            'nfemis_school_id' => $referral->nfemis_school_id,
                            'school_name'      => $referral->school_name,
                        ]);
                        continue;
                    }

                    // c. Check vacancy via institution_classes (available seats)
                    $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
                    $instClass  = InstitutionClass::where('institution_id', $institution->id)
                        ->where('is_active', true)
                        ->whereHas('classModel', fn($q) => $q->where('name', 'like', '%' . $referral->ClassID . '%'))
                        ->first();

                    if (!$instClass || ($instClass->total_seats - $instClass->existing_enrollment) <= 0) {
                        Log::channel('nfemis_sync')->warning('ProcessNfemisReferralsJob: no vacancy', [
                            'enrollment_id'  => $referral->StudentEnrollmentID,
                            'institution_id' => $institution->id,
                            'class_id'       => $referral->ClassID,
                        ]);
                        continue;
                    }

                    // d. Create FDE admission record
                    $admission = new Admission();
                    $refId     = $admission->generateRefId();

                    $admission = Admission::create([
                        'ref_id'             => $refId,
                        'nfemis_referral_id' => $referral->StudentEnrollmentID,
                        'child_name'         => $referral->child_name,
                        'child_dob'          => $referral->child_dob,
                        'child_gender'       => $referral->child_gender,
                        'parent_name'        => $referral->parent_name,
                        'parent_contact'     => $referral->parent_contact,
                        'institution_id'     => $institution->id,
                        'class_name'         => $referral->ClassID,
                        'referral_date'      => $referral->DateOfAdmission,
                        'status'             => 'pending',
                    ]);

                    // f. Dispatch SMS to principal + parent
                    SendAdmissionSmsJob::dispatch($admission);

                    // g. Write fde_ref_id and status back to NFEMIS
                    DB::connection('nfemis')
                        ->table('StudentAdmissionRegister')
                        ->where('StudentEnrollmentID', $referral->StudentEnrollmentID)
                        ->update([
                            'Remarks'     => $refId,                   // e.g. "FDE-20260508-ABC12345"
                            'Status'      => NfemisStatus::RECEIVED,   // 21
                            'LastUpdated' => now(),
                        ]);

                    $count++;

                    Log::channel('nfemis_sync')->info('ProcessNfemisReferralsJob: imported', [
                        'enrollment_id' => $referral->StudentEnrollmentID,
                        'ref_id'        => $refId,
                        'child'         => $referral->child_name,
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
            Log::channel('nfemis_sync')->error('ProcessNfemisReferralsJob: NFEMIS connection error', [
                'error' => $e->getMessage(),
            ]);
        }

        Log::channel('nfemis_sync')->info("ProcessNfemisReferralsJob: processed {$count} referrals");
    }
}
