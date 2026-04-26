<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyAdmission;
use App\Models\AdmissionMonitoring;

/**
 * BackfillMonitoringSeeder
 *
 * Creates AdmissionMonitoring records for every verified/locked
 * DailyAdmission row that doesn't already have one.
 *
 * Safe to run multiple times — uses firstOrCreate so no duplicates.
 *
 * RUN:
 *   php artisan db:seed --class=BackfillMonitoringSeeder
 */
class BackfillMonitoringSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Backfilling AdmissionMonitoring records...');

        // Fetch all verified/locked daily admissions that have no monitoring record
        $admissions = DailyAdmission::whereIn('status', ['verified', 'locked'])
            ->whereNotIn('id', AdmissionMonitoring::pluck('daily_admission_id'))
            ->get();

        if ($admissions->isEmpty()) {
            $this->command->info('  → Nothing to backfill. All verified admissions already have monitoring records.');
            return;
        }

        $created = 0;

        foreach ($admissions as $da) {
            AdmissionMonitoring::firstOrCreate(
                ['daily_admission_id' => $da->id],
                [
                    'institution_id'   => $da->institution_id,
                    'class_id'         => $da->class_id,
                    'academic_year_id' => $da->academic_year_id,
                    'admission_date'   => $da->admission_date,
                    'workflow_status'  => 'test_verification',
                    'test_status'      => 'pending',
                    'merit_status'     => 'pending',
                    'doc_status'       => 'pending',
                ]
            );
            $created++;
        }

        $this->command->info("  → Created {$created} monitoring records.");
        $this->command->info('  Done. Visit HOI → Monitoring to see the records.');
    }
}
