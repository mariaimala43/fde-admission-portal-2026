<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix sector name and code
        DB::table('sectors')
            ->where('code', 'TARNOL')
            ->update(['name' => 'Tarnaul', 'code' => 'TARNAUL']);

        // Fix the one institution name that contains "Tarnol"
        DB::table('institutions')
            ->where('name', 'IMCB (VI-XII) Tarnol')
            ->update(['name' => 'IMCB (VI-XII) Tarnaul']);

        // Fix the union council display name
        DB::table('union_councils')
            ->where('name', 'UC-47 Tarnol')
            ->update(['name' => 'UC-47 Tarnaul']);
    }

    public function down(): void
    {
        DB::table('sectors')
            ->where('code', 'TARNAUL')
            ->update(['name' => 'Tarnol', 'code' => 'TARNOL']);

        DB::table('institutions')
            ->where('name', 'IMCB (VI-XII) Tarnaul')
            ->update(['name' => 'IMCB (VI-XII) Tarnol']);

        DB::table('union_councils')
            ->where('name', 'UC-47 Tarnaul')
            ->update(['name' => 'UC-47 Tarnol']);
    }
};
