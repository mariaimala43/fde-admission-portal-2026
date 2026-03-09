<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAVE AS: database/migrations/2026_03_05_000002_add_referral_id_to_daily_admissions.php
 *
 * Adds referral_id to daily_admissions so any admission created
 * via a referral acceptance can be traced back to the referral.
 *
 * Run: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_admissions', function (Blueprint $table) {
            $table->foreignId('referral_id')
                ->nullable()
                ->after('id')
                ->constrained('referrals')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('daily_admissions', function (Blueprint $table) {
            $table->dropForeign(['referral_id']);
            $table->dropColumn('referral_id');
        });
    }
};
