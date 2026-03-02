<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Which classes each institution has + authorized seats
        Schema::create('institution_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->unsignedInteger('total_seats')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['institution_id', 'class_id']);
        });

        // Named sections per class per institution
        Schema::create('institution_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('name', 10); // A, B, C, etc.
            $table->unsignedTinyInteger('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['institution_id', 'class_id', 'name']);
        });

        // Add ECE flag to institutions
        Schema::table('institutions', function (Blueprint $table) {
            $table->boolean('has_ece')->default(false)->after('has_evening_classes');
            $table->boolean('classes_configured')->default(false)->after('has_ece');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_sections');
        Schema::dropIfExists('institution_classes');
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['has_ece', 'classes_configured']);
        });
    }
};
