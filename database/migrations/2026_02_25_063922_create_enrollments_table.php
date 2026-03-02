<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
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

            // Baseline enrollment at start of academic year
            // Set once by HoI, locked after AEO verification
            $table->unsignedInteger('existing_enrollment')->default(0);

            // Status flow
            $table->enum('status', [
                'draft',
                'submitted',
                'pending_verification',
                'returned',
                'verified',
                'locked'
            ])->default('draft');

            // Verification tracking
            $table->foreignId('verified_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('return_reason')->nullable();

            // Submission tracking
            $table->foreignId('submitted_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();

            // FDE override tracking
            $table->foreignId('overridden_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->text('override_reason')->nullable();
            $table->timestamp('overridden_at')->nullable();

            $table->timestamps();

            // One enrollment record per section per year
            $table->unique([
                'academic_year_id',
                'section_id'
            ], 'unique_enrollment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
