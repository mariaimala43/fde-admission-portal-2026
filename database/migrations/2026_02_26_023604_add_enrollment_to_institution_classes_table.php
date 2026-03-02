<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->unsignedInteger('existing_enrollment')->default(0)->after('total_seats');
            $table->enum('enrollment_status', [
                'draft',
                'submitted',
                'locked'
            ])->default('draft')->after('existing_enrollment');
        });
    }

    public function down(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->dropColumn(['existing_enrollment', 'enrollment_status']);
        });
    }
};
