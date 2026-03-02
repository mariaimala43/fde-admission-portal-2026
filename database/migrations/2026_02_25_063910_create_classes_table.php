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
            $table->string('name');         // "Class 1", "Class 2" ... "Class 12"
            $table->integer('order');       // 1, 2, 3 ... 12 (for sorting)
            $table->enum('type', [
                            'I-V',
                            'I-VIII',
                            'I-X',
                            'I-XII',
                            'VI-VIII',
                            'VI-X',
                            'VI-XII',
                            'Model_College'
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
