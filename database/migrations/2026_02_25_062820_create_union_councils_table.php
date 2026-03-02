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
            $table->foreignId('sector_id')
                  ->constrained('sectors')
                  ->onDelete('restrict');
            $table->string('name');
            $table->string('code')->unique();        // e.g. UC-1, UC-2
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('union_councils');
    }
};
