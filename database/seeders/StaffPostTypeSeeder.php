<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffPostType;

class StaffPostTypeSeeder extends Seeder
{
    public function run(): void
    {
        $allTypes = [
            'I-V', 'I-VIII', 'I-X', 'I-XII',
            'VI-VIII', 'VI-X', 'VI-XII',
            'XI-XII', 'XI-XIV', 'Model College',
        ];

        // VP applies to all levels except primary-only schools
        $vpTypes = array_values(array_filter($allTypes, fn($t) => $t !== 'I-V'));

        // SST — secondary and above
        $sstTypes = ['VI-X', 'I-X', 'VI-XII', 'I-XII', 'XI-XII', 'XI-XIV', 'Model College'];

        // SET — middle and above (but not XI-XII / XI-XIV standalone)
        $setTypes = ['I-VIII', 'VI-VIII', 'VI-X', 'I-X', 'VI-XII', 'I-XII', 'Model College'];

        // EST — primary component
        $estTypes = ['I-V', 'I-VIII', 'I-X', 'I-XII', 'Model College'];

        // ── Section A — Teaching posts ─────────────────────
        $teachingPosts = [
            ['code' => 'PRINCIPAL',   'name' => 'Principal / Head',         'category' => 'principal', 'applicable_levels' => $allTypes,  'sort_order' => 1],
            ['code' => 'VP',          'name' => 'Vice Principal',            'category' => 'vp',        'applicable_levels' => $vpTypes,   'sort_order' => 2],
            ['code' => 'SST',         'name' => 'SST',                       'category' => 'sst',       'applicable_levels' => $sstTypes,  'sort_order' => 3],
            ['code' => 'SST_PE',      'name' => 'SST – Physical Education',  'category' => 'sst',       'applicable_levels' => $sstTypes,  'sort_order' => 4],
            ['code' => 'SET',         'name' => 'SET',                       'category' => 'set',       'applicable_levels' => $setTypes,  'sort_order' => 5],
            ['code' => 'SET_PE',      'name' => 'SET – Physical Education',  'category' => 'set',       'applicable_levels' => $setTypes,  'sort_order' => 6],
            ['code' => 'SET_SACKED',  'name' => 'SET – Sacked Employees',    'category' => 'set',       'applicable_levels' => $setTypes,  'sort_order' => 7],
            ['code' => 'SET_DRAW',    'name' => 'SET – Drawing',             'category' => 'set',       'applicable_levels' => $setTypes,  'sort_order' => 8],
            ['code' => 'EST',         'name' => 'EST',                       'category' => 'est',       'applicable_levels' => $estTypes,  'sort_order' => 9],
            ['code' => 'EST_SACKED',  'name' => 'EST – Sacked Employees',    'category' => 'est',       'applicable_levels' => $estTypes,  'sort_order' => 10],
        ];

        foreach ($teachingPosts as $post) {
            StaffPostType::updateOrCreate(
                ['code' => $post['code']],
                array_merge($post, [
                    'section'          => 'teaching',
                    'has_full_columns' => true,
                    'is_active'        => true,
                ])
            );
        }

        // ── Section B — Program posts ──────────────────────
        $programPosts = [
            ['code' => 'TFP',          'name' => 'TFP Fellows (Teach for Pakistan)',    'sort_order' => 11],
            ['code' => 'DIL',          'name' => 'DIL (Development in Literacy)',       'sort_order' => 12],
            ['code' => 'CODING_FELLOW','name' => 'Coding Fellow',                       'sort_order' => 13],
            ['code' => 'TECH_FELLOW',  'name' => 'Tech Fellow',                         'sort_order' => 14],
            ['code' => 'TRADE_TECH',   'name' => 'Trade Tech Fellow',                   'sort_order' => 15],
            ['code' => 'ECE_FDE',      'name' => 'ECE Teachers (FDE Project)',          'sort_order' => 16],
            ['code' => 'SMC_FUND',     'name' => 'Teachers Engaged through SMC Fund',  'sort_order' => 17],
            ['code' => 'OTHERS',       'name' => 'Others',                               'sort_order' => 18],
        ];

        foreach ($programPosts as $post) {
            StaffPostType::updateOrCreate(
                ['code' => $post['code']],
                array_merge($post, [
                    'section'          => 'program',
                    'category'         => 'program',
                    'applicable_levels'=> $allTypes,
                    'has_full_columns' => false,
                    'is_active'        => true,
                ])
            );
        }

        $this->command->info('✅ StaffPostType seeded: 10 teaching + 8 program posts.');
    }
}
