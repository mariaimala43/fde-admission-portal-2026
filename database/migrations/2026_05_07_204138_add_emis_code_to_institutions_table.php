<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('emis_code')->nullable()->unique()->after('name')
                  ->comment('NFEMIS SchoolCode — used to match NFEMIS referrals to FDE schools');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn('emis_code');
        });
    }
};
