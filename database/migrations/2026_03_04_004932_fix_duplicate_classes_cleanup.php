<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Clean up duplicate class entries in the classes table.
 *
 * Current state (broken):
 *   ID 13  ECE-I                order 0   ← keep
 *   ID 17  ECE                  order 0   ← DELETE (duplicate)
 *   ID 14  ECE-II/Prep          order 1   ← keep
 *   ID 1   Class 1              order 1   ← keep (fix order → 1)
 *   ...
 *   ID 10  Class 10             order 10  ← keep
 *   ID 18  Class 11             order 11  ← DELETE (duplicate)
 *   ID 15  Class 11 / 1st Year  order 12  ← RENAME → "Class 11", order → 11
 *   ID 19  Class 12             order 12  ← DELETE (duplicate)
 *   ID 16  Class 12 / 2nd Year  order 13  ← RENAME → "Class 12", order → 12
 *
 * After fix:
 *   Clean 14 classes: ECE-I, ECE-II/Prep, Class 1..10, Class 11, Class 12
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Reassign any foreign keys pointing to duplicate IDs ──
        //    Move institution_classes, institution_sections, daily_admissions,
        //    enrollments from the duplicate IDs to the original IDs.

        $reassign = [
            // duplicate ID => original ID
            18 => 15,  // "Class 11" (dup) → "Class 11 / 1st Year" (original, will be renamed)
            19 => 16,  // "Class 12" (dup) → "Class 12 / 2nd Year" (original, will be renamed)
        ];

        $tables = ['institution_classes', 'institution_sections', 'daily_admissions', 'enrollments'];

        foreach ($reassign as $dupId => $origId) {
            foreach ($tables as $table) {
                DB::table($table)->where('class_id', $dupId)->update(['class_id' => $origId]);
            }
        }

        // ── 2. Delete duplicate classes ─────────────────────────────
        //    ID 17 "ECE" (duplicate of ECE-I)
        //    ID 18 "Class 11" (duplicate)
        //    ID 19 "Class 12" (duplicate)
        DB::table('classes')->whereIn('id', [17, 18, 19])->delete();

        // ── 3. Rename and fix orders ────────────────────────────────
        DB::table('classes')->where('id', 15)->update([
            'name'  => 'Class 11',
            'order' => 11,
        ]);

        DB::table('classes')->where('id', 16)->update([
            'name'  => 'Class 12',
            'order' => 12,
        ]);
    }

    public function down(): void
    {
        // Restore original names
        DB::table('classes')->where('id', 15)->update([
            'name'  => 'Class 11 / 1st Year',
            'order' => 12,
        ]);

        DB::table('classes')->where('id', 16)->update([
            'name'  => 'Class 12 / 2nd Year',
            'order' => 13,
        ]);

        // Re-create deleted rows
        DB::table('classes')->insert([
            ['id' => 17, 'name' => 'ECE',      'order' => 0,  'level' => 'ece',              'is_ece' => true,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'name' => 'Class 11',  'order' => 11, 'level' => 'higher_secondary', 'is_ece' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 19, 'name' => 'Class 12',  'order' => 12, 'level' => 'higher_secondary', 'is_ece' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
