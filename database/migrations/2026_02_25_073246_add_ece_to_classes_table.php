<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add ECE level to classes enum
        Schema::table('classes', function (Blueprint $table) {
            $table->enum('level', [
                'ece',
                'primary',
                'middle',
                'high',
                'higher_secondary'
            ])->default('primary')->change();
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->enum('level', [
                'primary',
                'middle',
                'high',
                'higher_secondary'
            ])->default('primary')->change();
        });
    }
};
