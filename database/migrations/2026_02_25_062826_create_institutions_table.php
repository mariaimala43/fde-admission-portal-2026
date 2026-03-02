<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();

            // Location
            $table->foreignId('sector_id')
                  ->constrained('sectors')
                  ->onDelete('restrict');
            $table->foreignId('uc_id')
                  ->constrained('union_councils')
                  ->onDelete('restrict');

            // Basic Info
            $table->string('name');
            $table->string('code')->unique()->nullable(); // official school code
            $table->enum('type', [
                'primary',
                'middle',
                'high',
                'higher_secondary'
            ]);
            $table->enum('gender', [
                'boys',
                'girls',
                'co_education'
            ]);
            $table->enum('shift', [
                'morning',
                'evening',
                'both'
            ])->default('morning');

            // Address
            $table->text('address')->nullable();

            // Facilities (Yes/No flags)
            $table->boolean('has_matric_tech')->default(false);
            $table->boolean('has_transport')->default(false);
            $table->boolean('has_meal_program')->default(false);
            $table->boolean('has_evening_classes')->default(false);

            // Cambridge — system enforced, never editable via UI
            $table->boolean('is_cambridge')->default(false);

            // Admission Status
            $table->enum('admission_status', [
                'not_started',
                'open',
                'closed'
            ])->default('not_started');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
