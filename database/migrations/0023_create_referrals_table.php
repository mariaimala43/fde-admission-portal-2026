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

            $table->string('reference_no', 30)->unique();

            $table->foreignId('referred_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->cascadeOnDelete();

            $table->string('student_name', 100)->nullable();
            $table->string('father_name', 100)->nullable();

            $table->foreignId('class_id')
                  ->nullable()
                  ->constrained('classes')
                  ->nullOnDelete();

            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('shift', ['morning', 'evening'])->default('morning');
            $table->text('notes')->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                're_referred',
                'closed',
            ])->default('pending')->index();

            $table->text('rejection_reason')->nullable();

            // Self-referencing FKs
            $table->foreignId('re_referred_to')
                  ->nullable()
                  ->constrained('referrals')
                  ->nullOnDelete();

            $table->foreignId('parent_referral_id')
                  ->nullable()
                  ->constrained('referrals')
                  ->nullOnDelete();

            // Link to daily admission (not a constrained FK — avoids circular dep)
            $table->unsignedBigInteger('daily_admission_id')->nullable();

            $table->timestamp('accepted_at')->nullable();

            // Tracking fields (added by tracking migration)
            $table->enum('test_conducted', ['yes', 'no', 'exempted'])->nullable();
            $table->enum('test_result', ['pass', 'fail'])->nullable();
            $table->enum('admission_status', ['admitted', 'not_admitted'])->nullable();
            $table->timestamp('test_updated_at')->nullable();
            $table->foreignId('test_updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('admission_updated_at')->nullable();
            $table->foreignId('admission_updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('closed_at')->nullable();

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
