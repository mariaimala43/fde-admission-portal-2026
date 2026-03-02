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
            $table->foreignId('user_id')              // the AEO user
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('sector_id')
                  ->constrained('sectors')
                  ->onDelete('cascade');
            $table->timestamps();

            // One AEO cannot be assigned to same sector twice
            $table->unique(['user_id', 'sector_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aeo_sectors');
    }
};
