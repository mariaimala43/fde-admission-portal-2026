<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            // NFEMIS School.SchoolID — unique integer, directly matches StudentAdmissionRegister.SchoolID
            // More reliable than SchoolCode which has duplicates in NFEMIS data
            $table->unsignedInteger('nfemis_school_id')->nullable()->unique()->after('emis_code')
                  ->comment('NFEMIS School.SchoolID — used to match StudentAdmissionRegister referrals');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn('nfemis_school_id');
        });
    }
};
