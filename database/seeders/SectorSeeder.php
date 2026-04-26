<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = [
            ['name' => 'Urban-I',         'code' => 'URBAN-I'],
            ['name' => 'Urban-II',        'code' => 'URBAN-II'],
            ['name' => 'B.K',             'code' => 'B-K'],
            ['name' => 'Tarnaul',         'code' => 'TARNAUL'],
            ['name' => 'Sihala',          'code' => 'SIHALA'],
            ['name' => 'Nilore',          'code' => 'NILORE'],
            ['name' => 'MODEL COLLEGES',  'code' => 'MODEL'],
        ];

        foreach ($sectors as $s) {
            Sector::firstOrCreate(
                ['code' => $s['code']],
                ['name' => $s['name'], 'is_active' => true]
            );
        }

        $this->command->info('7 Sectors seeded successfully (6 geographic + MODEL COLLEGES).');
    }
}
