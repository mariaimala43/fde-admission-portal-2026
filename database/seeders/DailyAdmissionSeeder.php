<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Institution;
use App\Models\Classes;
use App\Models\InstitutionClass;
use App\Models\DailyAdmission;
use App\Models\AdmissionMonitoring;
use App\Models\AcademicYear;
use Carbon\Carbon;

/**
 * STEP 9 — Daily Admissions (30-day span)
 *
 * Extends the 7 days created by TestingSeeder to a full 30-day history.
 * Only creates entries that don't already exist (skip-if-exists).
 *
 * Variance scenarios:
 *   • Institutions 1, 433, 434  → high-fill schools (large class ranges)
 *   • Institutions 3, 65        → low-fill schools (small I-V primaries)
 *   • Institution 272 (Tarnaul) → skips today → appears in "Not Submitted" dashboard list
 *
 * Status distribution (from end of date array):
 *   fromEnd >= 3  → verified
 *   fromEnd == 2  → submitted
 *   fromEnd == 1  → returned  (first institution, first class only) | submitted for others
 *   fromEnd == 0  → draft
 *
 * Column naming: morning_p2p_boys / morning_p2p_girls (NOT p2g)
 */
class DailyAdmissionSeeder extends Seeder
{
    /** Institution IDs seeded by TestingSeeder */
    private array $institutionIds = [1, 3, 63, 65, 118, 120, 272, 197, 327, 433, 434, 436];

    /** Institution that skips today (shows in "Not Submitted" list) */
    private int $noSubmitTodayId = 272;

    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        if (! $academicYear) {
            $this->command->error('No active academic year. Run main seeders first.');
            return;
        }

        $fdeAdmin = User::whereHas('roles', fn ($q) => $q->where('name', 'fde_cell'))
            ->where('is_active', true)->first();

        $dates        = $this->buildWeekdayDates(30);
        $totalDays    = count($dates);
        $totalCreated = 0;

