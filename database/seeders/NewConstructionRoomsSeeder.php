<?php

// SAVE AS: database/seeders/NewConstructionRoomsSeeder.php
// Run: php artisan db:seed --class=NewConstructionRoomsSeeder

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Institution;
use App\Models\NewConstructionRoom;

class NewConstructionRoomsSeeder extends Seeder
{
    /**
     * Data sourced from two FDE documents:
     *   - "List of Completed Newly Constructed Classrooms – 205 Rooms"
     *   - "New Construction Rooms (Near To Completion)" – 56 Rooms
     *
     * Matching strategy: fuzzy name match against institutions table.
     * Unmatched records are logged to storage/logs/unmatched_rooms.txt.
     */
    public function run(): void
    {
        $entries = $this->getData();

        $matched   = 0;
        $unmatched = [];

        foreach ($entries as $entry) {
            [$name, $sector, $rooms, $status] = $entry;

            // Try to find the institution by name similarity
            $institution = $this->findInstitution($name, $sector);

            if (! $institution) {
                $unmatched[] = "$name ($sector) — $rooms rooms";
                continue;
            }

            NewConstructionRoom::updateOrCreate(
                ['institution_id' => $institution->id],
                [
                    'rooms_total'           => $rooms,
                    'construction_status'   => $status,
                    'source_document'       => $status === 'completed'
                        ? 'List of Completed Newly Constructed Classrooms – 205 Rooms'
                        : 'New Construction Rooms (Near To Completion) – 56 Rooms',
                ]
            );

            $matched++;
        }

        $this->command->info("✅ Matched and seeded: $matched institutions");

        if (count($unmatched)) {
            $this->command->warn("⚠️  Could not match " . count($unmatched) . " entries:");
            foreach ($unmatched as $u) {
                $this->command->line("   - $u");
            }
            $this->command->warn(
                "   → These schools may not yet be in the institutions table.\n" .
                "     Add them manually via Admin → Institutions, then re-run the seeder."
            );
        }
    }

