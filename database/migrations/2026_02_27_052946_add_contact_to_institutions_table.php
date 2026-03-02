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
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('contact_number')->nullable()->after('address');
            $table->string('latitude')->nullable()->after('contact_number');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['contact_number', 'latitude', 'longitude']);
        });
    }
};
