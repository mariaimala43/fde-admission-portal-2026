<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_admissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('restrict');

            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->onDelete('restrict');

            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->onDelete('restrict');

            $table->foreignId('section_id')
                  ->constrained('sections')
                  ->onDelete('restrict');

            // Date of admission entry — always today, no past/future
            $table->date('admission_date');

            // Shift-wise counts
            $table->unsignedInteger('morning_admissions')->default(0);
            $table->unsignedInteger('evening_admissions')->default(0);

            // Special intake — analytics only, does not affect seats
            $table->unsignedInteger('oosc_count')->default(0);
            $table->unsignedInteger('private_to_public_count')->default(0);

            // Status workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'pending_verification',
                'returned',
                'verified',
                'locked'
            ])->default('draft');

            // Submission tracking
            $table->foreignId('submitted_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();

            // AEO verification tracking
            $table->foreignId('verified_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('return_reason')->nullable();

            // FDE override tracking
            $table->foreignId('overridden_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->text('override_reason')->nullable();
            $table->timestamp('overridden_at')->nullable();

            $table->timestamps();

            // One entry per section per day
            $table->unique([
                'section_id',
                'admission_date'
            ], 'unique_daily_admission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_admissions');
    }
};
