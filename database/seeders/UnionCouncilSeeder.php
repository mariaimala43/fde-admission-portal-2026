<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnionCouncil;
use App\Models\Sector;

class UnionCouncilSeeder extends Seeder
{
    public function run(): void
    {
        // UC → Sector mapping
        // Urban-I:  UC-25 to UC-33
        // Urban-II: UC-34 to UC-44
        // B.K:      UC-01 to UC-11, UC-22, UC-23, UC-24
        // Tarnol:   UC-19, UC-20, UC-45 to UC-50
        // Sihala:   UC-14, UC-15, UC-16, UC-21
        // Nilore:   UC-12, UC-13, UC-17, UC-18


        $ucSectorMap = [
            'UC-01' => 'B.K',    'UC-02' => 'B.K',    'UC-03' => 'B.K',
            'UC-04' => 'B.K',    'UC-05' => 'B.K',    'UC-06' => 'B.K',
            'UC-07' => 'B.K',    'UC-08' => 'B.K',    'UC-09' => 'B.K',
            'UC-10' => 'B.K',    'UC-11' => 'B.K',
            'UC-12' => 'Nilore', 'UC-13' => 'Nilore',
            'UC-14' => 'Sihala', 'UC-15' => 'Sihala', 'UC-16' => 'Sihala',
            'UC-17' => 'Nilore', 'UC-18' => 'Nilore',
            'UC-19' => 'Tarnol', 'UC-20' => 'Tarnol',
            'UC-21' => 'Sihala',
            'UC-22' => 'B.K',    'UC-23' => 'B.K',    'UC-24' => 'B.K',
            'UC-25' => 'Urban-I','UC-26' => 'Urban-I','UC-27' => 'Urban-I',
            'UC-28' => 'Urban-I','UC-29' => 'Urban-I','UC-30' => 'Urban-I',
            'UC-31' => 'Urban-I','UC-32' => 'Urban-I','UC-33' => 'Urban-I',
            'UC-34' => 'Urban-II','UC-35' => 'Urban-II','UC-36' => 'Urban-II',
            'UC-37' => 'Urban-II','UC-38' => 'Urban-II','UC-39' => 'Urban-II',
            'UC-40' => 'Urban-II','UC-41' => 'Urban-II','UC-42' => 'Urban-II',
            'UC-43' => 'Urban-II','UC-44' => 'Urban-II',
            'UC-45' => 'Tarnol', 'UC-46' => 'Tarnol', 'UC-47' => 'Tarnol',
            'UC-48' => 'Tarnol', 'UC-49' => 'Tarnol', 'UC-50' => 'Tarnol',
            // New UCs from Nilore schools
            'UC-51' => 'Nilore', 'UC-52' => 'Nilore', 'UC-53' => 'Nilore',
            'UC-54' => 'Nilore', 'UC-55' => 'Nilore', 'UC-56' => 'Nilore',
            'UC-57' => 'Nilore', 'UC-58' => 'Nilore', 'UC-59' => 'Nilore',
            'UC-60' => 'Nilore', 'UC-61' => 'Nilore',
        ];

        $ucs = [
            ['name' => 'UC-01 Saidpur', 'code' => 'UC-01'],
            ['name' => 'UC-02 Noorpur Shahan (Barriamam)', 'code' => 'UC-02'],
            ['name' => 'UC-03 Malpur', 'code' => 'UC-03'],
            ['name' => 'UC-04 Kot Hathial (Shamal) Bharakahu', 'code' => 'UC-04'],
            ['name' => 'UC-05 Kot Hathial (Janoob)', 'code' => 'UC-05'],
            ['name' => 'UC-06 Phulgran', 'code' => 'UC-06'],
            ['name' => 'UC-07 Pind Begwal', 'code' => 'UC-07'],
            ['name' => 'UC-08 Tumair', 'code' => 'UC-08'],
            ['name' => 'UC-09 Charah', 'code' => 'UC-09'],
            ['name' => 'UC-10 Kirpa', 'code' => 'UC-10'],
            ['name' => 'UC-11 Mughal', 'code' => 'UC-11'],
            ['name' => 'UC-12 Rawat', 'code' => 'UC-12'],
            ['name' => 'UC-13 Humak', 'code' => 'UC-13'],
            ['name' => 'UC-14 Sihala', 'code' => 'UC-14'],
            ['name' => 'UC-15 Lohi Bhar', 'code' => 'UC-15'],
            ['name' => 'UC-16 Darwala', 'code' => 'UC-16'],
            ['name' => 'UC-17 Koral', 'code' => 'UC-17'],
            ['name' => 'UC-18 Khana Dak', 'code' => 'UC-18'],
            ['name' => 'UC-19 Tarlai Kalan', 'code' => 'UC-19'],
            ['name' => 'UC-20 Alipur', 'code' => 'UC-20'],
            ['name' => 'UC-21 Sohan', 'code' => 'UC-21'],
            ['name' => 'UC-22 Chak Shehzad', 'code' => 'UC-22'],
            ['name' => 'UC-23 Kuri (Banigala & Mohra Noor)', 'code' => 'UC-23'],
            ['name' => 'UC-24 Rawal Margala town', 'code' => 'UC-24'],
            ['name' => 'UC-25 Sector F-6', 'code' => 'UC-25'],
            ['name' => 'UC-26 Sector G-6/1', 'code' => 'UC-26'],
            ['name' => 'UC-27 Sector G-6/2', 'code' => 'UC-27'],
            ['name' => 'UC-28 F-7 F-8 F-9', 'code' => 'UC-28'],
            ['name' => 'UC-29 Sector F-10 F-11', 'code' => 'UC-29'],
            ['name' => 'UC-30 Sector G-7/3 G-7/4', 'code' => 'UC-30'],
            ['name' => 'UC-31 Sector G-7/1 G-7/2', 'code' => 'UC-31'],
            ['name' => 'UC-32 Sector G-8/3 G-8/4', 'code' => 'UC-32'],
            ['name' => 'UC-33 Sector G-8/1 G-8/2', 'code' => 'UC-33'],
            ['name' => 'UC-34 Sector G-9/1 G-9/3 G-9/4', 'code' => 'UC-34'],
            ['name' => 'UC-35 Sector G-9/2', 'code' => 'UC-35'],
            ['name' => 'UC-36 Sector G-10/3 G-10/4', 'code' => 'UC-36'],
            ['name' => 'UC-37 Sector G-10/1 G-10/2', 'code' => 'UC-37'],
            ['name' => 'UC-38 Sector G-11', 'code' => 'UC-38'],
            ['name' => 'UC-39 Maira Sumbal Jaffar (G-12, G-13)', 'code' => 'UC-39'],
            ['name' => 'UC-40 I-8 & H8', 'code' => 'UC-40'],
            ['name' => 'UC-41 I-9 & H9', 'code' => 'UC-41'],
            ['name' => 'UC-42 Sector I-10 & H-10', 'code' => 'UC-42'],
            ['name' => 'UC-43 Sector I-11 & H-11', 'code' => 'UC-43'],
            ['name' => 'UC-44 Bokra (I-12. H-11 & H-13)', 'code' => 'UC-44'],
            ['name' => 'UC-45 Jhangi Saidan', 'code' => 'UC-45'],
            ['name' => 'UC-46 Badhana Kalan', 'code' => 'UC-46'],
            ['name' => 'UC-47 Tarnol', 'code' => 'UC-47'],
            ['name' => 'UC-48 Sarai Kharbuza', 'code' => 'UC-48'],
            ['name' => 'UC-49 Shah Allah Ditta', 'code' => 'UC-49'],
            ['name' => 'UC-50 Golra Sharif', 'code' => 'UC-50'],
            // New UCs from Nilore schools (custom mappings)
            ['name' => 'UC-11 Thanda Pani', 'code' => 'UC-51'],
            ['name' => 'UC-20 Pind Begwal', 'code' => 'UC-52'],
            ['name' => 'UC-21 New Shakrial', 'code' => 'UC-53'],
            ['name' => 'UC-21 Tumair', 'code' => 'UC-54'],
            ['name' => 'UC-22 Thanda Pani', 'code' => 'UC-55'],
            ['name' => 'UC-23 Alipur', 'code' => 'UC-56'],
            ['name' => 'UC-24 Chirrah', 'code' => 'UC-57'],
            ['name' => 'UC-25 Kirpa', 'code' => 'UC-58'],
            ['name' => 'UC-47 Tarlai', 'code' => 'UC-59'],
            ['name' => 'UC-Dhoke Kala Khan RWP', 'code' => 'UC-60'],
            ['name' => 'UC-Khana Dak II RWP', 'code' => 'UC-61'],
        ];

        // Cache sectors
        $sectorCache = Sector::pluck('id', 'name')->toArray();

        foreach ($ucs as $uc) {
            $sectorName = $ucSectorMap[$uc['code']] ?? null;
            $sectorId   = $sectorName ? ($sectorCache[$sectorName] ?? null) : null;

            UnionCouncil::firstOrCreate(
                ['code' => $uc['code']],
                [
                    'name'      => $uc['name'],
                    'sector_id' => $sectorId,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('50 Union Councils seeded successfully.');
    }
}
