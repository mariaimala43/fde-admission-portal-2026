<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DailyAdmission;
use App\Models\AdmissionMonitoring;
use App\Models\AcademicYear;
use Carbon\Carbon;

/**
 * STEP 10 — Admission Monitoring Records
 *
 * Creates AdmissionMonitoring rows for every verified DailyAdmission
 * across the 12 test institutions.
 *
 * Workflow state distribution (across verified records):
 *   20% → finalized        (doc_status=complete, merit=selected, test=passed)
 *   15% → doc_verification (doc_status=provisional, merit=selected, test=passed)
 *   15% → doc_verification (doc_status=affidavit_case, merit=selected, test=passed)
 *   15% → merit_confirmation (merit=pending, test=passed)
 *   10% → test_verification (test=conducted → passed)
 *   10% → test_verification (test=scheduled)
 *   10% → test_verification (test=not_required, skip to merit)
 *    5% → draft
 */
class MonitoringSeeder extends Seeder
{
    private array $institutionIds = [1, 3, 63, 65, 118, 120, 272, 197, 327, 433, 434, 436];

    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        if (! $academicYear) {
            $this->command->error('No active academic year.');
            return;
        }

        $fdeAdmin = User::whereHas('roles', fn ($q) => $q->where('name', 'fde_cell'))
            ->where('is_active', true)->first();

        $verifiedDAs = DailyAdmission::whereIn('institution_id', $this->institutionIds)
            ->where('academic_year_id', $academicYear->id)
            ->whereIn('status', ['verified', 'locked'])
            ->get();

        $created = 0;

        foreach ($verifiedDAs as $da) {
            // Skip if monitoring record already exists
            if (AdmissionMonitoring::where('daily_admission_id', $da->id)->exists()) {
                continue;
            }

            $scenario = $this->pickScenario();
            $payload  = $this->buildPayload($scenario, $da, $fdeAdmin, $academicYear);

            AdmissionMonitoring::create(array_merge([
                'daily_admission_id' => $da->id,
                'institution_id'     => $da->institution_id,
                'class_id'           => $da->class_id,
                'academic_year_id'   => $academicYear->id,
                'admission_date'     => $da->admission_date,
            ], $payload));

            $created++;
        }

        $this->command->line("  → MonitoringSeeder: {$created} monitoring records created");
    }

    /** Weighted random scenario selection */
    private function pickScenario(): string
    {
        $rand = rand(1, 100);
        return match (true) {
            $rand <= 20 => 'finalized',
            $rand <= 35 => 'doc_complete',
            $rand <= 50 => 'doc_provisional',
            $rand <= 65 => 'merit_pending',
            $rand <= 75 => 'test_passed',
            $rand <= 85 => 'test_scheduled',
            $rand <= 95 => 'test_not_required',
            default     => 'draft',
        };
    }

    private function buildPayload(string $scenario, DailyAdmission $da, ?User $admin, AcademicYear $year): array
    {
        $base = $da->admission_date->toDateString();

        return match ($scenario) {
            'finalized' => [
                'workflow_status'  => 'finalized',
                'test_status'      => 'passed',
                'test_updated_at'  => $base . ' 10:00:00',
                'test_updated_by'  => $admin?->id,
                'merit_status'     => 'selected',
                'merit_updated_at' => $base . ' 11:00:00',
                'merit_updated_by' => $admin?->id,
                'doc_status'       => 'complete',
                'doc_updated_at'   => $base . ' 13:00:00',
                'doc_updated_by'   => $admin?->id,
                'finalized_at'     => $base . ' 15:00:00',
                'finalized_by'     => $admin?->id,
            ],
            'doc_complete' => [
                'workflow_status'  => 'doc_verification',
                'test_status'      => 'passed',
                'test_updated_at'  => $base . ' 10:00:00',
                'test_updated_by'  => $admin?->id,
                'merit_status'     => 'selected',
                'merit_updated_at' => $base . ' 11:00:00',
                'merit_updated_by' => $admin?->id,
                'doc_status'       => 'complete',
                'doc_updated_at'   => $base . ' 13:00:00',
                'doc_updated_by'   => $admin?->id,
            ],
            'doc_provisional' => [
                'workflow_status'  => 'doc_verification',
                'test_status'      => 'passed',
                'test_updated_at'  => $base . ' 10:00:00',
                'test_updated_by'  => $admin?->id,
                'merit_status'     => 'selected',
                'merit_updated_at' => $base . ' 11:00:00',
                'merit_updated_by' => $admin?->id,
                'doc_status'       => 'provisional',
                'doc_updated_at'   => $base . ' 13:30:00',
                'doc_updated_by'   => $admin?->id,
            ],
            'merit_pending' => [
                'workflow_status'  => 'merit_confirmation',
                'test_status'      => 'passed',
                'test_updated_at'  => $base . ' 10:00:00',
                'test_updated_by'  => $admin?->id,
                'merit_status'     => 'pending',
                'doc_status'       => 'pending',
            ],
            'test_passed' => [
                'workflow_status' => 'test_verification',
                'test_status'     => 'passed',
                'test_updated_at' => $base . ' 10:00:00',
                'test_updated_by' => $admin?->id,
                'merit_status'    => 'pending',
                'doc_status'      => 'pending',
            ],
            'test_scheduled' => [
                'workflow_status' => 'test_verification',
                'test_status'     => 'pending',
                'merit_status'    => 'pending',
                'doc_status'      => 'pending',
            ],
            'test_not_required' => [
                'workflow_status'  => 'merit_confirmation',
                'test_status'      => 'not_required',
                'test_updated_at'  => $base . ' 09:00:00',
                'test_updated_by'  => $admin?->id,
                'merit_status'     => 'pending',
                'doc_status'       => 'pending',
            ],
            default => [ // draft
                'workflow_status' => 'draft',
                'test_status'     => 'pending',
                'merit_status'    => 'pending',
                'doc_status'      => 'pending',
            ],
        };
    }
}
