<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_post_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('section');                 // 'teaching' or 'program'
            $table->string('category')->nullable();    // 'principal','vp','sst','set','est','program'
            $table->json('applicable_levels');         // JSON array of school_level values
            $table->boolean('has_full_columns')->default(true); // false = program posts
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_post_types');
    }
};
