<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('order');
            $table->boolean('is_ece')->default(false);
            $table->enum('level', [
                'ece',
                'primary',
                'middle',
                'high',
                'higher_secondary',
            ])->default('primary');
            $table->enum('type', [
                'I-V',
                'I-VIII',
                'I-X',
                'I-XII',
                'VI-VIII',
                'VI-X',
                'VI-XII',
                'Model_College',
            ]);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
