<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAVE AS: database/migrations/2026_03_05_000001_create_referrals_table.php
 *
 * Run: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            // Auto-generated human-readable reference e.g. REF-2026-00001
            $table->string('reference_no', 30)->unique();

            // Who created this referral (FDE Cell user)
            $table->foreignId('referred_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Which school this student is referred TO
            $table->foreignId('institution_id')
                ->constrained('institutions')
                ->cascadeOnDelete();

            // Academic year context
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();

            // Student info — all optional (tracking only, no student records in DB)
            $table->string('student_name', 100)->nullable();
            $table->string('father_name',  100)->nullable();
            $table->foreignId('class_id')
                ->nullable()
                ->constrained('classes')
                ->nullOnDelete();

            // Gender — used to determine which daily_admissions column to increment on accept
            // If null, HOI picks on accept
            $table->enum('gender', ['male', 'female'])->nullable();

            // Shift — used to determine morning_boys/girls vs evening_boys/girls on accept
            $table->enum('shift', ['morning', 'evening'])->default('morning');

            // FDE optional notes for the HOI
            $table->text('notes')->nullable();

            // ── Status lifecycle ──────────────────────────────────────────
            // pending → accepted | rejected
            // rejected → (FDE can re-refer = new referral with parent_referral_id)
            // pending → closed (FDE cancels)
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                're_referred',   // this referral was rejected and a new one was made
                'closed',        // FDE cancelled before HOI responded
            ])->default('pending')->index();

            // HOI rejection reason (required when status = rejected)
            $table->text('rejection_reason')->nullable();

            // If this referral was re-referred, link to the new referral
            $table->foreignId('re_referred_to')
                ->nullable()
                ->constrained('referrals')
                ->nullOnDelete();

            // If this referral was created as a re-referral of a rejected one
            $table->foreignId('parent_referral_id')
                ->nullable()
                ->constrained('referrals')
                ->nullOnDelete();

            // When daily admission was created on accept (FK added in separate migration)
            $table->unsignedBigInteger('daily_admission_id')->nullable();

            // Timestamps for each status transition
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Who accepted/rejected/closed
            $table->foreignId('actioned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
