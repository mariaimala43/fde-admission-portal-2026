<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            // Drop global unique constraint on code
            $table->dropUnique(['code']);

            // Add composite unique — same code allowed in different UCs
            $table->unique(['uc_id', 'code'], 'unique_sector_code_per_uc');
        });
    }

    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->dropUnique('unique_sector_code_per_uc');
            $table->unique('code');
        });
    }
};
