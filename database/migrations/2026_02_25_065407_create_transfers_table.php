<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('restrict');

            // Source institution
            $table->foreignId('from_institution_id')
                  ->constrained('institutions')
                  ->onDelete('restrict');

            // Destination institution
            $table->foreignId('to_institution_id')
                  ->constrained('institutions')
                  ->onDelete('restrict');

            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->onDelete('restrict');

            $table->foreignId('from_section_id')
                  ->constrained('sections')
                  ->onDelete('restrict');

            $table->foreignId('to_section_id')
                  ->nullable()              // destination section assigned after approval
                  ->constrained('sections')
                  ->onDelete('set null');

            $table->enum('gender', [
                'male',
                'female',
                'combined'
            ]);

            // Number of students being transferred
            $table->unsignedInteger('student_count');

            // Optional reference fields (not stored as student records)
            $table->string('student_name')->nullable();
            $table->string('father_name')->nullable();
            $table->text('transfer_reason')->nullable();

            // Status workflow
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled'
            ])->default('pending');

            // Initiated by HoI of source school
            $table->foreignId('initiated_by')
                  ->constrained('users')
                  ->onDelete('restrict');

            // AEO approval tracking
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // FDE Cell review (for cross-sector transfers)
            $table->boolean('needs_fde_review')->default(false);
            $table->foreignId('fde_reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('fde_reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
