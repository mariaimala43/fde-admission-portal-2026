<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAVE AS: database/migrations/2026_03_05_100001_add_seat_lock_to_institutions.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->foreignId('seats_locked_by')
                  ->nullable()
                  ->after('has_evening_classes')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('seats_locked_at')
                  ->nullable()
                  ->after('seats_locked_by');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropForeign(['seats_locked_by']);
            $table->dropColumn(['seats_locked_by', 'seats_locked_at']);
        });
    }
};
