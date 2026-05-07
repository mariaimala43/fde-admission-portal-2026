<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            [
                'emis_code'         => 'FDE-001',
                'name'              => 'Federal Model School G-9/4',
                'address'           => 'G-9/4, Islamabad',
                'principal_name'    => 'Mr. Tariq Mahmood',
                'principal_contact' => '03001234567',
            ],
            [
                'emis_code'         => 'FDE-002',
                'name'              => 'Federal Government Girls School F-7/2',
                'address'           => 'F-7/2, Islamabad',
                'principal_name'    => 'Ms. Sadia Akhtar',
                'principal_contact' => '03012345678',
            ],
            [
                'emis_code'         => 'FDE-003',
                'name'              => 'Federal Boys School I-8/1',
                'address'           => 'I-8/1, Islamabad',
                'principal_name'    => 'Mr. Khalid Hussain',
                'principal_contact' => '03023456789',
            ],
            [
                'emis_code'         => 'FDE-004',
                'name'              => 'Federal Model College H-9',
                'address'           => 'H-9, Islamabad',
                'principal_name'    => 'Mr. Asif Raza',
                'principal_contact' => '03034567890',
            ],
            [
                'emis_code'         => 'FDE-005',
                'name'              => 'Federal Government School G-6/2',
                'address'           => 'G-6/2, Islamabad',
                'principal_name'    => 'Ms. Nadia Rehman',
                'principal_contact' => '03045678901',
            ],
        ];

        foreach ($schools as $data) {
            School::updateOrCreate(['emis_code' => $data['emis_code']], $data);
        }
    }
}
