<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_strength_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')
                  ->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')->cascadeOnDelete();
            $table->enum('status', ['draft', 'submitted', 'locked'])->default('draft');
            $table->foreignId('submitted_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('locked_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->text('fde_remarks')->nullable();
            $table->timestamps();

            $table->unique(['institution_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_strength_registers');
    }
};
