<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAVE AS: database/migrations/YYYY_MM_DD_XXXXXX_create_admission_corrections_table.php
 * (keep whatever filename you already have)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admission_corrections')) {
            return; // Already exists — skip silently
        }

        Schema::create('admission_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->date('admission_date');
            $table->text('reason');

            // Old values
            $table->smallInteger('old_morning_boys')->unsigned()->default(0);
            $table->smallInteger('old_morning_girls')->unsigned()->default(0);
            $table->smallInteger('old_evening_boys')->unsigned()->default(0);
            $table->smallInteger('old_evening_girls')->unsigned()->default(0);
            $table->smallInteger('old_morning_oosc_boys')->unsigned()->default(0);
            $table->smallInteger('old_morning_oosc_girls')->unsigned()->default(0);
            $table->smallInteger('old_morning_p2p_boys')->unsigned()->default(0);
            $table->smallInteger('old_morning_p2p_girls')->unsigned()->default(0);
            $table->smallInteger('old_evening_oosc_boys')->unsigned()->default(0);
            $table->smallInteger('old_evening_oosc_girls')->unsigned()->default(0);
            $table->smallInteger('old_evening_p2p_boys')->unsigned()->default(0);
            $table->smallInteger('old_evening_p2p_girls')->unsigned()->default(0);

            // New values
            $table->smallInteger('new_morning_boys')->unsigned()->default(0);
            $table->smallInteger('new_morning_girls')->unsigned()->default(0);
            $table->smallInteger('new_evening_boys')->unsigned()->default(0);
            $table->smallInteger('new_evening_girls')->unsigned()->default(0);
            $table->smallInteger('new_morning_oosc_boys')->unsigned()->default(0);
            $table->smallInteger('new_morning_oosc_girls')->unsigned()->default(0);
            $table->smallInteger('new_morning_p2p_boys')->unsigned()->default(0);
            $table->smallInteger('new_morning_p2p_girls')->unsigned()->default(0);
            $table->smallInteger('new_evening_oosc_boys')->unsigned()->default(0);
            $table->smallInteger('new_evening_oosc_girls')->unsigned()->default(0);
            $table->smallInteger('new_evening_p2p_boys')->unsigned()->default(0);
            $table->smallInteger('new_evening_p2p_girls')->unsigned()->default(0);

            // Workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('fde_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_corrections');
    }
};
