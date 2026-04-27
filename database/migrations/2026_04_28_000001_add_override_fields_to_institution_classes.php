<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('overridden_by')->nullable()->after('enrollment_status');
            $table->string('override_reason', 500)->nullable()->after('overridden_by');
            $table->timestamp('overridden_at')->nullable()->after('override_reason');
        });
    }

    public function down(): void
    {
        Schema::table('institution_classes', function (Blueprint $table) {
            $table->dropColumn(['overridden_by', 'override_reason', 'overridden_at']);
        });
    }
};
