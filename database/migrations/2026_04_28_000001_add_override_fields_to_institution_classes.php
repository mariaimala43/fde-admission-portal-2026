<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            if (!Schema::hasColumn('institution_classes', 'overridden_by')) {
                $table->unsignedBigInteger('overridden_by')->nullable()->after('enrollment_status');
            }
            if (!Schema::hasColumn('institution_classes', 'override_reason')) {
                $table->string('override_reason', 500)->nullable()->after('overridden_by');
            }
            if (!Schema::hasColumn('institution_classes', 'overridden_at')) {
                $table->timestamp('overridden_at')->nullable()->after('override_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->dropColumn(['overridden_by', 'override_reason', 'overridden_at']);
        });
    }
};
