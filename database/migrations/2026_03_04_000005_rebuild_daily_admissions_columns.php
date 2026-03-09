<?php
// database/migrations/2026_03_04_000005_rebuild_daily_admissions_columns.php
//
// Safe rebuild — checks every index and column before touching anything.
// Does NOT attempt to drop anything that might not exist.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Snapshot current state ────────────────────────────────────
        $columns = collect(DB::select("SHOW COLUMNS FROM `daily_admissions`"))
                    ->pluck('Field')->toArray();

        $indexes = collect(DB::select("SHOW INDEX FROM `daily_admissions`"))
                    ->pluck('Key_name')->unique()->values()->toArray();

        $foreignKeys = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_NAME    = 'daily_admissions'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND TABLE_SCHEMA  = DATABASE()
        "))->pluck('CONSTRAINT_NAME')->toArray();

        // ── 1. Drop ALL known unique indexes (only if they exist) ─────
        $indexesToDrop = [
            'unique_daily_admission',
            'unique_daily_class_entry',
            'daily_admissions_section_id_admission_date_unique',
            'daily_admissions_institution_id_class_id_admission_date_unique',
        ];

        foreach ($indexesToDrop as $idx) {
            if (in_array($idx, $indexes)) {
                DB::statement("ALTER TABLE `daily_admissions` DROP INDEX `{$idx}`");
            }
        }

        // ── 2. Drop section_id FK + column (only if they exist) ───────
        if (in_array('daily_admissions_section_id_foreign', $foreignKeys)) {
            DB::statement("ALTER TABLE `daily_admissions` DROP FOREIGN KEY `daily_admissions_section_id_foreign`");
        }
        if (in_array('section_id', $columns)) {
            DB::statement("ALTER TABLE `daily_admissions` DROP COLUMN `section_id`");
        }

        // ── 3. Drop old wrong-named columns (only if they exist) ──────
        $oldCols = [
            'morning_admissions',
            'evening_admissions',
            'oosc_count',
            'private_to_public_count',
            'boys_count',
            'girls_count',
        ];
        foreach ($oldCols as $col) {
            if (in_array($col, $columns)) {
                DB::statement("ALTER TABLE `daily_admissions` DROP COLUMN `{$col}`");
            }
        }

        // ── 4. Re-snapshot columns after drops ────────────────────────
        $columns = collect(DB::select("SHOW COLUMNS FROM `daily_admissions`"))
                    ->pluck('Field')->toArray();

        // ── 5. Add correct shift+gender columns (only if missing) ─────
        $toAdd = [
            'morning_boys'  => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
            'morning_girls' => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
            'evening_boys'  => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
            'evening_girls' => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
            'oosc_boys'     => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
            'oosc_girls'    => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
            'p2p_boys'      => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
            'p2p_girls'     => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
        ];

        foreach ($toAdd as $col => $definition) {
            if (!in_array($col, $columns)) {
                DB::statement("ALTER TABLE `daily_admissions` ADD COLUMN `{$col}` {$definition}");
            }
        }

        // ── 6. Add correct unique constraint (only if missing) ────────
        $indexes = collect(DB::select("SHOW INDEX FROM `daily_admissions`"))
                    ->pluck('Key_name')->unique()->values()->toArray();

        if (!in_array('unique_daily_class_entry', $indexes)) {
            DB::statement("
                ALTER TABLE `daily_admissions`
                ADD UNIQUE KEY `unique_daily_class_entry`
                (`institution_id`, `class_id`, `admission_date`)
            ");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `daily_admissions` DROP INDEX IF EXISTS `unique_daily_class_entry`");

        foreach (['morning_boys','morning_girls','evening_boys','evening_girls','oosc_boys','oosc_girls','p2p_boys','p2p_girls'] as $col) {
            $exists = collect(DB::select("SHOW COLUMNS FROM `daily_admissions`"))->pluck('Field')->contains($col);
            if ($exists) {
                DB::statement("ALTER TABLE `daily_admissions` DROP COLUMN `{$col}`");
            }
        }
    }
};
