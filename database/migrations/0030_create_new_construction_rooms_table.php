<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('new_construction_rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institution_id')
                ->constrained('institutions')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('rooms_total');
            $table->unsignedSmallInteger('rooms_allocated')->default(0);
            $table->string('construction_status', 30)->default('completed');
            $table->string('source_document', 120)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('new_construction_rooms');
    }
};
