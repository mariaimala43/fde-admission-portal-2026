<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('class_name');
            $table->integer('total_seats')->default(40);
            $table->integer('occupied_seats')->default(0);
            $table->string('academic_year'); // e.g. "2024-25"
            $table->timestamps();

            $table->unique(['school_id', 'class_name', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_seats');
    }
};
