<?php
// SAVE AS: database/seeders/FacilitiesTestSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;

class FacilitiesTestSeeder extends Seeder
{
    public function run(): void
    {
        // Grab all active institutions
        $institutions = Institution::where('is_active', true)->get();

        if ($institutions->isEmpty()) {
            $this->command->warn('No active institutions found. Run InstitutionSeeder first.');
            return;
        }

        $total = $institutions->count();

        // Distribute facilities realistically across schools:
        // ~40% transport, ~30% meal program, ~25% matric tech,
        // ~15% ECE, ~10% Cambridge (only eligible ones)

        foreach ($institutions as $i => $inst) {
            $inst->has_transport      = ($i % 5 !== 0);           // 80% have transport
            $inst->has_meal_program   = ($i % 3 === 0);           // ~33% meal program
            $inst->has_matric_tech    = ($i % 4 === 0);           // 25% matric tech
            $inst->has_ece            = ($i % 6 === 0);           // ~17% ECE
            $inst->has_evening_classes= ($i % 7 === 0);           // ~14% evening

            // Cambridge: only the 4 eligible schools
            // is_cambridge is protected — use DB directly
            if ($inst->isCambridgeEligible()) {
                \Illuminate\Support\Facades\DB::table('institutions')
                    ->where('id', $inst->id)
                    ->update(['is_cambridge' => true]);
            }

            $inst->save();
        }

        // Summary
        $this->command->info("✅ Facilities seeded for {$total} institutions:");
        $this->command->table(
            ['Facility', 'Count'],
            [
                ['🚌 Transport',      Institution::where('has_transport',       true)->count()],
                ['🍱 Meal Program',   Institution::where('has_meal_program',    true)->count()],
                ['⚙️  Matric Tech',   Institution::where('has_matric_tech',     true)->count()],
                ['👶 ECE',            Institution::where('has_ece',             true)->count()],
                ['🌙 Evening Classes',Institution::where('has_evening_classes', true)->count()],
                ['🎓 Cambridge',      Institution::where('is_cambridge',        true)->count()],
            ]
        );
    }
}
