<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1 — Extend the level enum to include 'undergraduate'
        DB::statement("
            ALTER TABLE classes
            MODIFY COLUMN level
                ENUM('ece','primary','middle','high','higher_secondary','undergraduate')
                NOT NULL DEFAULT 'primary'
        ");

        // 2 — Insert Class 13 and Class 14 (idempotent)
        $now = now();

        DB::table('classes')->upsert(
            [
                [
                    'name'       => 'Class 13',
                    'order'      => 13,
                    'is_ece'     => 0,
                    'level'      => 'undergraduate',
                    'is_active'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name'       => 'Class 14',
                    'order'      => 14,
                    'is_ece'     => 0,
                    'level'      => 'undergraduate',
                    'is_active'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['name'],                         // unique key to match on
            ['order','level','is_active','updated_at']  // columns to update if exists
        );
    }

    public function down(): void
    {
        // Remove Class 13 & 14
        DB::table('classes')->whereIn('name', ['Class 13', 'Class 14'])->delete();

        // Revert enum (remove 'undergraduate')
        DB::statement("
            ALTER TABLE classes
            MODIFY COLUMN level
                ENUM('ece','primary','middle','high','higher_secondary')
                NOT NULL DEFAULT 'primary'
        ");
    }
};
