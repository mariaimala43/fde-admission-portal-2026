<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SAVE AS: database/seeders/ExFgCollegesSeeder.php
 *
 * Seeds the ex_fg_colleges reference table.
 * Source: "Contact number of Ex FG Colleges" document.
 * This table is purely informational — not linked to institutions.
 *
 * Run with:
 *   php artisan db:seed --class=ExFgCollegesSeeder
 */
class ExFgCollegesSeeder extends Seeder
{
    public function run(): void
    {
        $colleges = [
            [
                'serial_no'      => 1,
                'emis'           => '802',
                'ib_number'      => 'IB-2524',
                'name'           => 'IMPC, H-8',
                'principal_name' => 'Prof. Atthar ul Islam',
                'contact_number' => '0332-7942808',
            ],
            [
                'serial_no'      => 2,
                'emis'           => '805',
                'ib_number'      => 'IB-2869',
                'name'           => 'IMPCC, H-8/4',
                'principal_name' => 'Prof. Dr. Muhammad Khalid',
                'contact_number' => '0333-5511561',
            ],
            [
                'serial_no'      => 3,
                'emis'           => '804',
                'ib_number'      => 'IB-2522',
                'name'           => 'IMCB, H-9',
                'principal_name' => 'Prof. Muhammad Javed Iqbal',
                'contact_number' => '0300-9780372',
            ],
            [
                'serial_no'      => 4,
                'emis'           => '803',
                'ib_number'      => 'IB-2520',
                'name'           => 'IMCB, F-10/4',
                'principal_name' => 'Mr. Muhammad Rashid',
                'contact_number' => '0321-5106044',
            ],
            [
                'serial_no'      => 5,
                'emis'           => '801',
                'ib_number'      => 'IB-2765',
                'name'           => 'IMCB, Sihala',
                'principal_name' => 'Mr. Zahoor Ahmed',
                'contact_number' => '0334-5856956',
            ],
            [
                'serial_no'      => 6,
                'emis'           => '806',
                'ib_number'      => 'IB-2768',
                'name'           => 'IMCG (PG) F-7/2',
                'principal_name' => 'Prof. Dr. Fouzia Tanveer Sheikh',
                'contact_number' => '0333-5107474',
            ],
            [
                'serial_no'      => 7,
                'emis'           => '810',
                'ib_number'      => 'IB-2527',
                'name'           => 'IMCG (PG) F-7/4',
                'principal_name' => 'Ms. Ayesha Kiyani',
                'contact_number' => '0307-5555415',
            ],
            [
                'serial_no'      => 8,
                'emis'           => '807',
                'ib_number'      => 'IB-2523',
                'name'           => 'IMCG (PG) G-10/4',
                'principal_name' => 'Prof. Sadia Ibrar',
                'contact_number' => '0334-5164710',
            ],
            [
                'serial_no'      => 9,
                'emis'           => '809',
                'ib_number'      => 'IB-5147',
                'name'           => 'IMCG, I-8/3',
                'principal_name' => 'Ms. Najam Un Nisa',
                'contact_number' => '0333-3601098',
            ],
            [
                'serial_no'      => 10,
                'emis'           => '811',
                'ib_number'      => 'IB-1583',
                'name'           => 'IMCG, I-14/3',
                'principal_name' => 'Ms. Shazia Wazir',
                'contact_number' => '0332-5137674',
            ],
            [
                'serial_no'      => 11,
                'emis'           => '808',
                'ib_number'      => 'IB-2541',
                'name'           => 'IMCG, Humak',
                'principal_name' => 'Dr. Humaira Jabeen',
                'contact_number' => '0312-5281522',
            ],
            [
                'serial_no'      => 12,
                'emis'           => '812',
                'ib_number'      => 'IB-2766',
                'name'           => 'IMCG, Bharakau',
                'principal_name' => 'Ms. Abida Parveen',
                'contact_number' => '0333-7241916',
            ],
            [
                'serial_no'      => 13,
                'emis'           => '813',
                'ib_number'      => 'IB-2835',
                'name'           => 'Home Economics College F-11/1',
                'principal_name' => 'Prof. Rozina Faheem',
                'contact_number' => '0300-5098602',
            ],
        ];

        foreach ($colleges as $college) {
            DB::table('ex_fg_colleges')->updateOrInsert(
                ['emis' => $college['emis']],
                array_merge($college, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('ExFgCollegesSeeder: ' . count($colleges) . ' records seeded into ex_fg_colleges.');
    }
}
