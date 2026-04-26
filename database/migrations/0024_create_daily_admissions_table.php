<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_admissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('referral_id')
                  ->nullable()
                  ->constrained('referrals')
                  ->nullOnDelete();

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('restrict');

            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->onDelete('restrict');

            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->onDelete('restrict');

            $table->date('admission_date');
        });

        // Add 13 SMALLINT UNSIGNED columns via raw SQL (Blueprint unsignedSmallInteger
        // does not guarantee exact SMALLINT UNSIGNED NOT NULL DEFAULT 0 type)
        DB::statement("
            ALTER TABLE `daily_admissions`
                ADD COLUMN `morning_boys`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `morning_girls`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `morning_oosc_boys`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `morning_oosc_girls` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `morning_p2p_boys`   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `morning_p2p_girls`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `evening_boys`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `evening_girls`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `evening_oosc_boys`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `evening_oosc_girls` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `evening_p2p_boys`   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `evening_p2p_girls`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `matric_tech_count`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `oosc_boys`          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `oosc_girls`         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `p2p_boys`           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                ADD COLUMN `p2p_girls`          SMALLINT UNSIGNED NOT NULL DEFAULT 0
        ");

        Schema::table('daily_admissions', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'submitted',
                'pending_verification',
                'returned',
                'verified',
                'locked',
            ])->default('draft');

            $table->foreignId('submitted_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('verified_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('return_reason')->nullable();

            $table->foreignId('overridden_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->text('override_reason')->nullable();
            $table->timestamp('overridden_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['institution_id', 'class_id', 'admission_date'],
                'unique_daily_class_entry'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_admissions');
    }
};
