<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnionCouncil;
use App\Models\UcControlRoom;

class UcControlRoomSeeder extends Seeder
{
    /**
     * Seed UC Control Room focal-person data for 32 UCs in ICT.
     *
     * Columns:
     *   uc_code              – matches union_councils.code (e.g. "UC-01")
     *   organization_name    – partner NGO / organisation
     *   focal_person_name    – organisation focal person(s)
     *   focal_person_contact – organisation contact number(s)
     *   nchd_fo_name         – NCHD Field Officer name
     *   nchd_fo_contact      – NCHD FO contact
     *   fde_school_name      – FDE school in that UC
     *   fde_focal_person_name    – HOI / focal person at FDE school
     *   fde_focal_person_contact – HOI contact
     */
    public function run(): void
    {
        $records = [
            [
                'uc_code'                 => 'UC-01',
                'organization_name'       => 'ALIGHT, PAGE',
                'focal_person_name'       => 'Dr Tariq Cheema / Rabia Pasha',
                'focal_person_contact'    => '03004408797 / 03215577096',
                'nchd_fo_name'            => 'Azizullah',
                'nchd_fo_contact'         => '03337837215',
                'fde_school_name'         => 'IMSB (I-X) Saidpur',
                'fde_focal_person_name'   => 'Khan Muhammad Khoso',
                'fde_focal_person_contact'=> '0333-5173506',
            ],
            [
                'uc_code'                 => 'UC-02',
                'organization_name'       => 'ALIGHT, PAGE',
                'focal_person_name'       => 'Dr Tariq Cheema / Rabia Pasha',
                'focal_person_contact'    => '03004408797 / 03215577097',
                'nchd_fo_name'            => 'Dr Bashir Ahmed',
                'nchd_fo_contact'         => '03152241457',
                'fde_school_name'         => 'IMSB (VI-X) Noor Pur Shahan',
                'fde_focal_person_name'   => 'Muhammad Anwar',
                'fde_focal_person_contact'=> '0333-5252695',
            ],
            [
                'uc_code'                 => 'UC-03',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Tufail Ahmad AD',
                'focal_person_contact'    => '03345071991',
                'nchd_fo_name'            => 'Azizullah',
                'nchd_fo_contact'         => '03337837215',
                'fde_school_name'         => 'IMSB (I-VIII) Malpur',
                'fde_focal_person_name'   => 'Hakim Khan',
                'fde_focal_person_contact'=> '0300-5542415',
            ],
            [
                'uc_code'                 => 'UC-04',
                'organization_name'       => 'HUMAN APPEAL',
                'focal_person_name'       => 'Mr Daud',
                'focal_person_contact'    => '03004119156',
                'nchd_fo_name'            => 'Ilyas Abbas',
                'nchd_fo_contact'         => '03339334891',
                'fde_school_name'         => 'IMSB (I-VIII) KotHathi',
                'fde_focal_person_name'   => 'Anwar Mehboob',
                'fde_focal_person_contact'=> '0310-8504440',
            ],
            [
                'uc_code'                 => 'UC-05',
                'organization_name'       => 'NEF',
                'focal_person_name'       => 'Yawar Abbas',
                'focal_person_contact'    => '03328947481',
                'nchd_fo_name'            => 'Shehla Gul',
                'nchd_fo_contact'         => '03025507462',
                'fde_school_name'         => 'IMCB (VI-XII) Bhara Kau',
                'fde_focal_person_name'   => 'Fida Ur Rehman',
                'fde_focal_person_contact'=> '0331-8856955',
            ],
            [
                'uc_code'                 => 'UC-06',
                'organization_name'       => 'SUNBEAM',
                'focal_person_name'       => 'Ms Humaira Khan',
                'focal_person_contact'    => '03214443608',
                'nchd_fo_name'            => null,
                'nchd_fo_contact'         => null,
                'fde_school_name'         => 'IMSB (I-X) Phulgran',
                'fde_focal_person_name'   => 'Muhammad Latif',
                'fde_focal_person_contact'=> '0321-5852384',
            ],
            [
                'uc_code'                 => 'UC-07',
                'organization_name'       => 'BECS',
                'focal_person_name'       => 'Nabgha Hashmi',
                'focal_person_contact'    => '03218687567',
                'nchd_fo_name'            => 'Ilyas Abbas',
                'nchd_fo_contact'         => '03339334891',
                'fde_school_name'         => 'IMCB (VI-XII) Pind Begwal',
                'fde_focal_person_name'   => 'Dr Saeed Anwar',
                'fde_focal_person_contact'=> '0332-8940371',
            ],
            [
                'uc_code'                 => 'UC-08',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Rizwan Shah AD',
                'focal_person_contact'    => '03005809130',
                'nchd_fo_name'            => 'Taslim ul Haq',
                'nchd_fo_contact'         => '03078067011',
                'fde_school_name'         => 'IMSB (I-X) Tumair',
                'fde_focal_person_name'   => 'Naseem Ahmed',
                'fde_focal_person_contact'=> '0335-5066002',
            ],
            [
                'uc_code'                 => 'UC-09',
                'organization_name'       => 'BECS',
                'focal_person_name'       => 'Nabgha Hashmi',
                'focal_person_contact'    => '03218687567',
                'nchd_fo_name'            => 'Atiq ur Rehman',
                'nchd_fo_contact'         => '03349277319',
                'fde_school_name'         => 'IMSB (VI-X) Chirah',
                'fde_focal_person_name'   => 'Javed Iqbal',
                'fde_focal_person_contact'=> '0333-3105752',
            ],
            [
                'uc_code'                 => 'UC-10',
                'organization_name'       => 'BECS',
                'focal_person_name'       => 'Nabgha Hashmi',
                'focal_person_contact'    => '03218687568',
                'nchd_fo_name'            => 'Nasira Mumtaz',
                'nchd_fo_contact'         => '03458906116',
                'fde_school_name'         => 'IMSB (I-X) Kirpa',
                'fde_focal_person_name'   => 'Muhammad Nawaz',
                'fde_focal_person_contact'=> '0333-5022499',
            ],
            [
                'uc_code'                 => 'UC-11',
                'organization_name'       => 'BECS',
                'focal_person_name'       => 'Nabgha Hashmi',
                'focal_person_contact'    => '03218687569',
                'nchd_fo_name'            => 'Nasira Mumtaz',
                'nchd_fo_contact'         => '03458906116',
                'fde_school_name'         => 'IMCB Mughal',
                'fde_focal_person_name'   => 'Abrar Hussain Mirza',
                'fde_focal_person_contact'=> '0345-5044708',
            ],
            [
                'uc_code'                 => 'UC-12',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Ali Rafiq AD',
                'focal_person_contact'    => '03348961841',
                'nchd_fo_name'            => 'Saad Ullah',
                'nchd_fo_contact'         => '03008877599',
                'fde_school_name'         => 'IMCB Rawat',
                'fde_focal_person_name'   => 'Muhammad Asghar',
                'fde_focal_person_contact'=> '0345-6971160',
            ],
            [
                'uc_code'                 => 'UC-13',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Ali Rafiq AD',
                'focal_person_contact'    => '03348961842',
                'nchd_fo_name'            => 'Irum Shehzadi',
                'nchd_fo_contact'         => '03320936067',
                'fde_school_name'         => 'IMCB Humak',
                'fde_focal_person_name'   => 'Atif Naeem',
                'fde_focal_person_contact'=> '0343-5455983',
            ],
            [
                'uc_code'                 => 'UC-14',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Ali Rafiq AD',
                'focal_person_contact'    => '03348961842',
                'nchd_fo_name'            => 'Abdul Hanan',
                'nchd_fo_contact'         => '03317338071',
                'fde_school_name'         => 'IMSB (VI-X) Sihala',
                'fde_focal_person_name'   => 'Haji Hussain Ahmed',
                'fde_focal_person_contact'=> '0303-7426305',
            ],
            [
                'uc_code'                 => 'UC-15',
                'organization_name'       => 'Momentum',
                'focal_person_name'       => 'Iqbal ur Rehman',
                'focal_person_contact'    => '03245494244',
                'nchd_fo_name'            => 'Saadullah',
                'nchd_fo_contact'         => '03008877599',
                'fde_school_name'         => 'IMCB Pagh Panwal',
                'fde_focal_person_name'   => 'Abdul Rasheed',
                'fde_focal_person_contact'=> '0333-5254075',
            ],
            [
                'uc_code'                 => 'UC-16',
                'organization_name'       => 'BECS',
                'focal_person_name'       => 'Nabgha Hashmi',
                'focal_person_contact'    => '03218687567',
                'nchd_fo_name'            => 'Taslim ul Haq',
                'nchd_fo_contact'         => '03078067011',
                'fde_school_name'         => 'IMSB (I-X) Dhaliata',
                'fde_focal_person_name'   => 'Ayaz Nazir',
                'fde_focal_person_contact'=> '0342-7006032',
            ],
            [
                'uc_code'                 => 'UC-17',
                'organization_name'       => 'Character',
                'focal_person_name'       => 'Mr Asim',
                'focal_person_contact'    => '03315152502',
                'nchd_fo_name'            => 'Saima Hanif',
                'nchd_fo_contact'         => '03335691477',
                'fde_school_name'         => 'IMSB (I-VIII) Koral',
                'fde_focal_person_name'   => 'Abdul Basit',
                'fde_focal_person_contact'=> '0301-5146787',
            ],
            [
                'uc_code'                 => 'UC-18',
                'organization_name'       => 'Junior Jinnah',
                'focal_person_name'       => 'Mr Khayyam',
                'focal_person_contact'    => '03325488876',
                'nchd_fo_name'            => 'Irum Shehzadi',
                'nchd_fo_contact'         => '03320936067',
                'fde_school_name'         => 'IMSB (I-X) Khana Dak',
                'fde_focal_person_name'   => 'Saqib Rasheed',
                'fde_focal_person_contact'=> '0345-5159815',
            ],
            [
                'uc_code'                 => 'UC-19',
                'organization_name'       => 'HUMAN APPEAL',
                'focal_person_name'       => 'Mr Daud',
                'focal_person_contact'    => '03004119156',
                'nchd_fo_name'            => 'Saima Hanif',
                'nchd_fo_contact'         => '03335691477',
                'fde_school_name'         => 'IMSB (VI-X) Tarlai',
                'fde_focal_person_name'   => 'Taswar Ali',
                'fde_focal_person_contact'=> '0345-3805236',
            ],
            [
                'uc_code'                 => 'UC-20',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Rizwan Shah AD',
                'focal_person_contact'    => '03005809130',
                'nchd_fo_name'            => 'Atiq ur Rehman',
                'nchd_fo_contact'         => '03349277319',
                'fde_school_name'         => 'IMSB (I-VIII) Alipur Frash',
                'fde_focal_person_name'   => 'Wahib Shah',
                'fde_focal_person_contact'=> '0311-5830104',
            ],
            [
                'uc_code'                 => 'UC-21',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Rizwan Shah AD',
                'focal_person_contact'    => '03005809130',
                'nchd_fo_name'            => 'Shehzad Bhatti',
                'nchd_fo_contact'         => '03327148917',
                'fde_school_name'         => 'IMSB (I-VIII) Sohan',
                'fde_focal_person_name'   => 'Hafiz Akhter Rehman',
                'fde_focal_person_contact'=> '0345-5275365',
            ],
            [
                'uc_code'                 => 'UC-22',
                'organization_name'       => 'Muslim Hands',
                'focal_person_name'       => 'Palwasha Malik',
                'focal_person_contact'    => '03469187500',
                'nchd_fo_name'            => 'Atiq Kashmiri',
                'nchd_fo_contact'         => '03335318991',
                'fde_school_name'         => 'IMCB (VI-XII) Chak Shahzad',
                'fde_focal_person_name'   => 'Feroz Khan',
                'fde_focal_person_contact'=> '0333-5281376',
            ],
            [
                'uc_code'                 => 'UC-23',
                'organization_name'       => 'Zakat Foundation',
                'focal_person_name'       => 'Tayyaba Hafeez',
                'focal_person_contact'    => '03229144873',
                'nchd_fo_name'            => null,
                'nchd_fo_contact'         => null,
                'fde_school_name'         => 'IMSB (VI-X) Kuri',
                'fde_focal_person_name'   => 'Rehman Buksh',
                'fde_focal_person_contact'=> '0302-3634970',
            ],
            [
                'uc_code'                 => 'UC-24',
                'organization_name'       => 'BECS',
                'focal_person_name'       => 'Nabgha Hashmi',
                'focal_person_contact'    => '03218687567',
                'nchd_fo_name'            => 'Farooq Mughal',
                'nchd_fo_contact'         => '03339513215',
                'fde_school_name'         => 'IMCG (I-XII) Margalla Town',
                'fde_focal_person_name'   => 'Shumaila Ghazal',
                'fde_focal_person_contact'=> '0333-5449808',
            ],
            [
                'uc_code'                 => 'UC-39',
                'organization_name'       => 'BECS',
                'focal_person_name'       => 'Nabgha Hashmi',
                'focal_person_contact'    => '03218687567',
                'nchd_fo_name'            => 'Nasir ul Islam',
                'nchd_fo_contact'         => '03458959494',
                'fde_school_name'         => 'IMSB (I-X) Maira Akku',
                'fde_focal_person_name'   => 'Javed Hussain Gul',
                'fde_focal_person_contact'=> '0300-3180870',
            ],
            [
                'uc_code'                 => 'UC-44',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Tufail Ahmad AD',
                'focal_person_contact'    => '03345071991',
                'nchd_fo_name'            => 'M Azhar',
                'nchd_fo_contact'         => '03125624465',
                'fde_school_name'         => 'IMSB (I-V) Bokra',
                'fde_focal_person_name'   => 'Abdul Ghaffar',
                'fde_focal_person_contact'=> '0300-9848417',
            ],
            [
                'uc_code'                 => 'UC-45',
                'organization_name'       => 'NEF',
                'focal_person_name'       => 'Yawar Abbas',
                'focal_person_contact'    => '03328947481',
                'nchd_fo_name'            => 'Shafiullah',
                'nchd_fo_contact'         => '03023956350',
                'fde_school_name'         => 'IMSB (I-X) I-14',
                'fde_focal_person_name'   => 'Kifayat Ullah',
                'fde_focal_person_contact'=> '0333-9492656',
            ],
            [
                'uc_code'                 => 'UC-46',
                'organization_name'       => 'BECS',
                'focal_person_name'       => 'Nabgha Hashmi',
                'focal_person_contact'    => '03218687567',
                'nchd_fo_name'            => 'Zubair',
                'nchd_fo_contact'         => '03024152360',
                'fde_school_name'         => 'IMCB Bhadana Kalan',
                'fde_focal_person_name'   => 'Muhammad Arif',
                'fde_focal_person_contact'=> '0333-5465984',
            ],
            [
                'uc_code'                 => 'UC-47',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Ms Najma Khan AD',
                'focal_person_contact'    => '03453861499',
                'nchd_fo_name'            => 'M Azhar',
                'nchd_fo_contact'         => '03125624465',
                'fde_school_name'         => 'IMSB (I-V) Tarnaul',
                'fde_focal_person_name'   => 'Kashif Jamal',
                'fde_focal_person_contact'=> '0333-5440228',
            ],
            [
                'uc_code'                 => 'UC-48',
                'organization_name'       => 'NCHD',
                'focal_person_name'       => 'Ms Najma Khan AD',
                'focal_person_contact'    => '03453861500',
                'nchd_fo_name'            => 'Zubair Niazi',
                'nchd_fo_contact'         => '03024152360',
                'fde_school_name'         => 'IMSB (I-V) Sarai Kharbuza',
                'fde_focal_person_name'   => 'Muhammad Naveed',
                'fde_focal_person_contact'=> '0334-8315208',
            ],
            [
                'uc_code'                 => 'UC-49',
                'organization_name'       => 'SUNBEAM',
                'focal_person_name'       => 'Ms Humaira Khan',
                'focal_person_contact'    => '03214443608',
                'nchd_fo_name'            => 'Naseer ul Islam',
                'nchd_fo_contact'         => '03458959494',
                'fde_school_name'         => 'IMSB (I-V) Shah Allah Ditta',
                'fde_focal_person_name'   => 'Ghulam Rasool Abid',
                'fde_focal_person_contact'=> '0333-5317864',
            ],
            [
                'uc_code'                 => 'UC-50',
                'organization_name'       => 'BECS',
                'focal_person_name'       => 'Nabgha Hashmi',
                'focal_person_contact'    => '03218687567',
                'nchd_fo_name'            => 'Ilyas',
                'nchd_fo_contact'         => null,
                'fde_school_name'         => 'IMSB (VI-X) Golra',
                'fde_focal_person_name'   => 'Afsar Ali Shah',
                'fde_focal_person_contact'=> '0312-9355536',
            ],
        ];

        $skipped = 0;

        foreach ($records as $record) {
            $uc = UnionCouncil::where('code', $record['uc_code'])->first();

            if (! $uc) {
                $this->command->warn("UC not found for code: {$record['uc_code']} — skipping.");
                $skipped++;
                continue;
            }

            UcControlRoom::updateOrCreate(
                ['uc_id' => $uc->id],
                [
                    'organization_name'        => $record['organization_name'],
                    'focal_person_name'        => $record['focal_person_name'],
                    'focal_person_contact'     => $record['focal_person_contact'],
                    'nchd_fo_name'             => $record['nchd_fo_name'],
                    'nchd_fo_contact'          => $record['nchd_fo_contact'],
                    'fde_school_name'          => $record['fde_school_name'],
                    'fde_focal_person_name'    => $record['fde_focal_person_name'],
                    'fde_focal_person_contact' => $record['fde_focal_person_contact'],
                ]
            );
        }

        $seeded = count($records) - $skipped;
        $this->command->info("UcControlRoomSeeder: {$seeded} records seeded, {$skipped} skipped.");
    }
}
