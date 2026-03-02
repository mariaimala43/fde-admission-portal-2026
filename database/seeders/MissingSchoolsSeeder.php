<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\UnionCouncil;

class MissingSchoolsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Add UC-51 Nilore ───────────────────────────────
        $niloreUc = UnionCouncil::firstOrCreate(
            ['code' => 'UC-51'],
            [
                'name'      => 'UC-51 Nilore',
                'sector_id' => Sector::where('code', 'NILORE')->value('id'),
                'is_active' => true,
            ]
        );

        $this->command->info('UC-51 Nilore added.');

        // ── 88 Missing Schools ─────────────────────────────
        $schools = [
            ['emis' => 461, 'name' => 'IMSG (I-VIII), BAIN NALA', 'gender' => 'girls', 'type' => 'I-VIII', 'sector' => 'B.K'],
            ['emis' => 462, 'name' => 'IMSG (I-VIII) Mandla (FA)', 'gender' => 'girls', 'type' => 'I-VIII', 'sector' => 'B.K'],
            ['emis' => 463, 'name' => 'IMSG (I-X), MOHRA NOOR', 'gender' => 'girls', 'type' => 'I-X', 'sector' => 'B.K'],
            ['emis' => 464, 'name' => 'IMSG (I-VIII), BHARA KAU', 'gender' => 'girls', 'type' => 'I-VIII', 'sector' => 'B.K'],
            ['emis' => 465, 'name' => 'IMSG (I-VIII), BOBRI', 'gender' => 'girls', 'type' => 'I-VIII', 'sector' => 'B.K'],
            ['emis' => 466, 'name' => 'IMSG (I-VIII), KOT HATHIAL', 'gender' => 'girls', 'type' => 'I-VIII', 'sector' => 'B.K'],
            ['emis' => 467, 'name' => 'IMSG (I-VIII), SANJALIAN', 'gender' => 'girls', 'type' => 'I-VIII', 'sector' => 'B.K'],
            ['emis' => 468, 'name' => 'IMSG (I-V), ATHAL', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 469, 'name' => 'IMSG (I-V), (NHC) CHAK SHEHZAD', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 470, 'name' => 'IMSG (I-V), DHOKE JERRANI', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 471, 'name' => 'IMSG (I-V), KOT HATHIAL, NAI ABADI', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 472, 'name' => 'IMSG (I-VIII), MOHRIAN', 'gender' => 'girls', 'type' => 'I-VIII', 'sector' => 'B.K'],
            ['emis' => 473, 'name' => 'IMSG (I-V), PIND BEGWAL, DANA', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 474, 'name' => 'IMSG (I-V), SHAH PUR', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 475, 'name' => 'IMSG (I-V), SUBBAN', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 476, 'name' => 'IMSG (I-V) , SHAHZAD TOWN', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 477, 'name' => 'IMSG (I-V), BHARA KAU, NAI ABADI', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 478, 'name' => 'IMSG (I-V), MALPUR (F.A)', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 479, 'name' => 'IMSG (I-V) Maira Malpur', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'B.K'],
            ['emis' => 554, 'name' => 'IMSB (I-VIII) Ara Burji', 'gender' => 'boys', 'type' => 'I-VIII', 'sector' => 'Sihala'],
            ['emis' => 555, 'name' => 'IMSB (I-V) Humak', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 556, 'name' => 'IMSB (I-VIII)S/Mirzian', 'gender' => 'boys', 'type' => 'I-VIII', 'sector' => 'Sihala'],
            ['emis' => 557, 'name' => 'IMSB (I-V) Mughal', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 558, 'name' => 'IMSB (I-VIII) Herdogher', 'gender' => 'boys', 'type' => 'I-VIII', 'sector' => 'Sihala'],
            ['emis' => 559, 'name' => 'IMSB (I-V) Darwala', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 560, 'name' => 'IMSB (I-V) Boora Bangial', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 561, 'name' => 'IMSB (I-V) Pind Malkan', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 562, 'name' => 'IMSB (I-V) Mohra Kalu', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 563, 'name' => 'IMSB (I-V) D/Mai Nawab', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 564, 'name' => 'IMSB (I-V) Rajwal', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 565, 'name' => 'IMSB (I-V) Kortana', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 566, 'name' => 'IMSB (I-V) Bhangril', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 567, 'name' => 'IMSB (I-V) Chak', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 568, 'name' => 'IMSB (I-V) Mohri Rawat', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 569, 'name' => 'IMSB (I-VIII), Koral', 'gender' => 'boys', 'type' => 'I-VIII', 'sector' => 'Sihala'],
            ['emis' => 570, 'name' => 'IMSB (I-VIII) Nara Syedan', 'gender' => 'boys', 'type' => 'I-VIII', 'sector' => 'Sihala'],
            ['emis' => 571, 'name' => 'IMSB (I-V)Chak Kamdar', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 572, 'name' => 'IMSB (I-V). Sigga', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 573, 'name' => 'IMSG (I-V) CBR Colony', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 574, 'name' => 'IMS (I-V) Soan Garden, Lohi Bheer', 'gender' => 'boys', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 575, 'name' => 'IMS (I-V) Gohra Shahan', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'Sihala'],
            ['emis' => 648, 'name' => 'IMSG (I-V) Bheka Syedan', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'Tarnol'],
            ['emis' => 649, 'name' => 'IMSG (I-V) Pind Parian', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'Tarnol'],
            ['emis' => 650, 'name' => 'IMSG (I-V) Sheikhpur', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'Tarnol'],
            ['emis' => 651, 'name' => 'IMSG (I-V) Dhoke Hashoo', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'Tarnol'],
            ['emis' => 652, 'name' => 'IMSG (I-V) Dhoke Suleman', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'Tarnol'],
            ['emis' => 653, 'name' => 'IMSG (I-V) Sarae Madhu', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'Tarnol'],
            ['emis' => 654, 'name' => 'IMSG (I-V) I-14/3', 'gender' => 'girls', 'type' => 'I-V', 'sector' => 'Tarnol'],
            ['emis' => 655, 'name' => 'IMS (I-VIII) D-17', 'gender' => 'co_education', 'type' => 'I-VIII', 'sector' => 'Tarnol'],
            ['emis' => 801, 'name' => 'IMCB, SIHALA, Islamabad.', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Sihala'],
            ['emis' => 802, 'name' => 'IMPC H-8 ISLAMABAD', 'gender' => 'boys', 'type' => 'Model College', 'sector' => 'Urban-II'],
            ['emis' => 803, 'name' => 'IMCB, F-10/4', 'gender' => 'boys', 'type' => 'Model College', 'sector' => 'Urban-II'],
            ['emis' => 804, 'name' => 'IMCB, H-9', 'gender' => 'boys', 'type' => 'Model College', 'sector' => 'Urban-II'],
            ['emis' => 805, 'name' => 'IMPCC (B), H-8/4', 'gender' => 'boys', 'type' => 'Model College', 'sector' => 'Urban-II'],
            ['emis' => 806, 'name' => 'IMCG (PG), F-7/2', 'gender' => 'girls', 'type' => 'Model College', 'sector' => 'Urban-I'],
            ['emis' => 807, 'name' => 'IMCG(PG), G-10/4', 'gender' => 'girls', 'type' => 'Model College', 'sector' => 'Urban-II'],
            ['emis' => 808, 'name' => 'IMCG (MT) Humak', 'gender' => 'girls', 'type' => 'Model College', 'sector' => 'Sihala'],
            ['emis' => 809, 'name' => 'IMCG, I-8/3', 'gender' => 'girls', 'type' => 'Model College', 'sector' => 'Urban-II'],
            ['emis' => 810, 'name' => 'IMCG (PG), F-7/4, Islamabad', 'gender' => 'girls', 'type' => 'Model College', 'sector' => 'Urban-I'],
            ['emis' => 811, 'name' => 'IMCG I-14/3', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Tarnol'],
            ['emis' => 812, 'name' => 'IMCG (PG) Bharakahu Islamabad', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'B.K'],
            ['emis' => 813, 'name' => 'HE&MC F-11/1', 'gender' => 'girls', 'type' => 'Model College', 'sector' => 'Urban-II'],
            ['emis' => 901, 'name' => 'IMCB, F-10/3', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 902, 'name' => 'IMCB, F-11/1', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 903, 'name' => 'IMCG, F-11/3', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 904, 'name' => 'IMCB, F-7/3', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Urban-I'],
            ['emis' => 905, 'name' => 'IMCB, F-8/4', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Urban-I'],
            ['emis' => 906, 'name' => 'IMCB, G-10/4', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 907, 'name' => 'IMCB, G-11/1', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 908, 'name' => 'ICB G-6/3', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Urban-I'],
            ['emis' => 909, 'name' => 'IMCB, I-10/1', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 910, 'name' => 'IMCB, I-8/3', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 911, 'name' => 'IMCCG (COM), F-10/3', 'gender' => 'girls', 'type' => 'Model College', 'sector' => 'Urban-II'],
            ['emis' => 912, 'name' => 'IMCG, ST. 25,  F-6/2', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Urban-I'],
            ['emis' => 913, 'name' => 'ICG, F-6/2', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Urban-I'],
            ['emis' => 914, 'name' => 'IMCG, F-7/4', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Urban-I'],
            ['emis' => 915, 'name' => 'IMCG, F-8/1', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Urban-I'],
            ['emis' => 916, 'name' => 'IMCG, G-10/2', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 917, 'name' => 'IMCG, St. # 23, I-10/4', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 918, 'name' => 'IMCG, I-8/4', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 919, 'name' => 'IMCG, F-10/2', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Urban-II'],
            ['emis' => 920, 'name' => 'IMCG, Korang Town', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Sihala'],
            ['emis' => 921, 'name' => 'IMCG G-13/1', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Tarnol'],
            ['emis' => 922, 'name' => 'IMCG G-14/4', 'gender' => 'girls', 'type' => 'I-XII', 'sector' => 'Tarnol'],
            ['emis' => 923, 'name' => 'IMCB G-13/2', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Tarnol'],
            ['emis' => 924, 'name' => 'IMCB G-15', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Tarnol'],
            ['emis' => 925, 'name' => 'IMCB Pakistan Town', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'Sihala'],
            ['emis' => 926, 'name' => 'IMCB Maira Begwal', 'gender' => 'boys', 'type' => 'I-XII', 'sector' => 'B.K'],
        ];

        $sectorCache = Sector::pluck('id', 'name')->toArray();

        foreach ($schools as $school) {
            $sector = $sectorCache[$school['sector']] ?? null;

            if (!$sector) {
                $this->command->warn("Sector not found: {$school['sector']} for {$school['name']}");
                continue;
            }

            Institution::firstOrCreate(
                ['code' => (string)$school['emis']],
                [
                    'name'                => $school['name'],
                    'sector_id'           => $sector,
                    'uc_id'               => null,
                    'type'                => $school['type'],
                    'gender'              => $school['gender'],
                    'shift'               => 'morning',
                    'admission_status'    => 'not_started',
                    'has_matric_tech'     => false,
                    'has_transport'       => false,
                    'has_meal_program'    => false,
                    'has_evening_classes' => false,
                    'is_active'           => true,
                ]
            );
        }

        $this->command->info('88 missing schools added successfully.');
        $this->command->info('Total institutions: ' . Institution::count());
    }
}
