<?php
// SAVE AS: database/seeders/MatricTechTestSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Institution;
use App\Models\AcademicYear;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\Classes;
use Carbon\Carbon;

class MatricTechTestSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        if (! $academicYear) {
            $this->command->error('No active academic year found. Run AcademicYearSeeder first.');
            return;
        }

        // ── Get matric-tech schools ──────────────────────────────────
        $schools = Institution::where('is_active', true)
            ->where('has_matric_tech', true)
            ->with(['institutionClasses.classModel', 'sector'])
            ->get();

        if ($schools->isEmpty()) {
            $this->command->error('No schools with has_matric_tech=true found. Run FacilitiesTestSeeder first.');
            return;
        }

        $this->command->info("Found {$schools->count()} Matric Tech school(s). Seeding admissions...");

        // ── Matric-eligible class names (classes 9 & 10 only) ────────
        $matricClassNames = ['Class 9', 'Class IX', '9', 'Class 10', 'Class X', '10',
                             'Class 9 (Science)', 'Class 9 (Arts)', 'Class 10 (Science)', 'Class 10 (Arts)'];

        // ── Seed 7 days of data (last week) + today ──────────────────
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->push(Carbon::today()->subDays($i)->toDateString());
        }

        $inserted   = 0;
        $skipped    = 0;
        $adminId    = DB::table('users')->where('email', 'admin@fde.edu.pk')->value('id')
                      ?? DB::table('users')->first()?->id;

        foreach ($schools as $school) {
            // Get class 9 & 10 institution classes for this school
            $matricClasses = $school->institutionClasses->filter(function ($ic) use ($matricClassNames) {
                $name = $ic->classModel?->name ?? '';
                foreach ($matricClassNames as $mn) {
                    if (stripos($name, $mn) !== false) return true;
                }
                // Also match by class_id 9 or 10 if names don't match
                return in_array($ic->class_id, [9, 10]);
            });

            // Fallback: use any available class if no 9/10 found
            if ($matricClasses->isEmpty()) {
                $matricClasses = $school->institutionClasses->take(2);
            }

            if ($matricClasses->isEmpty()) {
                $this->command->warn("  ⚠ {$school->name} — no classes configured, skipping.");
                $skipped++;
                continue;
            }

            $this->command->line("  → {$school->name} ({$school->sector?->name}) — {$matricClasses->count()} class(es)");

            foreach ($dates as $date) {
                $isToday   = $date === Carbon::today()->toDateString();
                $isRecent  = Carbon::parse($date)->gte(Carbon::today()->subDays(2));

                foreach ($matricClasses as $ic) {
                    // Skip if already exists
                    $exists = DailyAdmission::where('institution_id', $school->id)
                        ->where('class_id', $ic->class_id)
                        ->where('admission_date', $date)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    // Realistic numbers
                    $morningBoys   = rand(8, 25);
                    $morningGirls  = $school->gender === 'boys'  ? 0 : rand(6, 20);
                    $eveningBoys   = in_array($school->shift, ['evening', 'both']) ? rand(3, 12) : 0;
                    $eveningGirls  = (in_array($school->shift, ['evening', 'both']) && $school->gender !== 'boys') ? rand(2, 8) : 0;

                    $ooscBoys  = rand(0, 4);
                    $ooscGirls = $school->gender === 'boys' ? 0 : rand(0, 3);
                    $p2pBoys   = rand(0, 2);
                    $p2pGirls  = $school->gender === 'boys' ? 0 : rand(0, 2);

                    $grandTotal = $morningBoys + $morningGirls + $eveningBoys + $eveningGirls
                                + $ooscBoys + $ooscGirls + $p2pBoys + $p2pGirls;

                    // matric_tech_count = subset of grand total (10–40%)
                    $matricTechCount = max(1, (int) round($grandTotal * (rand(10, 40) / 100)));

                    // Status: today = draft, recent = submitted, older = verified
                    if ($isToday) {
                        $status      = 'draft';
                        $submittedBy = null;
                        $submittedAt = null;
                        $verifiedBy  = null;
                        $verifiedAt  = null;
                    } elseif ($isRecent) {
                        $status      = 'submitted';
                        $submittedBy = $adminId;
                        $submittedAt = Carbon::parse($date)->setTime(20, rand(0, 59));
                        $verifiedBy  = null;
                        $verifiedAt  = null;
                    } else {
                        $status      = 'verified';
                        $submittedBy = $adminId;
                        $submittedAt = Carbon::parse($date)->setTime(20, rand(0, 59));
                        $verifiedBy  = $adminId;
                        $verifiedAt  = Carbon::parse($date)->setTime(22, rand(0, 59));
                    }

                    DailyAdmission::create([
                        'academic_year_id'    => $academicYear->id,
                        'institution_id'      => $school->id,
                        'class_id'            => $ic->class_id,
                        'admission_date'      => $date,

                        'morning_boys'        => $morningBoys,
                        'morning_girls'       => $morningGirls,
                        'evening_boys'        => $eveningBoys,
                        'evening_girls'       => $eveningGirls,

                        'morning_oosc_boys'   => $ooscBoys,
                        'morning_oosc_girls'  => $ooscGirls,
                        'morning_p2p_boys'    => $p2pBoys,
                        'morning_p2p_girls'   => $p2pGirls,
                        'evening_oosc_boys'   => 0,
                        'evening_oosc_girls'  => 0,
                        'evening_p2p_boys'    => 0,
                        'evening_p2p_girls'   => 0,

                        'oosc_boys'           => $ooscBoys,
                        'oosc_girls'          => $ooscGirls,
                        'p2p_boys'            => $p2pBoys,
                        'p2p_girls'           => $p2pGirls,

                        'matric_tech_count'   => $matricTechCount,

                        'status'              => $status,
                        'submitted_by'        => $submittedBy,
                        'submitted_at'        => $submittedAt,
                        'verified_by'         => $verifiedBy,
                        'verified_at'         => $verifiedAt,
                    ]);

                    $inserted++;
                }
            }
        }

        // ── Summary ──────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info("✅ Done! {$inserted} records inserted, {$skipped} skipped (already exist).");
        $this->command->newLine();

        // Show totals per school
        $this->command->table(
            ['School', 'Sector', 'Today MT', 'Year MT Total', 'Year Grand Total'],
            $schools->map(function ($school) use ($academicYear) {
                $today = DailyAdmission::where('institution_id', $school->id)
                    ->where('admission_date', now()->toDateString())
                    ->sum('matric_tech_count');

                $year = DailyAdmission::where('institution_id', $school->id)
                    ->where('academic_year_id', $academicYear->id)
                    ->selectRaw('SUM(matric_tech_count) as mt, SUM(morning_boys+morning_girls+evening_boys+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as grand')
                    ->first();

                return [
                    $school->name,
                    $school->sector?->name ?? '—',
                    $today,
                    $year?->mt    ?? 0,
                    $year?->grand ?? 0,
                ];
            })->toArray()
        );

        $this->command->newLine();
        $this->command->line('  <fg=cyan>HOI test login:</> Log in as a HOI from any of the above schools');
        $this->command->line('  <fg=cyan>             </> → Daily Admissions → Table 3 (⚙️ Matric Tech) should appear');
        $this->command->line('  <fg=cyan>FDE dashboard:</> /fde/dashboard → Today + Year matric tech cards should show numbers');
    }
}
