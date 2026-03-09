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
        Schema::table('daily_admissions', function (Blueprint $table) {
            $table->unsignedSmallInteger('morning_oosc_boys')->default(0)->after('morning_girls');
            $table->unsignedSmallInteger('morning_oosc_girls')->default(0)->after('morning_oosc_boys');
            $table->unsignedSmallInteger('morning_p2p_boys')->default(0)->after('morning_oosc_girls');
            $table->unsignedSmallInteger('morning_p2p_girls')->default(0)->after('morning_p2p_boys');
            $table->unsignedSmallInteger('evening_oosc_boys')->default(0)->after('evening_girls');
            $table->unsignedSmallInteger('evening_oosc_girls')->default(0)->after('evening_oosc_boys');
            $table->unsignedSmallInteger('evening_p2p_boys')->default(0)->after('evening_oosc_girls');
            $table->unsignedSmallInteger('evening_p2p_girls')->default(0)->after('evening_p2p_boys');
        });
    }

    public function down(): void
    {
        Schema::table('daily_admissions', function (Blueprint $table) {
            $table->dropColumn([
                'morning_oosc_boys','morning_oosc_girls',
                'morning_p2p_boys','morning_p2p_girls',
                'evening_oosc_boys','evening_oosc_girls',
                'evening_p2p_boys','evening_p2p_girls',
            ]);
        });
    }
};
