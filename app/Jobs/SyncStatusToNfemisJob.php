<?php

namespace App\Jobs;

use App\Models\Admission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncStatusToNfemisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [300, 900, 2700]; // 5 min, 15 min, 45 min
    }

    public function __construct(public Admission $admission)
    {
    }

    public function handle(): void
    {
        $admission = $this->admission;

        try {
            $enrollmentStatus = match ($admission->status) {
                'confirmed' => 'Enrolled',
                'rejected'  => 'Rejected',
                default     => null,
            };

            if ($enrollmentStatus === null) {
                Log::channel('nfemis_sync')->warning('SyncStatusToNfemisJob: unsupported status, skipping', [
                    'admission_id' => $admission->id,
                    'status'       => $admission->status,
                ]);
                return;
            }

            DB::connection('nfemis')
                ->table('school_referrals')
                ->where('fde_ref_id', $admission->ref_id)
                ->update([
                    'enrollment_status' => $enrollmentStatus,
                    'enrolled_at'       => $admission->status === 'confirmed' ? now() : null,
                    'status'            => 'processed',
                ]);

            $admission->update(['nfemis_synced_at' => now()]);

            Log::channel('nfemis_sync')->info('SyncStatusToNfemisJob: synced', [
                'admission_id'      => $admission->id,
                'ref_id'            => $admission->ref_id,
                'enrollment_status' => $enrollmentStatus,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('nfemis_sync')->error('SyncStatusToNfemisJob: DB connection error, will retry', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
            ]);
            // Re-throw to trigger the retry backoff
            throw $e;
        } catch (\Exception $e) {
            Log::channel('nfemis_sync')->error('SyncStatusToNfemisJob: unexpected error', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
