<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * SAVE AS: database/migrations/2026_03_05_000001_add_is_cross_sector_to_student_transfers.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_transfers', function (Blueprint $table) {
            $table->boolean('is_cross_sector')->default(false)->after('initiated_by_role');
            $table->string('cross_sector_note', 500)->nullable()->after('is_cross_sector');
        });

        // Backfill existing rows — mark cross-sector if sectors differ
        DB::statement('
            UPDATE student_transfers st
            JOIN institutions fi ON fi.id = st.from_institution_id
            JOIN institutions ti ON ti.id = st.to_institution_id
            SET st.is_cross_sector = 1
            WHERE fi.sector_id != ti.sector_id
        ');
    }

    public function down(): void
    {
        Schema::table('student_transfers', function (Blueprint $table) {
            $table->dropColumn(['is_cross_sector', 'cross_sector_note']);
        });
    }
};
