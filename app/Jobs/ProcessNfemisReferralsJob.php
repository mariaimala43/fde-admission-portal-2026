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

class ProcessNfemisReferralsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $count = 0;

        try {
            $referrals = DB::connection('nfemis')
                ->table('school_referrals')
                ->where('status', 'approved')
                ->whereNull('fde_ref_id')
                ->orderBy('referral_date')
                ->get();

            foreach ($referrals as $referral) {
                try {
                    // a. Skip if already imported
                    if (Admission::where('nfemis_referral_id', $referral->id)->exists()) {
                        continue;
                    }

                    // b. Look up school by EMIS code
                    $school = School::where('emis_code', $referral->emis_school_code)->first();
                    if (!$school) {
                        Log::channel('nfemis_sync')->warning('ProcessNfemisReferralsJob: school not found', [
                            'referral_id'    => $referral->id,
                            'emis_code'      => $referral->emis_school_code ?? null,
                        ]);
                        continue;
                    }

                    // c. Check vacancy in school_seats
                    $seat = SchoolSeat::where('school_id', $school->id)
                        ->where('class_name', $referral->class_name)
                        ->vacant()
                        ->first();

                    if (!$seat) {
                        Log::channel('nfemis_sync')->warning('ProcessNfemisReferralsJob: no vacancy', [
                            'referral_id' => $referral->id,
                            'school_id'   => $school->id,
                            'class_name'  => $referral->class_name ?? null,
                        ]);
                        continue;
                    }

                    // d. Generate ref_id, create Admission, increment occupied_seats
                    $admission = new Admission();
                    $refId     = $admission->generateRefId();

                    $admission = Admission::create([
                        'ref_id'              => $refId,
                        'nfemis_referral_id'  => $referral->id,
                        'child_name'          => $referral->child_name,
                        'child_dob'           => $referral->child_dob,
                        'child_gender'        => $referral->child_gender,
                        'parent_name'         => $referral->parent_name,
                        'parent_contact'      => $referral->parent_contact,
                        'school_id'           => $school->id,
                        'class_name'          => $referral->class_name,
                        'referral_date'       => $referral->referral_date,
                        'status'              => 'pending',
                    ]);

                    $seat->increment('occupied_seats');

                    // e. Dispatch SMS job
                    SendAdmissionSmsJob::dispatch($admission);

                    // f. Update NFEMIS: mark as submitted with our ref_id
                    DB::connection('nfemis')
                        ->table('school_referrals')
                        ->where('id', $referral->id)
                        ->update([
                            'fde_ref_id' => $refId,
                            'status'     => 'submitted',
                        ]);

                    $count++;
                } catch (\Exception $e) {
                    Log::channel('nfemis_sync')->error('ProcessNfemisReferralsJob: error processing referral', [
                        'referral_id' => $referral->id ?? null,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // NFEMIS unavailability must not crash the scheduler
            Log::channel('nfemis_sync')->error('ProcessNfemisReferralsJob: NFEMIS connection error', [
                'error' => $e->getMessage(),
            ]);
        }

        Log::channel('nfemis_sync')->info("ProcessNfemisReferralsJob: Processed {$count} referrals");
    }
}
