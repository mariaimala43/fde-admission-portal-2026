<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAVE AS: database/migrations/2026_03_05_000002_add_cross_sector_approval_to_student_transfers.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_transfers', function (Blueprint $table) {
            $table->foreignId('cross_sector_approved_by')
                  ->nullable()
                  ->after('cross_sector_note')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('cross_sector_approved_at')
                  ->nullable()
                  ->after('cross_sector_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('student_transfers', function (Blueprint $table) {
            $table->dropForeign(['cross_sector_approved_by']);
            $table->dropColumn(['cross_sector_approved_by', 'cross_sector_approved_at']);
        });
    }
};
