<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // e.g. "2026-27"
            $table->date('start_date');
            $table->date('end_date');
            $table->date('admission_start')->nullable();
            $table->date('admission_end')->nullable();
            $table->time('daily_cutoff_time')->default('17:00:00');
            $table->boolean('is_active')->default(false);  // only one active at a time
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
