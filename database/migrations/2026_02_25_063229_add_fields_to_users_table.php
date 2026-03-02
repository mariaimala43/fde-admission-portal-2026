<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institution_id')
                  ->nullable()
                  ->constrained('institutions')
                  ->onDelete('set null')
                  ->after('email');           // HoI: linked to one school
            $table->boolean('is_active')
                  ->default(true)
                  ->after('institution_id');
            $table->string('phone')
                  ->nullable()
                  ->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn(['institution_id', 'is_active', 'phone']);
        });
    }
};
