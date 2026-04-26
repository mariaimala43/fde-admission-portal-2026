<?php

// SAVE AS: database/seeders/NewConstructionRoomsSeeder.php
// Run: php artisan db:seed --class=NewConstructionRoomsSeeder

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\NewConstructionRoom;

class NewConstructionRoomsSeeder extends Seeder
{
    /**
     * Data sourced from two FDE documents:
     *   - "List of Completed Newly Constructed Classrooms – 205 Rooms" (44 schools)
     *   - "New Construction Rooms (Near To Completion) – 56 Rooms"     (12 schools)
     *
     * Total: 56 schools / 261 rooms
     * All names are exact matches from the institutions table.
     */
    public function run(): void
    {
        $matched   = 0;
        $unmatched = [];

        foreach ($this->getData() as [$name, $rooms, $status]) {

            $institution = Institution::where('name', $name)->first();

            if (! $institution) {
                $unmatched[] = "[$status]  $name  — $rooms rooms";
                continue;
            }

            NewConstructionRoom::updateOrCreate(
                ['institution_id' => $institution->id],
                [
                    'rooms_total'         => $rooms,
                    'construction_status' => $status,
                    'source_document'     => $status === 'completed'
                        ? 'List of Completed Newly Constructed Classrooms – 205 Rooms'
                        : 'New Construction Rooms (Near To Completion) – 56 Rooms',
                ]
            );

            $matched++;
        }

        $this->command->info("✅  $matched schools added successfully.");

        if (count($unmatched)) {
            $this->command->warn('⚠️  Could not match ' . count($unmatched) . ' entries:');
            foreach ($unmatched as $line) {
                $this->command->line("   - $line");
            }
            $this->command->warn(
                "\n   Add missing schools via Admin → Institutions, then re-run the seeder."
            );
        }
    }

    // ── Master data ───────────────────────────────────────────────────────
    // Format: [exact_institution_name, rooms_total, status]
    // Names copied exactly from institutions table — no fuzzy matching needed.

    private function getData(): array
    {
        return [

            // ── COMPLETED — 44 schools / 205 rooms ───────────────────────
            ['IMS(I-V) E-7/4',                      4,  'completed'],
            ['IMS(I-V) F-6/3 ',                        3,  'completed'],   // add via Admin if missing
            ['IMS(I-V) No.1 I-9/1',                 3,  'completed'],
            ['IMCG, I-8/3',                          3,  'completed'],
            ['IMS(I-V) I-8/1',                       4,  'completed'],
            ['IMS(I-V) AIOU Colony',                 2,  'completed'],
            ['IMS(I-V) F-10/1',                      2,  'completed'],
            ['IMCCG (COM), F-10/3',                  2,  'completed'],
            ['IMCG, F-11/3',                         20, 'completed'],   // doc: "IMCB F-11/3"
            ['IMPC H-8 ISLAMABAD',                   20, 'completed'],   // doc: "IMPGCB H-8"
            ['IMS(I-V) No.1 G-10/2',                2,  'completed'],
            ['IMS(I-V) No.2 G-10/2',                4,  'completed'],
            ['IMS(I-V) G-10/3',                      2,  'completed'],
            ['IMSB(VI-X) G-11/2',                    2,  'completed'],
            ['IMSG(I-X) G-10/3',                     2,  'completed'],
            ['IMS(I-V) G-11/2',                      5,  'completed'],
            ['IMS(I-V) G-10/4',                      2,  'completed'],
            ['IMCB, H-9',                            20, 'completed'],
            ['IMCB, F-10/4',                         4,  'completed'],
            ['IMSB (I-V) D/Mai Nawab',               4,  'completed'],   // doc: "Dhoke Mai Nawab"
            ['IMSB (I-VIII), Koral',                 2,  'completed'],
            ['IMSB(I-V) CH. BANGIAL',                2,  'completed'],   // doc: "Channual Bangial"
            ['IMSB(I-V) JHANG SYDEN',                8,  'completed'],   // doc: "Jhang Syedan"
            ['IMSG (I-V) CHAPPAR Ghasota (F.A)',     4,  'completed'],   // doc: "Chapper Ghasota"
            ['IMSB(I-V)NILORE',                      3,  'completed'],
            ['IMSB(VI-X)JHANG SYDEN',                8,  'completed'],   // doc: "IMSB (VI-X) Jhang Syedan"
            ['IMCG,JAGIOT',                          3,  'completed'],
            ['IMCG,PEHOUNT',                         3,  'completed'],
            ['IMSB(I-V)PINDMISTRIAN',                2,  'completed'],   // doc: "Pind Mistrain"
            ['IMSG(I-V)TAMMA',                       1,  'completed'],
            ['IMSG(I-V)ALI PUR FRASH',               6,  'completed'],   // doc: "Alipur Frash"
            ['IMSG(I-V) SEVERA',                     2,  'completed'],   // doc: "Seevra"
            ['IMSG(I-VIII) KIJNAH',                  2,  'completed'],   // doc: "Kijjnah"
            ['IMSG(I-V) NILORE',                     6,  'completed'],
            ['IMSB(I-V) SIRRI',                      2,  'completed'],
            ['IMSG (I-V) CHOUNIAL BANGIAL',          1,  'completed'],   // doc: "IMSG Chanoul Bangail"
            ['IMCG (I-XII), MARGALLA TOWN',          6,  'completed'],   // doc: "IMCG Margalla"
            ['IMSB (I-V) Pind Parian',               2,  'completed'],
            ['IMSB (I-V) Golra',                     4,  'completed'],
            ['IMSG (I-V) Bheka Syedan',              4,  'completed'],   // doc: "Bekha Syedan"
            ['IMSG (I-V) Dhoke Hashoo',              9,  'completed'],
            ['IMSB (I-V) Tamman',                    2,  'completed'],
            ['IMSG (I-V) I-14/3',                    9,  'completed'],
            ['IMSG (I-V) Sarae Madhu',               4,  'completed'],   // doc: "Sarai Madhu"

            // ── NEAR COMPLETION — 12 schools / 56 rooms ──────────────────
            ['IMCG (PG), F-7/4, Islamabad',          5,  'near_completion'],  // doc: "IMCG (PG) F-7/4"
            ['IMSB(VI-X) G-10/3',                    6,  'near_completion'],
            ['IMSG(I-X) G-11/2',                     4,  'near_completion'],
            ['IMS(I-V) G-11/1',                      6,  'near_completion'],
            ['IMSG(VI-X) I-8/1',                     5,  'near_completion'],
            ['IMSG (I-X), SAID PUR',                 3,  'near_completion'],  // doc: "IMSG (I-X) SaidPur"
            ['IMSG (I-V) Humak',                     6,  'near_completion'],
            ['IMSG (I-V) CBR Colony',                3,  'near_completion'],
            ['IMSG(I-V) ALI PUR SOUTH',              3,  'near_completion'],  // doc: "Alipur South"
            ['IMSB(VI-X)TARLAI',                     5,  'near_completion'],  // doc: "IMSB (VI-X) Tarlai"
            ['IMSB(I-VIII) KIJNAH',                  6,  'near_completion'],  // doc: "Kijnah"
            ['IMSB (I-V) Sarae Karboza',             4,  'near_completion'],  // doc: "Sarai Kharbuza"
        ];
    }
}
