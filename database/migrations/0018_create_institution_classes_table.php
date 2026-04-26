<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->unsignedInteger('total_seats')->default(0);
            $table->unsignedInteger('existing_enrollment')->default(0);
            $table->unsignedInteger('promoted_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->enum('enrollment_status', [
                'draft',
                'submitted',
                'verified',
                'returned',
                'locked',
            ])->default('draft');
            $table->foreignId('verified_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['institution_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_classes');
    }
};
