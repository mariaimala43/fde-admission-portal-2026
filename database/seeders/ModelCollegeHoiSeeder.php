<?php

// SAVE AS: database/seeders/ModelCollegeHoiSeeder.php
// Run: php artisan db:seed --class=ModelCollegeHoiSeeder

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\User;

class ModelCollegeHoiSeeder extends Seeder
{
    public function run(): void
    {
        $updated   = 0;
        $unmatched = [];

        foreach ($this->getData() as [$ibNumber, $emisCode, $collegeName, $hoiName, $hoiPhone]) {

            // Match institution by IB number → EMIS code → name (fuzzy)
            $institution = Institution::where('ib_number', $ibNumber)->first()
                ?? Institution::where('code', $emisCode)->first()
                ?? Institution::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($collegeName)) . '%'])->first();

            if (! $institution) {
                $unmatched[] = "IB#{$ibNumber} | EMIS#{$emisCode} | {$collegeName} — institution not found";
                continue;
            }

            // Find the HOI user linked to this institution
            $hoiUser = User::where('institution_id', $institution->id)
                ->where('is_active', true)
                ->get()
                ->first(fn ($u) => $u->hasRole('hoi'));

            if (! $hoiUser) {
                $unmatched[] = "IB#{$ibNumber} | {$collegeName} — no active HOI user found";
                continue;
            }

            $hoiUser->update([
                'name'  => $hoiName,
                'phone' => $hoiPhone,
            ]);

            // Also denormalise HOI info onto the institution for public portal display
            $institution->update([
                'hoi_name'    => $hoiName,
                'hoi_contact' => $hoiPhone,
            ]);

            $updated++;
        }

        $this->command->info("✅  Updated: {$updated} HOI users");

        if (count($unmatched)) {
            $this->command->warn('⚠️  Could not process ' . count($unmatched) . ' entries:');
            foreach ($unmatched as $line) {
                $this->command->line("   - {$line}");
            }
        }
    }

    // Format: [ib_number, emis_code, college_name, hoi_name, hoi_phone]
    private function getData(): array
    {
        return [
            ['2755', '908',  'ICB G-6/3',         'Prof. Dr. Yaseen Aafaqi',       '0333-5622902'],
            ['2758', '905',  'IMCB F-8/4',         'Prof. Naeem ur Raheem',          '0333-7447442'],
            ['2854', '904',  'IMCB F-7/3',         'Dr. Rashid Ahmed Siddiqui',      '0321-5611855'],
            ['2861', '906',  'IMCB G-10/4',        'Prof. M. Ihsan Ul Haq',          '0334-5440689'],
            ['2858', '901',  'IMCB F-10/3',        'Prof. Najeeb Ullah',             '0333-5446675'],
            ['2862', '909',  'IMCB I-10/1',        'Prof. M. Saeed',                 '0316-5954771'],
            ['2853', '910',  'IMCB I-8/3',         'Prof. Shahid Mehmood Abbasi',    '0300-5011394'],
            ['2756', '902',  'IMCB F-11/1',        'Prof. Ghayoor Hussain',          '0300-5173212'],
            ['2850', '907',  'IMCB G-11/1',        'Prof. Amir Mumtaz',              '0333-5139238'],
            ['5141', '925',  'IMCB Pak. Town',     'Prof. Sharafat Ali Awan',        '0331-5003363'],
            ['5145', '923',  'IMCB G-13/2',        'Prof. M. Riaz Hussain',          '0333-5125081'],
            ['5142', '924',  'IMCB G-15',          'Prof. Zahid Akbar',              '0333-5102142'],
            ['5140', '926',  'IMCB Maira Begwal',  'Prof. Adnan Jahangir',           '0332-5142315'],
            ['2859', '913',  'ICG F-6/2',          'Prof. Shazia Shamim',            '0332-8500093'],
            ['2763', '912',  'IMCG F-6/2',         'Prof. Aaliya Durrani',           '0301-8730000'],
            ['2860', '914',  'IMCG F-7/4',         'Dr. Sadia Aziz',                 '0333-5396999'],
            ['2762', '919',  'IMCG F-10/2',        'Prof. Fakhar uz Zia Kamal',      '0334-5334774'],
            ['2856', '915',  'IMCG F-8/1',         'Prof. Aasia Rafiq',              '0333-1520276'],
            ['2851', '916',  'IMCG G-10/2',        'Prof. Saadat Begum',             '0345-5174951'],
            ['285',  '918',  'IMCG I-8/4',         'Prof. Shagufta Naz',             '0321-9399375'],
            ['2764', '911',  'IMCCG F-10/3',       'Prof. Samra Latif',              '0331-5132146'],
            ['2760', '917',  'IMCG I-10/4',        'Prof. Fatima Mobeen',            '0333-5601711'],
            ['2855', '920',  'IMCG K. Town',       'Prof. Dr. Munazza Fahim',        '0300-5393826'],
            ['2857', '903',  'IMCG F-11/3',        'Prof. Rukhsana Naveed',          '0333-5174923'],
            ['5143', '922',  'IMCG G-13/1',        'Prof. Saleha Tabassum',          '0336-5276489'],
            ['5144', '921',  'IMCG G-14/4',        'Prof. Musarrat Bokhari',         '0335-9070529'],
        ];
    }
}
