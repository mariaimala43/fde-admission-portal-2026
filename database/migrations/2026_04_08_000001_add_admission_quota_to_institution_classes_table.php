<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            // HOI-set admission target for the year (null = no cap)
            $table->unsignedSmallInteger('admission_quota')->nullable()->after('existing_enrollment');
        });
    }

    public function down(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->dropColumn('admission_quota');
        });
    }
};
