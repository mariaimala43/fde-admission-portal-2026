<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_admissions', function (Blueprint $table) {
            // section_id no longer required — per class only
            $table->dropForeign(['section_id']);
            $table->unsignedBigInteger('section_id')->nullable()->change();

            // Replace morning/evening with boys/girls
            $table->dropColumn(['morning_admissions', 'evening_admissions']);
            $table->unsignedInteger('boys_count')->default(0)->after('admission_date');
            $table->unsignedInteger('girls_count')->default(0)->after('boys_count');
        });
    }

    public function down(): void
    {
        Schema::table('daily_admissions', function (Blueprint $table) {
            $table->unsignedInteger('morning_admissions')->default(0);
            $table->unsignedInteger('evening_admissions')->default(0);
            $table->dropColumn(['boys_count', 'girls_count']);
        });
    }
};
