<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transfers', function (Blueprint $table) {
            $table->id();

            // Institutions
            $table->foreignId('from_institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('to_institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();

            // Academic year
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();

            // Optional student info (no student model — record-keeping only)
            $table->string('student_name')->nullable();
            $table->string('father_name')->nullable();
            $table->text('notes')->nullable();

            // Who initiated
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->enum('initiated_by_role', ['hoi', 'fde_cell']);

            // Status workflow
            $table->enum('status', [
                'pending',
                'info_requested',
                'accepted',
                'rejected',
                'cancelled',
            ])->default('pending');

            // Action metadata
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->text('info_request_note')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Timestamps for workflow steps
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('info_requested_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transfers');
    }
};
