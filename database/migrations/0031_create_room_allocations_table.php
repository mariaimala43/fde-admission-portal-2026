<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('new_construction_room_id')
                ->constrained('new_construction_rooms')
                ->cascadeOnDelete();

            $table->foreignId('institution_id')
                ->constrained('institutions')
                ->cascadeOnDelete();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('rooms_assigned');
            $table->string('purpose', 40)->default('classroom');
            $table->text('hoi_note')->nullable();
            $table->string('status', 20)->default('pending');

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();

            $table->unique(['institution_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_allocations');
    }
};
