<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Column may already exist from migration 2026_04_25_120913
        if (!Schema::hasColumn('institution_classes', 'matric_tech_existing')) {
            Schema::table('institution_classes', function (Blueprint $table) {
                $table->unsignedSmallInteger('matric_tech_existing')->default(0)->after('existing_enrollment');
            });
        }
    }

    public function down(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->dropColumn('matric_tech_existing');
        });
    }
};
