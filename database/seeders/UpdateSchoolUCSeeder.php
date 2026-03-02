<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Institution;
use App\Models\UnionCouncil;
use App\Models\Sector;

class UpdateSchoolUCSeeder extends Seeder
{
    public function run(): void
    {
        // ── UC Mapping: Document UC name → DB UC match ────────
        // Document uses different numbering, match by area name
        $ucMapping = [
            'UC-20 PIND BEWGAL'      => 'Pind Begwal',       // UC-07 in DB
            'UC-21 TUMAIR'           => 'Tumair',            // UC-08 in DB
            'UC-22 THANDA PANI'      => 'Thanda Pani',       // NEW - needs creation
            'UC-23 ALIPUR'           => 'Alipur',            // UC-20 in DB
            'UC-24 CHIRRAH'          => 'Charah',            // UC-09 in DB (Chirrah = Charah)
            'UC-25 KIRPA'            => 'Kirpa',             // UC-10 in DB
            'UC-47 TARLAI'           => 'Tarlai Kalan',      // UC-19 in DB
            'UC-68 SHAKRIAL'         => 'Shakrial',          // NEW - needs creation
            'UC-DHOKE KALA KHAN RWP' => 'Dhoke Kala Khan',   // NEW - needs creation
            'UC-KHANA DAK II RWP'    => 'Khana Dak',         // UC-18 in DB
        ];

        // ── Resolve UC IDs ───────────────────────────────────
        $ucIds = [];
        $maxCode = (int) UnionCouncil::max('id');
        $nextCode = max(52, $maxCode + 1);

        foreach ($ucMapping as $docUC => $dbName) {
            $uc = UnionCouncil::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($dbName) . '%'])->first();

