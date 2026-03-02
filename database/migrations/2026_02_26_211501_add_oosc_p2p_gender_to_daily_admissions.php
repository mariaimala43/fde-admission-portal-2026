<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_admissions', function (Blueprint $table) {
            // Remove old single columns
            $table->dropColumn(['oosc_count', 'private_to_public_count']);

            // OOSC boys/girls
            $table->unsignedInteger('oosc_boys')->default(0)->after('girls_count');
            $table->unsignedInteger('oosc_girls')->default(0)->after('oosc_boys');

            // Private→Public boys/girls
            $table->unsignedInteger('p2p_boys')->default(0)->after('oosc_girls');
            $table->unsignedInteger('p2p_girls')->default(0)->after('p2p_boys');
        });
    }

    public function down(): void
    {
        Schema::table('daily_admissions', function (Blueprint $table) {
            $table->dropColumn(['oosc_boys', 'oosc_girls', 'p2p_boys', 'p2p_girls']);
            $table->unsignedInteger('oosc_count')->default(0);
            $table->unsignedInteger('private_to_public_count')->default(0);
        });
    }
};
