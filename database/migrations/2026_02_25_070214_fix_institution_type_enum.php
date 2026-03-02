<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->enum('type', [
                'I-V',
                'I-VIII',
                'I-X',
                'I-XII',
                'VI-VIII',
                'VI-X',
                'VI-XII',
                'Model College'
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->enum('type', [
                'primary',
                'middle',
                'high',
                'higher_secondary'
            ])->change();
        });
    }
};
