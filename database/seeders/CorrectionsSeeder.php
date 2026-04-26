<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Institution;
use App\Models\Classes;
use App\Models\InstitutionClass;
use App\Models\DailyAdmission;
use App\Models\AdmissionCorrection;
use App\Models\AcademicYear;
use Carbon\Carbon;

/**
 * STEP 11 — Admission Corrections (15 records)
 *
 *   5 pending  — awaiting FDE review
 *   4 approved — corrections accepted
 *   3 rejected — corrections denied
 *   3 recent   — submitted today / yesterday
 */
class CorrectionsSeeder extends Seeder
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

        // Gather verified DailyAdmissions to base corrections on
        $verifiedDAs = DailyAdmission::whereIn('institution_id', $this->institutionIds)
            ->where('academic_year_id', $academicYear->id)
            ->whereIn('status', ['verified', 'locked'])
            ->inRandomOrder()
            ->limit(15)
            ->get();

        if ($verifiedDAs->isEmpty()) {
            $this->command->warn('  ⚠ No verified DailyAdmissions found for corrections. Run MonitoringSeeder first.');
            return;
        }

        $corrections = [
            // 5 pending
            ['status' => 'pending', 'daysAgo' => 8],
            ['status' => 'pending', 'daysAgo' => 6],
            ['status' => 'pending', 'daysAgo' => 5],
            ['status' => 'pending', 'daysAgo' => 4],
            ['status' => 'pending', 'daysAgo' => 3],
            // 4 approved
            ['status' => 'approved', 'daysAgo' => 20],
            ['status' => 'approved', 'daysAgo' => 18],
            ['status' => 'approved', 'daysAgo' => 15],
            ['status' => 'approved', 'daysAgo' => 12],
            // 3 rejected
            ['status' => 'rejected', 'daysAgo' => 25],
            ['status' => 'rejected', 'daysAgo' => 22],
            ['status' => 'rejected', 'daysAgo' => 14],
            // 3 recent
            ['status' => 'pending', 'daysAgo' => 1],
            ['status' => 'pending', 'daysAgo' => 0],
            ['status' => 'pending', 'daysAgo' => 0],
        ];

        $reasons = [
            'Data entry error — morning boys count was incorrect. Corrected after double-checking attendance register.',
            'Class teacher reported wrong figure. Actual admission count updated after verification.',
            'Evening shift count was missing from original entry. Added missing students.',
            'OOSC tally was entered under regular admissions. Corrected to proper category.',
            'Clerical error — numbers were transposed. Corrected per admission register.',
        ];

        $rejectReasons = [
            'Original figures appear correct based on attendance register. Correction not justified.',
            'Insufficient documentation provided to support correction request.',
            'Time window for correction has passed. Data is already reported to higher offices.',
        ];

        $created = 0;

        foreach ($corrections as $i => $config) {
            $da = $verifiedDAs->get($i);
            if (! $da) break;

            $institution = Institution::find($da->institution_id);
            $hoiUser     = $institution?->users()
                ->whereHas('roles', fn ($q) => $q->where('name', 'hoi'))
                ->where('is_active', true)
                ->first() ?? $fdeAdmin;

            // Generate plausible "old" and "new" values based on the DA
            $oldMb = $da->morning_boys;
            $oldMg = $da->morning_girls;
            $oldEb = $da->evening_boys;
            $oldEg = $da->evening_girls;

            // New values: slightly different
            $newMb = max(0, $oldMb + rand(-2, 3));
            $newMg = max(0, $oldMg + rand(-1, 2));
            $newEb = max(0, $oldEb + rand(0, 1));
            $newEg = max(0, $oldEg + rand(0, 1));

            $requestedAt = now()->subDays($config['daysAgo']);
            $reviewedAt  = $config['status'] !== 'pending'
                ? $requestedAt->copy()->addDays(rand(1, 2))
                : null;

            AdmissionCorrection::create([
                'institution_id'        => $da->institution_id,
                'class_id'              => $da->class_id,
                'academic_year_id'      => $academicYear->id,
                'admission_date'        => $da->admission_date,
                'reason'                => $reasons[$i % count($reasons)],
                // Old values
                'old_morning_boys'      => $oldMb,
                'old_morning_girls'     => $oldMg,
                'old_evening_boys'      => $oldEb,
                'old_evening_girls'     => $oldEg,
                'old_morning_oosc_boys' => $da->morning_oosc_boys,
                'old_morning_oosc_girls'=> $da->morning_oosc_girls,
                'old_morning_p2p_boys'  => $da->morning_p2p_boys,
                'old_morning_p2p_girls' => $da->morning_p2p_girls,
                'old_evening_oosc_boys' => $da->evening_oosc_boys,
                'old_evening_oosc_girls'=> $da->evening_oosc_girls,
                'old_evening_p2p_boys'  => $da->evening_p2p_boys,
                'old_evening_p2p_girls' => $da->evening_p2p_girls,
                // New values
                'new_morning_boys'      => $newMb,
                'new_morning_girls'     => $newMg,
                'new_evening_boys'      => $newEb,
                'new_evening_girls'     => $newEg,
                'new_morning_oosc_boys' => $da->morning_oosc_boys,
                'new_morning_oosc_girls'=> $da->morning_oosc_girls,
                'new_morning_p2p_boys'  => $da->morning_p2p_boys,
                'new_morning_p2p_girls' => $da->morning_p2p_girls,
                'new_evening_oosc_boys' => $da->evening_oosc_boys,
                'new_evening_oosc_girls'=> $da->evening_oosc_girls,
                'new_evening_p2p_boys'  => $da->evening_p2p_boys,
                'new_evening_p2p_girls' => $da->evening_p2p_girls,
                // Status
                'status'       => $config['status'],
                'requested_by' => $hoiUser?->id,
                'reviewed_by'  => $config['status'] !== 'pending' ? $fdeAdmin?->id : null,
                'fde_note'     => match ($config['status']) {
                    'approved' => 'Correction verified and approved. Records updated.',
                    'rejected' => $rejectReasons[$i % count($rejectReasons)],
                    default    => null,
                },
                'reviewed_at'  => $reviewedAt,
                'created_at'   => $requestedAt,
                'updated_at'   => $reviewedAt ?? $requestedAt,
            ]);

            $created++;
        }

        $this->command->line("  → CorrectionsSeeder: {$created} correction requests created");
    }
}
