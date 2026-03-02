<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop sector_id from union_councils
        // (currently UC belongs to Sector — wrong)
        Schema::table('union_councils', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
        });

        // Step 2: Add uc_id to sectors
        // (Sector now belongs to UC — correct)
        Schema::table('sectors', function (Blueprint $table) {
            $table->foreignId('uc_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('union_councils')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->dropForeign(['uc_id']);
            $table->dropColumn('uc_id');
        });

        Schema::table('union_councils', function (Blueprint $table) {
            $table->foreignId('sector_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('sectors')
                  ->onDelete('restrict');
        });
    }
};
