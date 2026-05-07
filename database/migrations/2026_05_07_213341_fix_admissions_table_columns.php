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
        Schema::table('admissions', function (Blueprint $table) {
            // parent_contact may not always be available (OutOfSchoolChild join can return null)
            $table->string('parent_contact')->nullable()->change();

            // NFEMIS stores gender as numeric (1=male, 2=female) not enum string
            $table->string('child_gender')->nullable()->change();

            // DOB may be stored differently in NFEMIS, make nullable
            $table->date('child_dob')->nullable()->change();

            // Drop old FK to schools table, add institution_id FK to institutions
            $table->dropForeign(['school_id']);
            $table->renameColumn('school_id', 'institution_id');
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->foreign('institution_id')->references('id')->on('institutions');
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->renameColumn('institution_id', 'school_id');
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools');
            $table->string('parent_contact')->nullable(false)->change();
        });
    }
};
