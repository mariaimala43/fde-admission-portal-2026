<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Assign correct sector_id to every union_council row.
     *
     * UnionCouncilSeeder had two bugs:
     *   1. Sector cache was keyed by 'code' but lookups used 'name' → all nulls
     *   2. Tarnol UCs used the typo name 'Tarnaul' → never resolved
     *
     * This migration applies the definitive UC→Sector mapping directly.
     */
    public function up(): void
    {
        // Load sector IDs by name (what the seeder's ucSectorMap values are)
        $sectors = DB::table('sectors')->pluck('id', 'name');

        // UC code → Sector name  (matches UnionCouncilSeeder::$ucSectorMap)
        $ucSectorMap = [
            'UC-01' => 'B.K',     'UC-02' => 'B.K',     'UC-03' => 'B.K',
            'UC-04' => 'B.K',     'UC-05' => 'B.K',     'UC-06' => 'B.K',
            'UC-07' => 'B.K',     'UC-08' => 'B.K',     'UC-09' => 'B.K',
            'UC-10' => 'B.K',     'UC-11' => 'B.K',
            'UC-12' => 'Nilore',  'UC-13' => 'Nilore',
            'UC-14' => 'Sihala',  'UC-15' => 'Sihala',  'UC-16' => 'Sihala',
            'UC-17' => 'Nilore',  'UC-18' => 'Nilore',
            'UC-19' => 'Tarnol',  'UC-20' => 'Tarnol',
            'UC-21' => 'Sihala',
            'UC-22' => 'B.K',     'UC-23' => 'B.K',     'UC-24' => 'B.K',
            'UC-25' => 'Urban-I', 'UC-26' => 'Urban-I', 'UC-27' => 'Urban-I',
            'UC-28' => 'Urban-I', 'UC-29' => 'Urban-I', 'UC-30' => 'Urban-I',
            'UC-31' => 'Urban-I', 'UC-32' => 'Urban-I', 'UC-33' => 'Urban-I',
            'UC-34' => 'Urban-II','UC-35' => 'Urban-II','UC-36' => 'Urban-II',
            'UC-37' => 'Urban-II','UC-38' => 'Urban-II','UC-39' => 'Urban-II',
            'UC-40' => 'Urban-II','UC-41' => 'Urban-II','UC-42' => 'Urban-II',
            'UC-43' => 'Urban-II','UC-44' => 'Urban-II',
            'UC-45' => 'Tarnol',  'UC-46' => 'Tarnol',  'UC-47' => 'Tarnol',
            'UC-48' => 'Tarnol',  'UC-49' => 'Tarnol',  'UC-50' => 'Tarnol',
            'UC-52' => 'Nilore',  'UC-53' => 'Nilore',
            'UC-54' => 'Nilore',  'UC-55' => 'Nilore',
        ];

        foreach ($ucSectorMap as $ucCode => $sectorName) {
            $sectorId = $sectors[$sectorName] ?? null;
            if (!$sectorId) continue;

            DB::table('union_councils')
                ->where('code', $ucCode)
                ->update(['sector_id' => $sectorId]);
        }

        // NOTE: We intentionally do NOT cascade to institutions here.
        // Existing institution.sector_id values were set correctly by the original
        // data import and must be preserved. The InstitutionController already
        // derives sector_id from UC when creating or updating an institution,
        // so new/updated records will always be consistent.
        // If institutions have a NULL sector_id they were imported with, fix those only:
        DB::statement("
            UPDATE institutions i
            JOIN union_councils uc ON uc.id = i.uc_id
            SET i.sector_id = uc.sector_id
            WHERE uc.sector_id IS NOT NULL
              AND i.sector_id IS NULL
        ");
    }

    public function down(): void
    {
        // Revert — set all UC sector_ids back to null (original broken state)
        DB::table('union_councils')->update(['sector_id' => null]);
    }
};
