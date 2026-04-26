<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('union_councils', function (Blueprint $table) {
            $table->id();
            // Final state: nullable (sector_id was removed then re-added as nullable)
            $table->foreignId('sector_id')
                  ->nullable()
                  ->constrained('sectors')
                  ->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('union_councils');
    }
};
