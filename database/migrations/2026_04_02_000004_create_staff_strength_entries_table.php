<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_strength_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('register_id')
                  ->constrained('staff_strength_registers')->cascadeOnDelete();
            $table->foreignId('post_type_id')
                  ->constrained('staff_post_types')->cascadeOnDelete();

            // Section A — full columns (teaching posts)
            $table->unsignedSmallInteger('sanctioned_posts')->default(0);
            $table->unsignedSmallInteger('filled_posts')->default(0);
            $table->unsignedSmallInteger('sacked_employees')->default(0);
            $table->unsignedSmallInteger('daily_wagers_in')->default(0);
            $table->unsignedSmallInteger('daily_wagers_out')->default(0);
            $table->unsignedSmallInteger('study_leave')->default(0);
            $table->unsignedSmallInteger('deputationist_in')->default(0);
            $table->unsignedSmallInteger('deputationist_out')->default(0);
            $table->unsignedSmallInteger('temporary_in')->default(0);
            $table->unsignedSmallInteger('temporary_out')->default(0);

            // Section B — program posts (count only)
            $table->unsignedSmallInteger('number_of_posts')->default(0);

            $table->timestamps();

            $table->unique(['register_id', 'post_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_strength_entries');
    }
};
