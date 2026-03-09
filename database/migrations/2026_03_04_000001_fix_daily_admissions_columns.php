
<?php
// database/migrations/2026_03_04_000001_fix_daily_admissions_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Get current state of daily_admissions ──────────────────
        $indexes = collect(DB::select("SHOW INDEX FROM `daily_admissions`"))
                    ->pluck('Key_name')->unique()->values()->toArray();

        $columns = collect(DB::select("SHOW COLUMNS FROM `daily_admissions`"))
                    ->pluck('Field')->toArray();

        $foreignKeys = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA    = DATABASE()
              AND TABLE_NAME      = 'daily_admissions'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        "))->pluck('CONSTRAINT_NAME')->toArray();

        // ── 2. Drop every possible old unique index (raw SQL) ─────────
        foreach ([
            'unique_daily_admission',
            'daily_admissions_section_id_admission_date_unique',
            'daily_admissions_institution_id_class_id_admission_date_unique',
            'unique_daily_class_entry',
        ] as $idx) {
            if (in_array($idx, $indexes)) {
                DB::statement("ALTER TABLE `daily_admissions` DROP INDEX `{$idx}`");
            }
        }

        // ── 3. Drop section_id FK then column ─────────────────────────
        if (in_array('daily_admissions_section_id_foreign', $foreignKeys)) {
            DB::statement("ALTER TABLE `daily_admissions` DROP FOREIGN KEY `daily_admissions_section_id_foreign`");
        }
        if (in_array('section_id', $columns)) {
            DB::statement("ALTER TABLE `daily_admissions` DROP COLUMN `section_id`");
        }

        // ── 4. Drop old wrongly-named count columns ───────────────────
        foreach ([
            'morning_admissions',
            'evening_admissions',
            'oosc_count',
            'private_to_public_count',
            'boys_count',
            'girls_count',
        ] as $col) {
            if (in_array($col, $columns)) {
                DB::statement("ALTER TABLE `daily_admissions` DROP COLUMN `{$col}`");
            }
        }

        // Re-fetch columns after drops
        $columns = collect(DB::select("SHOW COLUMNS FROM `daily_admissions`"))
                    ->pluck('Field')->toArray();

        // ── 5. Add correct shift + gender columns ─────────────────────
        $adds = [];
        if (!in_array('morning_boys',  $columns)) $adds[] = "ADD COLUMN `morning_boys`  SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `admission_date`";
        if (!in_array('morning_girls', $columns)) $adds[] = "ADD COLUMN `morning_girls` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `morning_boys`";
        if (!in_array('evening_boys',  $columns)) $adds[] = "ADD COLUMN `evening_boys`  SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `morning_girls`";
        if (!in_array('evening_girls', $columns)) $adds[] = "ADD COLUMN `evening_girls` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `evening_boys`";
        if (!in_array('oosc_boys',     $columns)) $adds[] = "ADD COLUMN `oosc_boys`     SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `evening_girls`";
        if (!in_array('oosc_girls',    $columns)) $adds[] = "ADD COLUMN `oosc_girls`    SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `oosc_boys`";
        if (!in_array('p2p_boys',      $columns)) $adds[] = "ADD COLUMN `p2p_boys`      SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `oosc_girls`";
        if (!in_array('p2p_girls',     $columns)) $adds[] = "ADD COLUMN `p2p_girls`     SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `p2p_boys`";

        if (!empty($adds)) {
            DB::statement("ALTER TABLE `daily_admissions` " . implode(', ', $adds));
        }

        // ── 6. Add correct unique constraint ─────────────────────────
        $indexes = collect(DB::select("SHOW INDEX FROM `daily_admissions`"))
                    ->pluck('Key_name')->unique()->toArray();

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
            $exists = collect(DB::select("SHOW COLUMNS FROM `daily_admissions` LIKE '{$col}'"))->isNotEmpty();
            if ($exists) {
                DB::statement("ALTER TABLE `daily_admissions` DROP COLUMN `{$col}`");
            }
        }
    }
};
