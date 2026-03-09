<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core reference data needed by the application
        $this->call([
            RolesSeeder::class,
            ClassesSeeder::class,
            AcademicYearSeeder::class,
            AdminUserSeeder::class,
            UnionCouncilSeeder::class,
            SectorSeeder::class,
            InstitutionSeeder::class,
        ]);

        // Local / testing only: realistic workflow data for full-system testing
        if (! app()->environment('production')) {
            $this->call(TestingSeeder::class);
        }
    }
}
