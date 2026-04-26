<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aeo_sectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('sector_id')
                  ->constrained('sectors')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'sector_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aeo_sectors');
    }
};
