<?php
// database/seeders/ModelCollegeSeeder.php
//
// RULES:
//   - UPDATE only — never INSERT new institution records
//   - Match by emis_code (stored in the `code` column on institutions table)
//   - If EMIS not found → warn and skip
//   - Sets: type, ib_number

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelCollegeSeeder extends Seeder
{
    // ── Model Colleges — 26 records ───────────────────────────────────────
    // emis_code => ib_number
    private const MODEL_COLLEGES = [
        '908' => '2755',
        '905' => '2758',
        '904' => '2854',
        '906' => '2861',
        '901' => '2858',
        '909' => '2862',
        '910' => '2853',
        '902' => '2756',
        '907' => '2850',
        '925' => '5141',
        '923' => '5145',
        '924' => '5142',
        '926' => '5140',
        '913' => '2859',
        '912' => '2763',
        '914' => '2860',
        '919' => '2762',
        '915' => '2856',
        '916' => '2851',
        '918' => '285',
        '911' => '2764',
        '917' => '2760',
        '920' => '2855',
        '903' => '2857',
        '922' => '5143',
        '921' => '5144',
    ];

    // ── Ex-FG Colleges — 4 records ────────────────────────────────────────
    // emis_code => ib_number
    private const EX_FG_COLLEGES = [
        '802' => '2524',
        '805' => '2869',
        '804' => '2522',
        '803' => '2520',
    ];

    // ─────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->tagInstitutions(self::MODEL_COLLEGES, 'Model College');
        $this->tagInstitutions(self::EX_FG_COLLEGES, 'Ex-FG College');

        // Assign all Model Colleges to the MODEL COLLEGES sector
        $modelSectorId = DB::table('sectors')->where('code', 'MODEL')->value('id');
        if ($modelSectorId) {
            DB::table('institutions')
                ->where('type', 'Model College')
                ->update(['sector_id' => $modelSectorId]);
            $this->command->info("  Assigned 26 Model Colleges to sector id={$modelSectorId}.");
        } else {
            $this->command->warn('  ⚠️  MODEL COLLEGES sector not found — run SectorSeeder first.');
        }

        $this->command->info('✅ ModelCollegeSeeder complete.');
    }

    // ─────────────────────────────────────────────────────────────────────

    private function tagInstitutions(array $map, string $type): void
    {
        $updated = 0;
        $skipped = 0;

        foreach ($map as $emisCode => $ibNumber) {
            // institutions.code stores the EMIS code
            $affected = DB::table('institutions')
                ->where('code', $emisCode)
                ->update([
                    'type'       => $type,
                    'ib_number'  => $ibNumber,
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                $this->command->warn("  ⚠️  EMIS {$emisCode} not found — skipped ({$type})");
                $skipped++;
            } else {
                $updated++;
            }
        }

        $this->command->info("  {$type}: {$updated} updated, {$skipped} skipped.");
    }
}
