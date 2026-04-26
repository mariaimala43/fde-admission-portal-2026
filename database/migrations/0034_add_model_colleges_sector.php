<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Step A — add HOI columns to institutions ──────────────────────────
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('hoi_name', 150)->nullable()->after('ib_number');
            $table->string('hoi_contact', 60)->nullable()->after('hoi_name');
        });

        // ── Step B — create the MODEL COLLEGES sector ─────────────────────────
        $sectorId = DB::table('sectors')->insertGetId([
            'name'       => 'MODEL COLLEGES',
            'code'       => 'MODEL',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Step C — move all 26 Model Colleges to the new sector ─────────────
        DB::table('institutions')
            ->where('type', 'Model College')
            ->update(['sector_id' => $sectorId]);

        // ── Step D — backfill hoi_name / hoi_contact from HOI user records ────
        $modelCollegeIds = DB::table('institutions')
            ->where('type', 'Model College')
            ->pluck('id');

        foreach ($modelCollegeIds as $instId) {
            $hoiUser = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'hoi')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->where('users.institution_id', $instId)
                ->whereNotNull('users.phone')
                ->select('users.name', 'users.phone')
                ->first();

            if ($hoiUser) {
                DB::table('institutions')->where('id', $instId)->update([
                    'hoi_name'    => $hoiUser->name,
                    'hoi_contact' => $hoiUser->phone,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Remove MODEL COLLEGES sector (institutions.sector_id will become NULL via FK nullable)
        DB::table('institutions')
            ->whereIn('sector_id', DB::table('sectors')->where('code', 'MODEL')->pluck('id'))
            ->update(['sector_id' => null, 'hoi_name' => null, 'hoi_contact' => null]);

        DB::table('sectors')->where('code', 'MODEL')->delete();

        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['hoi_name', 'hoi_contact']);
        });
    }
};
