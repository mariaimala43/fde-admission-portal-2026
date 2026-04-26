<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->unsignedSmallInteger('matric_tech_existing')
                  ->default(0)
                  ->after('existing_enrollment')
                  ->comment('Existing Matric Tech students (Class 9 & 10 only, when institution has_matric_tech)');
        });
    }

    public function down(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->dropColumn('matric_tech_existing');
        });
    }
};
