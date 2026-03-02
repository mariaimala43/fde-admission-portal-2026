<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('restrict');

            // Target school receiving the referral
            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->onDelete('restrict');

            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->onDelete('restrict');

            $table->enum('gender', [
                'male',
                'female'
            ]);

            // Student details — internal only, never shown on public portal
            $table->string('student_name');
            $table->string('father_name');

            $table->enum('priority', [
                'normal',
                'urgent'
            ])->default('normal');

            $table->text('referral_notes')->nullable();

            // Status workflow
            $table->enum('status', [
                'pending',
                'admitted',
                'unable_to_admit'
            ])->default('pending');

            // HoI response
            $table->foreignId('responded_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('responded_at')->nullable();
            $table->text('response_reason')->nullable(); // required if unable_to_admit

            // Issued by FDE Cell
            $table->foreignId('issued_by')
                  ->constrained('users')
                  ->onDelete('restrict');

            // Response deadline
            $table->date('response_due_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