            if ($uc) {
                $ucIds[$docUC] = $uc->id;
                $this->command->info("  Matched: {$docUC} → {$uc->name} (ID: {$uc->id})");
            } else {
                // Create new UC - assign to a default sector
                $defaultSector = Sector::first();

                $newUC = UnionCouncil::create([
                    'name'      => "UC-{$nextCode} {$dbName}",
                    'code'      => "UC-{$nextCode}",
                    'sector_id' => $defaultSector?->id,
                ]);
                $ucIds[$docUC] = $newUC->id;
                $this->command->warn("  Created: UC-{$nextCode} {$dbName} (ID: {$newUC->id})");
                $nextCode++;
            }
        }

        // ── Schools from document ────────────────────────────
        $schools = [
            ['name' => 'IMCG, PUNJGRAN',                  'uc' => 'UC-23 ALIPUR',           'gender' => 'girls',   'type' => 'I-VIII'],
            ['name' => 'IMCG, THANDA PANI (FA)',           'uc' => 'UC-22 THANDA PANI',      'gender' => 'girls',   'type' => 'I-XII'],
            ['name' => 'IMSB(I-VIII) DELLA',               'uc' => 'UC-24 CHIRRAH',          'gender' => 'boys',    'type' => 'I-VIII'],
            ['name' => 'IMSB(I-X) THANDA PANI',            'uc' => 'UC-22 THANDA PANI',      'gender' => 'boys',    'type' => 'I-X'],
            ['name' => 'IMSG(I-X) NEW SHAKRIAL',            'uc' => 'UC-68 SHAKRIAL',         'gender' => 'girls',   'type' => 'I-X'],
            ['name' => 'IMSG (I-VIII) KALIA (FA)',          'uc' => 'UC-24 CHIRRAH',          'gender' => 'girls',   'type' => 'I-VIII'],
            ['name' => 'IMSB(I-V) SHARIFABAD',             'uc' => 'UC-47 TARLAI',           'gender' => 'boys',    'type' => 'I-V'],
            ['name' => 'IMSB(I-V) KHADRAPPAR',             'uc' => 'UC-47 TARLAI',           'gender' => 'boys',    'type' => 'I-V'],
            ['name' => 'IMSB(I-V) CH. BANGIAL',            'uc' => 'UC-25 KIRPA',            'gender' => 'boys',    'type' => 'I-V'],
            ['name' => 'IMSB(I-VIII) KIJNAH',              'uc' => 'UC-21 TUMAIR',           'gender' => 'boys',    'type' => 'I-VIII'],
            ['name' => 'IMSB(I-V) MOHARA SOLINA',          'uc' => 'UC-24 CHIRRAH',          'gender' => 'boys',    'type' => 'I-V'],
            ['name' => 'IMSB(I-V) MOHARA',                 'uc' => 'UC-21 TUMAIR',           'gender' => 'boys',    'type' => 'I-V'],
            ['name' => 'IMSB(I-V) ARA',                    'uc' => 'UC-25 KIRPA',            'gender' => 'boys',    'type' => 'I-V'],
            ['name' => 'IMSB(I-V) KHANNA KAK',             'uc' => 'UC-DHOKE KALA KHAN RWP', 'gender' => 'boys',    'type' => 'I-V'],
            ['name' => 'IMSB(I-V) PINDMISTRIAN',           'uc' => 'UC-22 THANDA PANI',      'gender' => 'boys',    'type' => 'I-V'],
            ['name' => 'IMSG(I-V) SHAKRIAL',                'uc' => 'UC-KHANA DAK II RWP',    'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG(I-V) TAMMA',                   'uc' => 'UC-47 TARLAI',           'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG(I-V) SEVERA',                  'uc' => 'UC-20 PIND BEWGAL',      'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG (I-V) CHOUNIAL BANGIAL',      'uc' => 'UC-25 KIRPA',            'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG(I-V) HERNO',                   'uc' => 'UC-22 THANDA PANI',      'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG(I-X) DARKALA',                 'uc' => 'UC-22 THANDA PANI',      'gender' => 'girls',   'type' => 'I-X'],
            ['name' => 'IMSG(I-V) DHOK FATHALL',            'uc' => 'UC-21 TUMAIR',           'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG(I-VIII) KIJNAH',               'uc' => 'UC-21 TUMAIR',           'gender' => 'girls',   'type' => 'I-VIII'],
            ['name' => 'IMSG(I-V) SIMLY DAM',               'uc' => 'UC-21 TUMAIR',           'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG(I-V) KALIA',                   'uc' => 'UC-24 CHIRRAH',          'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG (I-V) CHAPPAR Ghasota',       'uc' => 'UC-24 CHIRRAH',          'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG(I-V) CHAKHTAN',                'uc' => 'UC-24 CHIRRAH',          'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSG(I-V) PUNJGRAN (760)',           'uc' => 'UC-23 ALIPUR',           'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSB(I-V) SIRRI',                   'uc' => 'UC-22 THANDA PANI',      'gender' => 'boys',    'type' => 'I-V'],
            ['name' => 'IMSG(I-V) FRASH TOWN',              'uc' => 'UC-23 ALIPUR',           'gender' => 'girls',   'type' => 'I-V'],
            ['name' => 'IMSB(I-V) BIATH',                   'uc' => 'UC-24 CHIRRAH',          'gender' => 'boys',    'type' => 'I-V'],
        ];

        $this->command->info("\n=== Adding/Updating 31 Schools ===\n");

        $added = 0;
        $updated = 0;

        foreach ($schools as $school) {
            $ucId = $ucIds[$school['uc']] ?? null;

            // Try to find existing institution by exact or close name
            $existing = Institution::whereRaw('LOWER(name) = ?', [strtolower($school['name'])])->first();

            // Also try a fuzzy match - extract the area name
            if (!$existing) {
                // Extract area name from school name (after the prefix)
                preg_match('/(?:IMCG|IMSB|IMSG)[\s,]*(?:\([\w-]+\))?\s*(.+)/i', $school['name'], $matches);
                $areaName = trim($matches[1] ?? '', ' ,');
                if ($areaName) {
                    $existing = Institution::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($areaName) . '%'])
                        ->where(function ($q) use ($school) {
                            if ($school['gender'] === 'boys') {
                                $q->whereRaw('LOWER(name) LIKE ?', ['%imsb%']);
                            } else {
                                $q->where(function ($q2) {
                                    $q2->whereRaw('LOWER(name) LIKE ?', ['%imsg%'])
                                       ->orWhereRaw('LOWER(name) LIKE ?', ['%imcg%']);
                                });
                            }
                        })
                        ->first();
                }
            }

            // Determine sector from the UC
            $ucRecord = UnionCouncil::find($ucId);
            $sectorId = $ucRecord?->sector_id;

            // If no sector from UC, use a default
            if (!$sectorId) {
                $sectorId = Sector::first()?->id;
            }

            if ($existing) {
                // Update existing school UC
                $existing->update(['uc_id' => $ucId]);
                $updated++;
                $this->command->info("  Updated: {$existing->name} → UC ID: {$ucId}");
            } else {
                // Create new institution
                Institution::create([
                    'name'      => $school['name'],
                    'type'      => $school['type'],
                    'gender'    => $school['gender'],
                    'sector_id' => $sectorId,
                    'uc_id'     => $ucId,
                    'is_active' => true,
                ]);
                $added++;
                $this->command->info("  Added: {$school['name']} → UC: {$school['uc']}");
            }
        }

        $this->command->info("\n=== Done: {$added} added, {$updated} updated ===\n");
    }
}
