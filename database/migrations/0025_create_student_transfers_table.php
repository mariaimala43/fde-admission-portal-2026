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

            $table->foreignId('from_institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('to_institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();

            $table->string('student_name')->nullable();
            $table->string('father_name')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->enum('initiated_by_role', ['hoi', 'fde_cell']);

            // Cross-sector fields
            $table->boolean('is_cross_sector')->default(false);
            $table->string('cross_sector_note', 500)->nullable();
            $table->foreignId('cross_sector_approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('cross_sector_approved_at')->nullable();

            $table->enum('status', [
                'pending',
                'info_requested',
                'accepted',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->text('info_request_note')->nullable();
            $table->text('cancellation_reason')->nullable();

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
