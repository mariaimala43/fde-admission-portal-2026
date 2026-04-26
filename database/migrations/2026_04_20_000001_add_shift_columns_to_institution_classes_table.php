<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            // Per-shift seat capacity (evening schools only; non-evening stays 0)
            $table->unsignedInteger('morning_seats')->default(0)->after('total_seats');
            $table->unsignedInteger('evening_seats')->default(0)->after('morning_seats');

            // Per-shift existing enrollment (baseline from Class Setup)
            $table->unsignedInteger('morning_existing')->default(0)->after('existing_enrollment');
            $table->unsignedInteger('evening_existing')->default(0)->after('morning_existing');

            // Per-shift promoted / failed breakdown (from Baseline Enrollment)
            $table->unsignedInteger('morning_promoted')->default(0)->after('promoted_count');
            $table->unsignedInteger('morning_failed')->default(0)->after('morning_promoted');
            $table->unsignedInteger('evening_promoted')->default(0)->after('morning_failed');
            $table->unsignedInteger('evening_failed')->default(0)->after('evening_promoted');
        });
    }

    public function down(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->dropColumn([
                'morning_seats', 'evening_seats',
                'morning_existing', 'evening_existing',
                'morning_promoted', 'morning_failed',
                'evening_promoted', 'evening_failed',
            ]);
        });
    }
};
