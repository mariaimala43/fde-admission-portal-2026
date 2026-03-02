<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add sector_id to union_councils
        Schema::table('union_councils', function (Blueprint $table) {
            $table->foreignId('sector_id')
                  ->nullable()
                  ->after('code')
                  ->constrained('sectors')
                  ->onDelete('set null');
        });

        // Remove uc_id from sectors
        Schema::table('sectors', function (Blueprint $table) {
            $table->dropForeign(['uc_id']);
            $table->dropColumn('uc_id');
        });
    }

    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->foreignId('uc_id')->nullable()
                  ->constrained('union_councils')->onDelete('set null');
        });

        Schema::table('union_councils', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
        });
    }
};