    /**
     * Find institution by trying multiple name strategies.
     * FDE institution names follow patterns like "IMSG (I-V) Alipur" or "IMCB, F-11/3"
     */
    private function findInstitution(string $name, string $sector): ?Institution
    {
        // Clean up the name for matching
        $cleanName = trim($name);

        // 1. Exact match
        $inst = Institution::whereRaw('LOWER(name) = ?', [strtolower($cleanName)])->first();
        if ($inst) return $inst;

        // 2. Institution name LIKE %name%
        $inst = Institution::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($cleanName) . '%'])->first();
        if ($inst) return $inst;

        // 3. Strip grade range (e.g. "(I-V)", "(VI-X)", "(I-VIII)") and try again
        $strippedName = preg_replace('/\s*\([IVX]+-[IVX]+\)\s*/i', ' ', $cleanName);
        $strippedName = trim(preg_replace('/\s+/', ' ', $strippedName));

        $inst = Institution::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($strippedName) . '%'])->first();
        if ($inst) return $inst;

        // 4. Use last meaningful word(s) as keyword
        $words = explode(' ', $strippedName);
        $keyword = implode(' ', array_slice($words, -2));
        if (strlen($keyword) > 3) {
            $inst = Institution::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($keyword) . '%'])->first();
            if ($inst) return $inst;
        }

        return null;
    }

    private function getData(): array
    {
        // [name, sector/cluster, rooms, status]
        return [
            // ── COMPLETED (205 rooms) ──────────────────────────────────────
            ['IMS (I-V) E-7',                   'Urban-I',   4,  'completed'],
            ['Kohsar School',                    'Urban-I',   3,  'completed'],
            ['IMS (I-V) No.1 I-9/1',            'URBAN-II',  3,  'completed'],
            ['IMCG I-8/3',                       'URBAN-II',  3,  'completed'],
            ['IMS (I-V) I-8/1',                  'URBAN-II',  4,  'completed'],
            ['IMS (I-V) AIOU Colony',            'URBAN-II',  2,  'completed'],
            ['IMS (I-V) F-10/1',                 'URBAN-II',  2,  'completed'],
            ['IMCCG F-10/3',                     'URBAN-II',  2,  'completed'],
            ['IMCB F-11/3',                      'URBAN-II',  20, 'completed'],
            ['IMPGCB H-8',                       'URBAN-II',  20, 'completed'],
            ['IMS (I-V) No.1 G-10/2',           'URBAN-II',  2,  'completed'],
            ['IMS (I-V) No.2 G-10/2',           'URBAN-II',  4,  'completed'],
            ['IMS (I-V) G-10/3',                 'URBAN-II',  2,  'completed'],
            ['IMSB (VI-X) G-11/2',              'URBAN-II',  2,  'completed'],
            ['IMSG (I-X) G-10/3',               'URBAN-II',  2,  'completed'],
            ['IMS (I-V) G-11/2',                 'URBAN-II',  5,  'completed'],
            ['IMS (I-V) G-10/4',                 'URBAN-II',  2,  'completed'],
            ['IMCB H-9',                         'URBAN-II',  20, 'completed'],
            ['IMCB F-10/4',                      'URBAN-II',  4,  'completed'],
            ['IMSB (I-V) Dhoke Mai Nawab',       'Sihala',    4,  'completed'],
            ['IMSB (I-VIII) Koral',              'Sihala',    2,  'completed'],
            ['IMSB (I-V) Channual Bangial',      'Nilore',    2,  'completed'],
            ['IMSB (I-V) Jhang Syedan',          'Nilore',    8,  'completed'],
            ['IMSG (I-V) Chapper Ghasota',       'Nilore',    4,  'completed'],
            ['IMSB (I-V) Nilore',                'Nilore',    3,  'completed'],
            ['IMSB (VI-X) Jhang Syedan',         'Nilore',    8,  'completed'],
            ['IMCG Jagiot',                      'Nilore',    3,  'completed'],
            ['IMCG Pehount',                     'Nilore',    3,  'completed'],
            ['IMSB (I-V) Pind Mistrain',         'Nilore',    2,  'completed'],
            ['IMSG (I-V) Tamma',                 'Nilore',    1,  'completed'],
            ['IMSG (I-V) Alipur Frash',          'Nilore',    6,  'completed'],
            ['IMSG (I-V) Seevra',                'Nilore',    2,  'completed'],
            ['IMSG (I-VIII) Kijjnah',            'Nilore',    2,  'completed'],
            ['IMSG (I-V) Nilore',                'Nilore',    6,  'completed'],
            ['IMSB (I-V) Sirri',                 'Nilore',    2,  'completed'],
            ['IMSG Chanoul Bangail',             'Nilore',    1,  'completed'],
            ['IMCG Margalla',                    'Barakahu',  6,  'completed'],
            ['IMSB (I-V) Pind Parian',           'Tarnol',    2,  'completed'],
            ['IMSB (I-V) Golra',                 'Tarnol',    4,  'completed'],
            ['IMSG (I-V) Bekha Syedan',          'Tarnol',    4,  'completed'],
            ['IMSG (I-V) Dhoke Hashoo',          'Tarnol',    9,  'completed'],
            ['IMSB (I-V) Tamman',                'Tarnol',    2,  'completed'],
            ['IMSG (I-V) I-14/3',                'Tarnol',    9,  'completed'],
            ['IMSG (I-V) Sarai Madhu',           'Tarnol',    4,  'completed'],

            // ── NEAR COMPLETION (56 rooms) ─────────────────────────────────
            ['IMCG (PG) F-7/4',                  'URBAN-I',   5,  'near_completion'],
            ['IMSB (VI-X) G-10/3',              'URBAN-II',  6,  'near_completion'],
            ['IMSG (I-X) G-11/2',               'URBAN-II',  4,  'near_completion'],
            ['IMS (I-V) G-11/1',                 'URBAN-II',  6,  'near_completion'],
            ['IMSG (VI-X) I-8/1',               'URBAN-II',  5,  'near_completion'],
            ['IMSG (I-X) SaidPur',               'Bara Kahu', 3,  'near_completion'],
            ['IMSG (I-V) Humak',                 'Sihala',    6,  'near_completion'],
            ['IMSG (I-V) CBR Colony',            'Sihala',    3,  'near_completion'],
            ['IMSG (I-V) Alipur South',          'Nilore',    3,  'near_completion'],
            ['IMSB (VI-X) Tarlai',              'Nilore',    5,  'near_completion'],
            ['IMSB (I-VIII) Kijnah',             'Nilore',    6,  'near_completion'],
            ['IMSB (I-V) Sarai Kharbuza',        'Tarnol',    4,  'near_completion'],
        ];
    }
}
