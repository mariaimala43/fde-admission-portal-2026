<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Institution;
use App\Models\Classes;
use App\Models\InstitutionClass;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;

/**
 * MatricTechSeeder
 *
 * Enables the Matric Tech vocational program on a subset of test
 * institutions (those that teach classes 9–12) and populates
 * matric_tech_count on their existing DailyAdmission records.
 *
 * Why a separate seeder?
 *   DailyAdmissionSeeder already seeds matric_tech_count, but only when
 *   institution->has_matric_tech = true at seeding time.  TestingSeeder
 *   does not set that flag, so no matric tech data is produced.
 *   This seeder retrofits both the flag and the count values.
 *
 * Institutions enabled (VI-XII / I-XII, cross-sector variety):
 *   ID 1   — IMCG G-6/1-4  (Urban-I,  VI-XII, Girls)
 *   ID 118  — IMCB B.K      (B.K,      VI-XII, Boys)
 *   ID 272  — IMCB Tarnol   (Tarnol,   VI-XII, Boys)
 *   ID 433  — IMCG F-8/1    (Sector-I, I-XII,  Girls, Cambridge)
 *
 * Matric Tech classes:  orders 9, 10, 11, 12
 * Count per day:        1–6  (small, realistic)
 */
class MatricTechSeeder extends Seeder
{
    /** Institution IDs to enable matric tech on */
    private array $enabledInstitutions = [1, 118, 272, 433];

    /** Class orders eligible for Matric Tech */
    private array $eligibleOrders = [9, 10, 11, 12];

    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        if (! $academicYear) {
            $this->command->error('No active academic year.');
            return;
        }

        // 1. Enable has_matric_tech on selected institutions
        $enabled = Institution::whereIn('id', $this->enabledInstitutions)->update([
            'has_matric_tech' => true,
        ]);
        $this->command->line("  → MatricTechSeeder: enabled matric_tech on {$enabled} institutions");

        // 2. Find eligible class IDs (orders 9–12, non-ECE)
        $eligibleClassIds = Classes::whereIn('order', $this->eligibleOrders)
            ->where('is_ece', false)
            ->pluck('id')
            ->toArray();

        if (empty($eligibleClassIds)) {
            $this->command->warn('  ⚠ No eligible classes (orders 9–12) found.');
            return;
        }

        // 3. Update existing DailyAdmission records for these institutions/classes
        $updated = 0;

        foreach ($this->enabledInstitutions as $instId) {
            $institution = Institution::find($instId);
            if (! $institution) continue;

            // Get institution classes for eligible orders
            $instClassIds = InstitutionClass::where('institution_id', $instId)
                ->whereIn('class_id', $eligibleClassIds)
                ->pluck('class_id')
                ->toArray();

            if (empty($instClassIds)) continue;

            // Update each verified/submitted DA record for this institution + eligible classes
            $das = DailyAdmission::where('institution_id', $instId)
                ->whereIn('class_id', $instClassIds)
                ->where('academic_year_id', $academicYear->id)
                ->whereIn('status', ['verified', 'locked', 'submitted'])
                ->get();

            foreach ($das as $da) {
                // Only update if currently zero (don't overwrite any existing counts)
                if ($da->matric_tech_count > 0) continue;

                $count = $this->generateMatricTechCount();
                $da->update(['matric_tech_count' => $count]);
                $updated++;
            }

            // Also seed a few draft DA records for today's entries
            $draftDas = DailyAdmission::where('institution_id', $instId)
                ->whereIn('class_id', $instClassIds)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', 'draft')
                ->get();

            foreach ($draftDas as $da) {
                if ($da->matric_tech_count > 0) continue;
                $da->update(['matric_tech_count' => $this->generateMatricTechCount()]);
                $updated++;
            }
        }

        $this->command->line("  → MatricTechSeeder: updated matric_tech_count on {$updated} daily admission records");

        // 4. Summary: count total matric tech students seeded
        $totalMatricTech = DailyAdmission::whereIn('institution_id', $this->enabledInstitutions)
            ->where('academic_year_id', $academicYear->id)
            ->sum('matric_tech_count');

        $this->command->line("  → MatricTechSeeder: total matric_tech students across all records: {$totalMatricTech}");
    }

    /** Small realistic count per day — 1-6 students */
    private function generateMatricTechCount(): int
    {
        // 60% chance of 1-3, 30% chance of 4-6, 10% chance of 0 (absent day)
        $roll = rand(1, 10);
        return match (true) {
            $roll <= 1  => 0,
            $roll <= 7  => rand(1, 3),
            default     => rand(4, 6),
        };
    }
}