        foreach ($this->institutionIds as $instId) {
            $institution = Institution::find($instId);
            if (! $institution) continue;

            $hoiUser = $institution->users()
                ->whereHas('roles', fn ($q) => $q->where('name', 'hoi'))
                ->where('is_active', true)
                ->first();
            $submitter = $hoiUser ?? $fdeAdmin;

            $instClasses = InstitutionClass::where('institution_id', $instId)->get();

            foreach ($instClasses as $ic) {
                $classModel = Classes::find($ic->class_id);
                if (! $classModel || $classModel->is_ece) continue;

                // Running total of ALL admissions (seats used) this year — ALL statuses including draft.
                // Must match exactly what the dashboard counts as "Newly Admitted":
                // regular + OOSC + P2G combined. Including draft prevents DailyAdmissionSeeder
                // from adding on top of TestingSeeder's draft entries.
                $cumulRegular = (int) DailyAdmission::where('institution_id', $instId)
                    ->where('class_id', $ic->class_id)
                    ->where('academic_year_id', $academicYear->id)
                    ->get()
                    ->sum(fn ($da) => $da->morning_boys  + $da->morning_girls
                                    + $da->evening_boys  + $da->evening_girls
                                    + $da->oosc_boys     + $da->oosc_girls
                                    + $da->p2p_boys      + $da->p2p_girls);

                $isFirstEntry = true;

                foreach ($dates as $dayIndex => $admDate) {
                    // Skip today for the designated "not submitted" school
                    if ($instId === $this->noSubmitTodayId && $dayIndex === $totalDays - 1) {
                        continue;
                    }

                    // Skip if already exists (preserves TestingSeeder entries)
                    if (DailyAdmission::where('institution_id', $instId)
                        ->where('class_id', $ic->class_id)
                        ->where('admission_date', $admDate)
                        ->exists()) {
                        continue;
                    }

                    // Capacity guard — same logic as DailyAdmissionController
                    $available = max(0, $ic->total_seats - $ic->existing_enrollment - $cumulRegular);
                    if ($available === 0) break; // class is full

                    $nums = $this->generateNumbers(
                        $institution->gender,
                        $classModel->order,
                        $available,
                        $institution->has_matric_tech
                    );

                    $regularTotal = $nums['morning_boys'] + $nums['morning_girls']
                                  + $nums['evening_boys'] + $nums['evening_girls'];

                    $grandTotal = $regularTotal
                                + $nums['morning_oosc_boys']  + $nums['morning_oosc_girls']
                                + $nums['evening_oosc_boys']  + $nums['evening_oosc_girls']
                                + $nums['morning_p2p_boys']   + $nums['morning_p2p_girls']
                                + $nums['evening_p2p_boys']   + $nums['evening_p2p_girls'];

                    // Skip empty days
                    if ($grandTotal === 0 && $nums['matric_tech_count'] === 0) {
                        continue;
                    }

                    // Hard cap — grandTotal must not exceed remaining available seats
                    if ($grandTotal > $available) {
                        continue;
                    }

                    $status = $this->resolveStatus($dayIndex, $totalDays, $instId, $isFirstEntry);

                    $da = DailyAdmission::create([
                        'academic_year_id'    => $academicYear->id,
                        'institution_id'      => $instId,
                        'class_id'            => $ic->class_id,
                        'admission_date'      => $admDate,
                        // Regular shift counts (affect available seats)
                        'morning_boys'        => $nums['morning_boys'],
                        'morning_girls'       => $nums['morning_girls'],
                        'evening_boys'        => $nums['evening_boys'],
                        'evening_girls'       => $nums['evening_girls'],
                        // Shift-specific OOSC
                        'morning_oosc_boys'   => $nums['morning_oosc_boys'],
                        'morning_oosc_girls'  => $nums['morning_oosc_girls'],
                        'evening_oosc_boys'   => $nums['evening_oosc_boys'],
                        'evening_oosc_girls'  => $nums['evening_oosc_girls'],
                        // Shift-specific P2P
                        'morning_p2p_boys'    => $nums['morning_p2p_boys'],
                        'morning_p2p_girls'   => $nums['morning_p2p_girls'],
                        'evening_p2p_boys'    => $nums['evening_p2p_boys'],
                        'evening_p2p_girls'   => $nums['evening_p2p_girls'],
                        // Aggregate totals (analytics only)
                        'oosc_boys'           => $nums['morning_oosc_boys'] + $nums['evening_oosc_boys'],
                        'oosc_girls'          => $nums['morning_oosc_girls'] + $nums['evening_oosc_girls'],
                        'p2p_boys'            => $nums['morning_p2p_boys'] + $nums['evening_p2p_boys'],
                        'p2p_girls'           => $nums['morning_p2p_girls'] + $nums['evening_p2p_girls'],
                        'matric_tech_count'   => $nums['matric_tech_count'],
                        // Workflow
                        'status'              => $status,
                        'submitted_by'        => $status !== 'draft' ? $submitter?->id : null,
                        'submitted_at'        => $status !== 'draft' ? $admDate . ' 14:30:00' : null,
                        'verified_by'         => in_array($status, ['verified', 'locked']) ? $fdeAdmin?->id : null,
                        'verified_at'         => in_array($status, ['verified', 'locked']) ? $admDate . ' 16:00:00' : null,
                        'return_reason'       => $status === 'returned'
                            ? 'Figures appear inconsistent with class capacity. Please verify and resubmit.'
                            : null,
                    ]);

                    // ── Monitoring record for verified entries ────────────────
                    if (in_array($status, ['verified', 'locked'])) {
                        AdmissionMonitoring::firstOrCreate(
                            ['daily_admission_id' => $da->id],
                            [
                                'institution_id'   => $instId,
                                'class_id'         => $ic->class_id,
                                'academic_year_id' => $academicYear->id,
                                'admission_date'   => $admDate,
                                'workflow_status'  => 'test_verification',
                                'test_status'      => 'pending',
                                'merit_status'     => 'pending',
                                'doc_status'       => 'pending',
                            ]
                        );
                    }

                    $cumulRegular += $grandTotal; // track ALL categories — matches dashboard logic
                    $totalCreated++;
                    $isFirstEntry = false;
                }
            }
        }

        $this->command->line("  → DailyAdmissionSeeder: {$totalCreated} new entries (30-day span)");
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /** Build N weekday dates going back from today, oldest first */
    private function buildWeekdayDates(int $count): array
    {
        $dates = [];
        $date  = now();
        while (count($dates) < $count) {
            if (! $date->isWeekend()) {
                $dates[] = $date->toDateString();
            }
            $date = $date->copy()->subDay();
        }
        return array_reverse($dates);
    }

