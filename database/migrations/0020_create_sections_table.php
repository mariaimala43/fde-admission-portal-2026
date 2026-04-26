<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->onDelete('restrict');

            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->onDelete('restrict');

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('restrict');

            $table->string('name');                    // "A", "B", "C", "D"
            $table->enum('gender', ['male', 'female', 'combined']);
            $table->unsignedInteger('total_seats');
            $table->enum('shift', ['morning', 'evening'])->default('morning');
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('restrict');

            $table->timestamps();

            $table->unique(
                ['institution_id', 'class_id', 'academic_year_id', 'name', 'gender'],
                'unique_section'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
