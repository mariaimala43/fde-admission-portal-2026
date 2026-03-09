<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::firstOrCreate(
            ['name' => '2026-27'],
            [
                'start_date'        => '2026-04-01',
                'end_date'          => '2027-03-31',
                'admission_start'   => '2026-04-01',
                'admission_end'     => '2026-06-30',
                // Keep entry window open till midnight for testing
                'daily_cutoff_time' => '23:59:00',
                'is_active'         => true,
            ]
        );

        $this->command->info('Academic year 2026-27 seeded successfully.');
    }
}