    /**
     * Status logic based on distance from end of date array:
     *   fromEnd >= 3 → verified
     *   fromEnd == 2 → submitted
     *   fromEnd == 1 → returned (first institution first entry) | submitted
     *   fromEnd == 0 → draft
     */
    private function resolveStatus(int $dayIndex, int $totalDays, int $instId, bool $isFirst): string
    {
        $fromEnd = $totalDays - 1 - $dayIndex;

        if ($fromEnd >= 3)  return 'verified';
        if ($fromEnd === 2) return 'submitted';
        if ($fromEnd === 1) return ($instId === 1 && $isFirst) ? 'returned' : 'submitted';
        return 'draft'; // fromEnd === 0 → today
    }

    /** Generate admission numbers respecting capacity and gender */
    private function generateNumbers(
        string $gender,
        int    $classOrder,
        int    $available,
        bool   $hasMatricTech
    ): array {
        $scale = match (true) {
            $classOrder <= 3  => rand(2, 6),
            $classOrder <= 5  => rand(1, 4),
            $classOrder <= 8  => rand(1, 3),
            default           => rand(0, 2),
        };

        $mb = 0; $mg = 0; $eb = 0; $eg = 0;

        if ($gender === 'boys') {
            $mb = $scale;
            $eb = rand(0, 1) ? (int) ceil($scale * 0.3) : 0;
        } elseif ($gender === 'girls') {
            $mg = $scale;
            $eg = rand(0, 1) ? (int) ceil($scale * 0.3) : 0;
        } else { // co_education
            $mb = (int) ceil($scale * 0.5);
            $mg = $scale - $mb;
            $eb = rand(0, 1) ? rand(0, 1) : 0;
            $eg = rand(0, 1) ? rand(0, 1) : 0;
        }

        // Cap regular admissions to available seats
        $regularTotal = $mb + $mg + $eb + $eg;
        if ($regularTotal > $available) {
            $remaining = $available;
            foreach ([&$mb, &$mg, &$eb, &$eg] as &$val) {
                if ($remaining <= 0) {
                    $val = 0;
                } elseif ($val <= $remaining) {
                    $remaining -= $val;
                } else {
                    $val       = $remaining;
                    $remaining = 0;
                }
            }
        }

        // OOSC — 25% chance, small numbers, exempt from seat cap
        $mOoscB = 0; $mOoscG = 0; $eOoscB = 0; $eOoscG = 0;
        if (rand(1, 100) <= 25) {
            if ($gender !== 'girls') $mOoscB = rand(0, 2);
            if ($gender !== 'boys')  $mOoscG = rand(0, 2);
            if ($eb > 0 && rand(0, 1)) $eOoscB = rand(0, 1);
            if ($eg > 0 && rand(0, 1)) $eOoscG = rand(0, 1);
        }

        // P2P — 15% chance, mostly morning, exempt from seat cap
        $mP2pB = 0; $mP2pG = 0; $eP2pB = 0; $eP2pG = 0;
        if (rand(1, 100) <= 15) {
            if ($gender !== 'girls') $mP2pB = rand(0, 1);
            if ($gender !== 'boys')  $mP2pG = rand(0, 1);
        }

        // Matric Tech — only Class 9 and 10 (not 11/12), eligible schools only
        // Must not exceed the regular row total for this day
        $regularTotal = $mb + $mg + $eb + $eg;
        $matricTech = 0;
        if ($hasMatricTech && in_array($classOrder, [9, 10]) && $regularTotal > 0) {
            if (rand(0, 1)) {
                $matricTech = rand(1, max(1, (int) ceil($regularTotal * 0.6)));
                $matricTech = min($matricTech, $regularTotal); // hard cap
            }
        }

        return [
            'morning_boys'       => $mb,
            'morning_girls'      => $mg,
            'evening_boys'       => $eb,
            'evening_girls'      => $eg,
            'morning_oosc_boys'  => $mOoscB,
            'morning_oosc_girls' => $mOoscG,
            'evening_oosc_boys'  => $eOoscB,
            'evening_oosc_girls' => $eOoscG,
            'morning_p2p_boys'   => $mP2pB,
            'morning_p2p_girls'  => $mP2pG,
            'evening_p2p_boys'   => $eP2pB,
            'evening_p2p_girls'  => $eP2pG,
            'matric_tech_count'  => $matricTech,
        ];
    }
}
