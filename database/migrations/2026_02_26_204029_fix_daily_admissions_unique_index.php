<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_admissions', function (Blueprint $table) {
            // Drop old unique index on section_id + date
            $table->dropUnique('unique_daily_admission');

            // Add correct unique index — one entry per class per institution per day
            $table->unique(
                ['institution_id', 'class_id', 'admission_date'],
                'unique_daily_class_admission'
            );
        });
    }

    public function down(): void
    {
        Schema::table('daily_admissions', function (Blueprint $table) {
            $table->dropUnique('unique_daily_class_admission');
        });
    }
};
