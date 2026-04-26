<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\UnionCouncil;
use Illuminate\Support\Facades\DB;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding 432 institutions from EMIS data...');

        // ------------------------------------------------------------------
        // 1. Ensure Nilore Union Councils are correctly assigned
        //    (mirrors the fixes from ResetAllSchoolsSeeder)
        // ------------------------------------------------------------------
        $niloreSector = Sector::where('code', 'NILORE')->first();
        if ($niloreSector) {
            // UC-52, UC-53, UC-54 were previously assigned to Urban-I – move them to Nilore
            UnionCouncil::whereIn('code', ['UC-52', 'UC-53', 'UC-54'])
                ->update(['sector_id' => $niloreSector->id]);

            // Rename UC-53 from "Shakrial" to "New Shakrial" for consistency
            UnionCouncil::where('code', 'UC-53')
                ->update(['name' => 'UC-53 New Shakrial']);

            // Create UC-55 if it does not exist (Khana Dak II RWP for Nilore)
            UnionCouncil::firstOrCreate(
                ['code' => 'UC-55'],
                [
                    'name'       => 'UC-55 Khana Dak II RWP',
                    'sector_id'  => $niloreSector->id,
                    'is_active'  => true,
                ]
            );
        } else {
            $this->command->warn('Sector NILORE not found – skipping UC fixes.');
        }

        // ------------------------------------------------------------------
        // 2. Cache sector and union council lookups for performance
        // ------------------------------------------------------------------
        $sectors = Sector::pluck('id', 'code')->toArray();
        $ucs     = UnionCouncil::pluck('id', 'code')->toArray();

        // ------------------------------------------------------------------
        // 3. Define all 432 schools as tuples:
        //    [emis, name, gender, type, sector_code, uc_code]
        //    Gender values: 'boys', 'girls', 'co_education'
        // ------------------------------------------------------------------
        $schools = [
            // ===== URBAN-I =====
            [201, 'IMCG G-6/1-4', 'girls', 'VI-XII', 'URBAN-I', 'UC-26'],
            [202, 'IMSG(VI-X) G-6/1-3', 'girls', 'VI-X', 'URBAN-I', 'UC-26'],
            [203, 'IMS (I-V) G-6/1-1', 'co_education', 'I-V', 'URBAN-I', 'UC-26'],
            [204, 'IMS (I-V) G-6/1-3', 'co_education', 'I-V', 'URBAN-I', 'UC-26'],
            [205, 'IMS(I-V) G-6/1-4', 'co_education', 'I-V', 'URBAN-I', 'UC-26'],
            [206, 'IMS(I-V) G-6/4', 'co_education', 'I-V', 'URBAN-I', 'UC-27'],
            [207, 'IMSG(VI-X) G-6/2', 'girls', 'VI-X', 'URBAN-I', 'UC-27'],
            [208, 'IMS(I-V) G-6/2 Cafe Iram', 'co_education', 'I-V', 'URBAN-I', 'UC-27'],
            [209, 'IMSG(I-VIII) G-6/2', 'girls', 'I-VIII', 'URBAN-I', 'UC-27'],
            [210, 'IMS(I-V) G-6/2', 'co_education', 'I-V', 'URBAN-I', 'UC-27'],
            [211, 'IMSG (I-X) P.E. CLY G-5', 'girls', 'I-X', 'URBAN-I', 'UC-25'],
            [212, 'IMS(I-V) G-6/1-2', 'co_education', 'I-V', 'URBAN-I', 'UC-26'],
            [213, 'IMSG(VI-X) G-7/1', 'girls', 'VI-X', 'URBAN-I', 'UC-31'],
            [214, 'IMSG(VI-X) G-7/2', 'girls', 'VI-X', 'URBAN-I', 'UC-31'],
            [215, 'IMSG (I-VIII) G-7/3-2', 'girls', 'I-VIII', 'URBAN-I', 'UC-30'],
            [216, 'IMS(I-V) G-7/1', 'co_education', 'I-V', 'URBAN-I', 'UC-31'],
            [217, 'IMS(I-V) G-7/4', 'co_education', 'I-V', 'URBAN-I', 'UC-30'],
            [218, 'IMSG (I-VIII) G-7/3-4', 'girls', 'I-VIII', 'URBAN-I', 'UC-30'],
            [219, 'IMS(I-V) G-7/3-1', 'co_education', 'I-V', 'URBAN-I', 'UC-30'],
            [220, 'IMS (I-V) G-7/3-3', 'co_education', 'I-V', 'URBAN-I', 'UC-30'],
            [221, 'IMSG (I-VIII) F-7/1', 'girls', 'I-VIII', 'URBAN-I', 'UC-28'],
            [222, 'IMS(I-V) No.1 G-7/2', 'co_education', 'I-V', 'URBAN-I', 'UC-31'],
            [223, 'IMSG(VI-X) F-7/2', 'girls', 'VI-X', 'URBAN-I', 'UC-28'],
            [224, 'IMSG(VI-X) F-6/1', 'girls', 'VI-X', 'URBAN-I', 'UC-25'],
            [225, 'IMSG (I-VIII) F-7/4', 'girls', 'I-VIII', 'URBAN-I', 'UC-28'],
            [226, 'IMS(I-V) F-6/4', 'co_education', 'I-V', 'URBAN-I', 'UC-25'],
            [227, 'IMSG (I-X) P.M. Colony G-5', 'girls', 'I-X', 'URBAN-I', 'UC-25'],
            [228, 'IMS(I-V) F-6/1', 'co_education', 'I-V', 'URBAN-I', 'UC-25'],
            [229, 'IMS(I-V) F-6/3', 'co_education', 'I-V', 'URBAN-I', 'UC-28'],
            [230, 'IMS (I-V), F-7/2-4', 'co_education', 'I-V', 'URBAN-I', 'UC-28'],
            [231, 'IMS(I-V) F-7/2', 'co_education', 'I-V', 'URBAN-I', 'UC-28'],
            [232, 'IMSB (I-X) P.M. Colony G-5', 'boys', 'I-X', 'URBAN-I', 'UC-25'],
            [233, 'IMSG(VI-X) E-8/3', 'girls', 'VI-X', 'URBAN-I', 'UC-40'],
            [234, 'IMS(I-V) No.1 E-8', 'co_education', 'I-V', 'URBAN-I', 'UC-40'],
            [235, 'IMS(I-V) No.2 E-8', 'co_education', 'I-V', 'URBAN-I', 'UC-28'],
            [236, 'IMSG(I-X) E-9', 'girls', 'I-X', 'URBAN-I', 'UC-28'],
            [237, 'IMS(I-V) E-7/4', 'co_education', 'I-V', 'URBAN-I', 'UC-28'],
            [238, 'IMCG G-8/4', 'girls', 'VI-XII', 'URBAN-I', 'UC-32'],
            [239, 'IMSG(VI-X) G-8/2', 'girls', 'VI-X', 'URBAN-I', 'UC-33'],
            [240, 'IMSG (I-VIII) G-8/4', 'girls', 'I-VIII', 'URBAN-I', 'UC-32'],
            [241, 'IMS(I-V) No.1 G-8/4', 'co_education', 'I-V', 'URBAN-I', 'UC-32'],
            [242, 'IMS(I-V) No.2 G-8/4', 'co_education', 'I-V', 'URBAN-I', 'UC-32'],
            [243, 'IMS(I-V) No.1 G-8/1', 'co_education', 'I-V', 'URBAN-I', 'UC-33'],
            [244, 'IMS(I-V) No.2 G-8/1', 'co_education', 'I-V', 'URBAN-I', 'UC-33'],
            [245, 'IMS(I-V) No.3 G-8/1', 'co_education', 'I-V', 'URBAN-I', 'UC-33'],
            [246, 'IMS(I-V) No.1 G-8/2', 'co_education', 'I-V', 'URBAN-I', 'UC-33'],
            [247, 'IMS(I-V) No.2 G-8/2', 'co_education', 'I-V', 'URBAN-I', 'UC-33'],
            [248, 'IMS(I-V) PIMS G-8/3', 'co_education', 'I-V', 'URBAN-I', 'UC-33'],
            [249, 'IMS(I-V) F-8/2', 'co_education', 'I-V', 'URBAN-I', 'UC-28'],
            [295, 'IMS(I-V) No.2 G-7/2', 'co_education', 'I-V', 'URBAN-I', 'UC-31'],
            [297, 'IMSB(VI-X) G-8/1', 'boys', 'VI-X', 'URBAN-I', 'UC-33'],
            [300, 'IMS(I-V) F-8/3', 'co_education', 'I-V', 'URBAN-I', 'UC-28'],
            [301, 'IMSB (VI-X) F-8/3', 'boys', 'VI-X', 'URBAN-I', 'UC-28'],
            [303, 'IMSB(VI-X) G-7/3-1', 'boys', 'VI-X', 'URBAN-I', 'UC-30'],
            [304, 'IMSB(VI-X) G-6/4', 'boys', 'VI-X', 'URBAN-I', 'UC-27'],
            [305, 'IMCB G-6/2', 'boys', 'VI-XII', 'URBAN-I', 'UC-27'],
            [306, 'IMSB (VI-X) F-6/2', 'boys', 'VI-X', 'URBAN-I', 'UC-25'],
            [307, 'IMCB G-7/2', 'boys', 'VI-XII', 'URBAN-I', 'UC-31'],
            [308, 'IMCB G-7/4', 'boys', 'VI-XII', 'URBAN-I', 'UC-30'],
            [315, 'IMSB(VI-X) G-8/4', 'boys', 'VI-X', 'URBAN-I', 'UC-32'],
            [316, 'IMSB(VI-VIII Technical) G-7/4', 'boys', 'VI-VIII', 'URBAN-I', 'UC-30'],
            [317, 'IMSB(VI-X) E-9', 'boys', 'VI-X', 'URBAN-I', 'UC-28'],

            // ===== URBAN-II =====
            [250, 'IMSG(VI-X) G-9/3', 'girls', 'VI-X', 'URBAN-II', 'UC-34'],
            [251, 'IMSG(VI-X) G-9/4', 'girls', 'VI-X', 'URBAN-II', 'UC-34'],
            [252, 'IMS(I-V) No.2 G-9/4', 'co_education', 'I-V', 'URBAN-II', 'UC-34'],
            [253, 'IMS(I-V) No.1 G-9/4', 'co_education', 'I-V', 'URBAN-II', 'UC-34'],
            [254, 'IMS(I-V) No.3 St 68 G-9/3', 'co_education', 'I-V', 'URBAN-II', 'UC-34'],
            [255, 'IMS(I-V) No.2 St 7 G-9/3', 'co_education', 'I-V', 'URBAN-II', 'UC-34'],
            [256, 'IMS(I-V) No.1 G-9/3', 'co_education', 'I-V', 'URBAN-II', 'UC-34'],
            [257, 'IMCG G-9/2', 'girls', 'VI-XII', 'URBAN-II', 'UC-35'],
            [258, 'IMSG(I-X) G-9/1', 'girls', 'I-X', 'URBAN-II', 'UC-34'],
            [259, 'IMS(I-V) No.1 G-9/2', 'co_education', 'I-V', 'URBAN-II', 'UC-35'],
            [260, 'IMS(I-V) No.2 G-9/2', 'co_education', 'I-V', 'URBAN-II', 'UC-35'],
            [261, 'IMS(I-V) No.3 G-9/2', 'co_education', 'I-V', 'URBAN-II', 'UC-35'],
            [262, 'IMS(I-V) No.4 G-9/2', 'co_education', 'I-V', 'URBAN-II', 'UC-35'],
            [263, 'IMS(I-V) G-9/1', 'co_education', 'I-V', 'URBAN-II', 'UC-34'],
            [264, 'IMS(I-V) F-10/1', 'co_education', 'I-V', 'URBAN-II', 'UC-29'],
            [265, 'IMS(I-V) F-10/2', 'co_education', 'I-V', 'URBAN-II', 'UC-29'],
            [266, 'IMS(I-V) F-10/4', 'co_education', 'I-V', 'URBAN-II', 'UC-29'],
            [267, 'IMSG(VI-X) G-10/1', 'girls', 'VI-X', 'URBAN-II', 'UC-37'],
            [268, 'IMSG(I-X) G-10/3', 'girls', 'I-X', 'URBAN-II', 'UC-36'],
            [269, 'IMS(I-V) G-10/3', 'co_education', 'I-V', 'URBAN-II', 'UC-36'],
            [270, 'IMS(I-V) G-10/4', 'co_education', 'I-V', 'URBAN-II', 'UC-36'],
            [271, 'IMS(I-V) G-10/1', 'co_education', 'I-V', 'URBAN-II', 'UC-37'],
            [272, 'IMS(I-V) No.1 G-10/2', 'co_education', 'I-V', 'URBAN-II', 'UC-37'],
            [273, 'IMS(I-V) No.2 G-10/2', 'co_education', 'I-V', 'URBAN-II', 'UC-37'],
            [274, 'IMS(I-V) G-11/1', 'co_education', 'I-V', 'URBAN-II', 'UC-38'],
            [275, 'IMSG(I-X) G-11/2', 'girls', 'I-X', 'URBAN-II', 'UC-38'],
            [276, 'IMSG(VI-X) F-11/1', 'girls', 'VI-X', 'URBAN-II', 'UC-29'],
            [277, 'IMS(I-V) G-11/2', 'co_education', 'I-V', 'URBAN-II', 'UC-38'],
            [278, 'IMSG(VI-X) G-11/1', 'girls', 'VI-X', 'URBAN-II', 'UC-38'],
            [279, 'IMSG(VI-X) I-8/1', 'girls', 'VI-X', 'URBAN-II', 'UC-40'],
            [280, 'IMS(I-V) I-8/1', 'co_education', 'I-V', 'URBAN-II', 'UC-40'],
            [281, 'IMSG (I-VIII) I-9/4', 'girls', 'I-VIII', 'URBAN-II', 'UC-41'],
            [282, 'IMS(I-V) No.1 I-9/4', 'co_education', 'I-V', 'URBAN-II', 'UC-41'],
            [283, 'IMS(I-V) No.2 I-9/4', 'co_education', 'I-V', 'URBAN-II', 'UC-41'],
            [284, 'IMSG (I-VIII) I-8/1', 'girls', 'I-VIII', 'URBAN-II', 'UC-40'],
            [285, 'IMS(I-V) AIOU Colony', 'co_education', 'I-V', 'URBAN-II', 'UC-42'],
            [286, 'IMCG I-9/1', 'girls', 'VI-XII', 'URBAN-II', 'UC-41'],
            [287, 'IMSG(VI-X) I-10/4', 'girls', 'VI-X', 'URBAN-II', 'UC-42'],
            [288, 'IMSG (I-VIII) I-10/4', 'girls', 'I-VIII', 'URBAN-II', 'UC-42'],
            [289, 'IMS(I-V) No.1 I-10/1', 'co_education', 'I-V', 'URBAN-II', 'UC-42'],
            [290, 'IMS(I-V) No.2 I-10/1', 'co_education', 'I-V', 'URBAN-II', 'UC-42'],
            [291, 'IMS (I-V) I-10/2', 'co_education', 'I-V', 'URBAN-II', 'UC-42'],
            [292, 'IMS(I-V) No.2 I-9/1', 'co_education', 'I-V', 'URBAN-II', 'UC-41'],
            [293, 'IMSG(VI-X) I-9/4', 'girls', 'VI-X', 'URBAN-II', 'UC-41'],
            [294, 'IMS(I-V) No.1 I-9/1', 'co_education', 'I-V', 'URBAN-II', 'UC-41'],
            [296, 'IMCB G-9/4', 'boys', 'VI-XII', 'URBAN-II', 'UC-35'],
            [298, 'IMSB(VI-X) G-10/3', 'boys', 'VI-X', 'URBAN-II', 'UC-36'],
            [299, 'IMSB(VI-X),G-9/1', 'boys', 'VI-X', 'URBAN-II', 'UC-34'],
            [302, 'IMSB(VI-X) G-11/2', 'boys', 'VI-X', 'URBAN-II', 'UC-38'],
            [309, 'IMSB(VI-X) No.1 I-9/4', 'boys', 'VI-X', 'URBAN-II', 'UC-41'],
            [310, 'IMCB I-10/1,Street No.17', 'boys', 'VI-XII', 'URBAN-II', 'UC-42'],
            [311, 'IMSB(VI-X) No.2 I-9/4', 'boys', 'VI-X', 'URBAN-II', 'UC-41'],
            [312, 'IMSB(VI-X) I-10/2', 'boys', 'VI-X', 'URBAN-II', 'UC-42'],
            [313, 'IMSB (I-VIII) I-8/1', 'boys', 'I-VIII', 'URBAN-II', 'UC-40'],
            [314, 'IMSB (VI-X) I-8/4', 'boys', 'VI-X', 'URBAN-II', 'UC-40'],

            // ===== B.K =====
            [401, 'IMCB (VI-XII), (BSK) BHARA KAU', 'boys', 'VI-XII', 'B-K', 'UC-04'],
            [402, 'IMCB (VI-XII), CHAKSHAHZAD', 'boys', 'VI-XII', 'B-K', 'UC-22'],
            [403, 'IMSB (I-X) BHARA KAU', 'boys', 'I-X', 'B-K', 'UC-04'],
            [404, 'IMSB (I-X) CHATTAR', 'boys', 'I-X', 'B-K', 'UC-45'],
            [405, 'IMSB (VI-X) KUREE', 'boys', 'VI-X', 'B-K', 'UC-23'],
            [406, 'IMSB (vi-x) Noor pur Shahan', 'boys', 'VI-X', 'B-K', 'UC-02'],
            [407, 'IMCB (VI-XII) PIND BEGWAL', 'boys', 'VI-XII', 'B-K', 'UC-07'],
            [408, 'IMSB (I-X) SHAHDARA', 'boys', 'I-X', 'B-K', 'UC-45'],
            [409, 'IMSB (VI-X) RAWAL DAM', 'boys', 'VI-X', 'B-K', 'UC-24'],
            [410, 'IMSB (VI-X) TALHAR', 'boys', 'VI-X', 'B-K', 'UC-45'],
            [411, 'IMSB (I-X), SAID PUR', 'boys', 'I-X', 'B-K', 'UC-01'],
            [412, 'IMSB (I-VIII), BOBRI', 'boys', 'I-VIII', 'B-K', 'UC-04'],
            [413, 'IMSB (I-VIII) JANDALA (F.A)', 'boys', 'I-VIII', 'B-K', 'UC-45'],
            [414, 'IMSB (I-X), MAIRA BEGWAL', 'boys', 'I-X', 'B-K', 'UC-07'],
            [415, 'IMSB (I-VIII), MOHRA NOOR NIH', 'boys', 'I-VIII', 'B-K', 'UC-23'],
            [416, 'IMSB (I-X) PHULGRAN F.A', 'boys', 'I-X', 'B-K', 'UC-06'],
            [417, 'IMSB (I-VIII), KOT HATHIAL (NAI ABADI)', 'boys', 'I-VIII', 'B-K', 'UC-04'],
            [418, 'IMSB (I-VIII), MALWAR', 'boys', 'I-VIII', 'B-K', 'UC-45'],
            [419, 'IMSB (I-VIII), SATRA MEEL', 'boys', 'I-VIII', 'B-K', 'UC-46'],
            [420, 'IMSB (I-V), ATHAL', 'boys', 'I-V', 'B-K', 'UC-45'],
            [421, 'IMSB (I-V), BHUDDO', 'boys', 'I-V', 'B-K', 'UC-45'],
            [422, 'IMSB (I-V), CHAHANN MASTAL (F.A) H-11', 'boys', 'I-V', 'TARNAUL', 'UC-43'],
            [423, 'IMSB (I-V), DHOKE JERRANI', 'boys', 'I-V', 'B-K', 'UC-45'],
            [424, 'IMSB (I-V), DHOKE SYEDAN', 'boys', 'I-V', 'B-K', 'UC-45'],
            [425, 'IMSB (I-V), DOHALA SYEDAN', 'boys', 'I-V', 'B-K', 'UC-01'],
            [426, 'IMSB (I-V), KALRAN', 'boys', 'I-V', 'B-K', 'UC-45'],
            [427, 'IMSB (I-V), MALOT', 'boys', 'I-V', 'B-K', 'UC-06'],
            [428, 'IMSB(I-V), MANGIAL(F.A)', 'boys', 'I-V', 'B-K', 'UC-45'],
            [429, 'IMSB (I-V), MAL', 'boys', 'I-V', 'B-K', 'UC-45'],
            [430, 'IMSB (I-V), NOOR PUR SHAHAN', 'boys', 'I-V', 'B-K', 'UC-02'],
            [431, 'IMSB (I-V), PALALI', 'boys', 'I-V', 'B-K', 'UC-45'],
            [432, 'IMSB (I-V), PIND BEGWAL', 'boys', 'I-V', 'B-K', 'UC-07'],
            [433, 'IMSB (I-V), RUMLI', 'boys', 'I-V', 'B-K', 'UC-24'],
            [434, 'IMSB (I-V), SIHALI', 'boys', 'I-V', 'B-K', 'UC-45'],
            [435, 'IMSB (I-V), TALHAR (FA) IBD', 'boys', 'I-V', 'B-K', 'UC-45'],
            [436, 'IMSB (I-V), KOT HATHIAL, QAZIABAD', 'boys', 'I-V', 'B-K', 'UC-04'],
            [437, 'IMSB (I-V), GOKINA', 'boys', 'I-V', 'B-K', 'UC-45'],
            [438, 'IMSB (I-V), KUREE', 'boys', 'I-V', 'B-K', 'UC-23'],
            [439, 'IMSB (I-VIII), MALPUR', 'boys', 'I-VIII', 'B-K', 'UC-03'],
            [440, 'IMSB (I-V), RAWAL DAM', 'boys', 'I-V', 'B-K', 'UC-24'],
            [441, 'IMSB (I-VIII), CHATTA BAKHTAWAR', 'boys', 'I-VIII', 'B-K', 'UC-45'],
            [442, 'IMCG (I-XII), MARGALLA TOWN', 'girls', 'I-XII', 'B-K', 'UC-39'],
            [443, 'IMCG (VI-XII),KOT HATHIAL BHARA KAHU', 'girls', 'VI-XII', 'B-K', 'UC-04'],
            [444, 'IMCG (I-XII), UNIVERSITY COLONY (U.C)', 'girls', 'I-XII', 'B-K', 'UC-22'],
            [445, 'IMSG (I-X), NHC', 'girls', 'I-X', 'B-K', 'UC-22'],
            [446, 'IMCG (VI-XII) NHC, CHAK SHAHZAD', 'girls', 'VI-XII', 'B-K', 'UC-22'],
            [447, 'IMSG (I-X) GOKINA', 'girls', 'I-X', 'B-K', 'UC-45'],
            [448, 'IMSG (I-X) KURRI', 'girls', 'I-X', 'B-K', 'UC-23'],
            [449, 'IMCG (VI-XII) MALPUR', 'girls', 'VI-XII', 'B-K', 'UC-03'],
            [450, 'IMSG (I-X) PHULGRAN', 'girls', 'I-X', 'B-K', 'UC-06'],
            [451, 'IMCG, RAWAL TOWN', 'girls', 'VI-XII', 'B-K', 'UC-24'],
            [452, 'IMSG (I-X) TALHAR', 'girls', 'I-X', 'B-K', 'UC-45'],
            [453, 'IMCG (I-XII) MAIRA BEGWAL', 'girls', 'I-XII', 'B-K', 'UC-07'],
            [454, 'IMSG (I-X) LAKHWAL', 'girls', 'I-X', 'B-K', 'UC-45'],
            [455, 'IMCG (I-XII) PIND BEGWAL', 'girls', 'I-XII', 'B-K', 'UC-07'],
            [456, 'IMSG (I-X) NOORPUR SHAHAN', 'girls', 'I-X', 'B-K', 'UC-02'],
            [457, 'IMSG (I-X) SHAHDRA KHURD', 'girls', 'I-X', 'B-K', 'UC-45'],
            [458, 'IMSG (I-X), SAID PUR', 'girls', 'I-X', 'B-K', 'UC-01'],
            [459, 'IMSG (I-X), MALOT', 'girls', 'I-X', 'B-K', 'UC-06'],
            [460, 'IMSG (I-VIII), SHAHDRA KALAN', 'girls', 'I-VIII', 'B-K', 'UC-45'],
            [461, 'IMSG (I-VIII), BAIN NALA', 'girls', 'I-VIII', 'B-K', 'UC-01'],
            [462, 'IMSG (I-VIII) Mandla (FA)', 'girls', 'I-VIII', 'B-K', 'UC-45'],
            [463, 'IMSG (I-X), MOHRA NOOR', 'girls', 'I-X', 'B-K', 'UC-23'],
            [464, 'IMSG (I-VIII), BHARA KAU', 'girls', 'I-VIII', 'B-K', 'UC-04'],
            [465, 'IMSG (I-VIII), BOBRI', 'girls', 'I-VIII', 'B-K', 'UC-04'],
            [466, 'IMSG (I-VIII), KOT HATHIAL', 'girls', 'I-VIII', 'B-K', 'UC-04'],
            [467, 'IMSG (I-VIII), SANJALIAN', 'girls', 'I-VIII', 'B-K', 'UC-45'],
            [468, 'IMSG (I-V), ATHAL', 'girls', 'I-V', 'B-K', 'UC-45'],
            [469, 'IMSG (I-V), (NHC) CHAK SHEHZAD', 'girls', 'I-V', 'B-K', 'UC-22'],
            [470, 'IMSG (I-V), DHOKE JERRANI', 'girls', 'I-V', 'B-K', 'UC-45'],
            [471, 'IMSG (I-V), KOT HATHIAL, NAI ABADI', 'girls', 'I-V', 'B-K', 'UC-04'],
            [472, 'IMSG (I-VIII), MOHRIAN', 'girls', 'I-VIII', 'B-K', 'UC-45'],
            [473, 'IMSG (I-V), PIND BEGWAL, DANA', 'girls', 'I-V', 'B-K', 'UC-07'],
            [474, 'IMSG (I-V), SHAH PUR', 'girls', 'I-V', 'B-K', 'UC-45'],
            [475, 'IMSG (I-V), SUBBAN', 'girls', 'I-V', 'B-K', 'UC-45'],
            [476, 'IMSG (I-V) , SHAHZAD TOWN', 'girls', 'I-V', 'B-K', 'UC-22'],
            [477, 'IMSG (I-V), BHARA KAU, NAI ABADI', 'girls', 'I-V', 'B-K', 'UC-04'],
            [478, 'IMSG (I-V), MALPUR (F.A)', 'girls', 'I-V', 'B-K', 'UC-03'],
            [479, 'IMSG (I-V) Maira Malpur', 'girls', 'I-V', 'B-K', 'UC-03'],

            // ===== SIHALA =====
            [501, 'IMCG Herdogher', 'girls', 'VI-XII', 'SIHALA', 'UC-14'],
            [502, 'IMCG. Rawat', 'girls', 'VI-XII', 'SIHALA', 'UC-12'],
            [503, 'IMCG Humak', 'girls', 'VI-XII', 'SIHALA', 'UC-13'],
            [504, 'IMSG (VI-X) Sihala', 'girls', 'VI-X', 'SIHALA', 'UC-14'],
            [505, 'IMSG (I-X) Nara Syedan', 'girls', 'I-X', 'SIHALA', 'UC-14'],
            [506, 'IMSG (I-X) Dhoke Gangal', 'girls', 'I-X', 'SIHALA', 'UC-14'],
            [507, 'IMCG (I-X) Pind Malkan', 'girls', 'I-X', 'SIHALA', 'UC-17'],
            [508, 'IMCG Lohi Bher', 'girls', 'I-XII', 'SIHALA', 'UC-15'],
            [509, 'IMCG Mohra Nagial', 'girls', 'VI-XII', 'SIHALA', 'UC-14'],
            [510, 'IMSG (I-X) Humak', 'girls', 'I-X', 'SIHALA', 'UC-13'],
            [511, 'IMSG (I-X) Gagri', 'girls', 'I-X', 'SIHALA', 'UC-13'],
            [512, 'IMSG (I-X) Upran Gohra', 'girls', 'I-X', 'SIHALA', 'UC-14'],
            [513, 'IMSG (I-VIII) Bhimber Trar', 'girls', 'I-VIII', 'SIHALA', 'UC-14'],
            [514, 'IMSG (I-VIII) Mohri Rawat', 'girls', 'I-VIII', 'SIHALA', 'UC-12'],
            [515, 'IMSG (I-X) R/Col. Rawat', 'girls', 'I-X', 'SIHALA', 'UC-12'],
            [516, 'IMSG (I-VIII) Bhangril', 'girls', 'I-VIII', 'SIHALA', 'UC-14'],
            [517, 'IMSG (I-VIII) Rajwal', 'girls', 'I-VIII', 'SIHALA', 'UC-14'],
            [518, 'IMSG (I-X) Dhaliala', 'girls', 'I-X', 'SIHALA', 'UC-14'],
            [519, 'IMSG (I-VIII) Niazian', 'girls', 'I-VIII', 'SIHALA', 'UC-14'],
            [520, 'IMSG (I-V) (M.T) Humak', 'girls', 'I-V', 'SIHALA', 'UC-13'],
            [521, 'IMSG (I-VIII)Peija', 'girls', 'I-VIII', 'SIHALA', 'UC-14'],
            [522, 'IMSG (I-V) Pindory Syedan', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [523, 'IMSG (I-VIII) Miana Thub', 'girls', 'I-VIII', 'SIHALA', 'UC-14'],
            [524, 'IMSG (I-VIII) Jandala', 'girls', 'I-VIII', 'SIHALA', 'UC-14'],
            [525, 'IMSG (I-V) Rawat', 'girls', 'I-V', 'SIHALA', 'UC-12'],
            [526, 'IMSG (I-V) Sheikhpur', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [527, 'IMSG (I-V) Herdogher', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [528, 'IMSG (I-V) Mughal', 'girls', 'I-V', 'SIHALA', 'UC-11'],
            [529, 'IMSG (I-V) Sihala', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [530, 'IMSG (I-V) Sihala Mirzian', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [531, 'IMSG (I-V) Hoon Dhamial', 'girls', 'I-V', 'SIHALA', 'UC-16'],
            [532, 'IMSG (I-V) Gohra Mast', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [533, 'IMSG (I-V) Ladhiot', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [534, 'IMSG (I-V) Humak', 'girls', 'I-V', 'SIHALA', 'UC-13'],
            [535, 'IMSG (I-V) GANGOTA SYEDAN', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [536, 'IMSG (I-V)Boora Bangial', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [537, 'IMSG (I-V) Mohri Mughal', 'girls', 'I-V', 'SIHALA', 'UC-11'],
            [538, 'IMSG (I-VIII) PTC Sihala', 'girls', 'I-VIII', 'SIHALA', 'UC-14'],
            [539, 'IMSG (I-V) PWD Col', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [540, 'IMSG (I-V) Sihala Khurd', 'girls', 'I-V', 'SIHALA', 'UC-14'],
            [541, 'IMCB Mughal', 'boys', 'VI-XII', 'SIHALA', 'UC-11'],
            [542, 'IMSB (VI-X) Sihala', 'boys', 'VI-X', 'SIHALA', 'UC-14'],
            [543, 'IMCB Rawat (F.A)', 'boys', 'VI-XII', 'SIHALA', 'UC-12'],
            [544, 'IMCB Bhimber Trar', 'boys', 'VI-XII', 'SIHALA', 'UC-14'],
            [545, 'IMCB Humak', 'boys', 'VI-XII', 'SIHALA', 'UC-13'],
            [546, 'IMCB Pahg Panwal', 'boys', 'VI-XII', 'SIHALA', 'UC-14'],
            [547, 'IMCB Mohra Nagial', 'boys', 'I-XII', 'SIHALA', 'UC-14'],
            [548, 'IMSB (I-X) Gagri', 'boys', 'I-X', 'SIHALA', 'UC-13'],
            [549, 'IMSB (I-X) Dhaliala', 'boys', 'I-X', 'SIHALA', 'UC-14'],
            [550, 'IMSB (I-X) Banni Saran', 'boys', 'I-X', 'SIHALA', 'UC-14'],
            [551, 'IMSB (I-V) Sihala', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [552, 'IMSB (I-V) Lohi Bher', 'boys', 'I-V', 'SIHALA', 'UC-15'],
            [553, 'IMSB (I-V) Bhimber Trar', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [554, 'IMSB (I-VIII) Ara Burji', 'boys', 'I-VIII', 'SIHALA', 'UC-16'],
            [555, 'IMSB (I-V) Humak', 'boys', 'I-V', 'SIHALA', 'UC-13'],
            [556, 'IMSB (I-VIII)S/Mirzian', 'boys', 'I-VIII', 'SIHALA', 'UC-14'],
            [557, 'IMSB (I-V) Mughal', 'boys', 'I-V', 'SIHALA', 'UC-11'],
            [558, 'IMSB (I-VIII) Herdogher', 'boys', 'I-VIII', 'SIHALA', 'UC-14'],
            [559, 'IMSB (I-V) Darwala', 'boys', 'I-V', 'SIHALA', 'UC-16'],
            [560, 'IMSB (I-V) Boora Bangial', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [561, 'IMSB (I-V) Pind Malkan', 'boys', 'I-V', 'SIHALA', 'UC-17'],
            [562, 'IMSB (I-V) Mohra Kalu', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [563, 'IMSB (I-V) D/Mai Nawab', 'boys', 'I-V', 'SIHALA', 'UC-16'],
            [564, 'IMSB (I-V) Rajwal', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [565, 'IMSB (I-V) Kortana', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [566, 'IMSB (I-V) Bhangril', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [567, 'IMSB (I-V) Chak', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [568, 'IMSB (I-V) Mohri Rawat', 'boys', 'I-V', 'SIHALA', 'UC-12'],
            [569, 'IMSB (I-VIII), Koral', 'boys', 'I-VIII', 'SIHALA', 'UC-17'],
            [570, 'IMSB (I-VIII) Nara Syedan', 'boys', 'I-VIII', 'SIHALA', 'UC-14'],
            [571, 'IMSB (I-V)Chak Kamdar', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [572, 'IMSB (I-V). Sigga', 'boys', 'I-V', 'SIHALA', 'UC-14'],
            [573, 'IMSG (I-V) CBR Colony', 'girls', 'I-V', 'SIHALA', 'UC-15'],
            [574, 'IMS (I-V) Soan Garden, Lohi Bheer', 'boys', 'I-V', 'SIHALA', 'UC-15'],
            [575, 'IMS (I-V) Gohra Shahan', 'girls', 'I-V', 'SIHALA', 'UC-14'],

            // ===== TARNAUL =====
            [601, 'IMCB (VI-XII) Tarnaul', 'boys', 'VI-XII', 'TARNAUL', 'UC-47'],
            [602, 'IMSB (VI-X) Sang Jani', 'boys', 'VI-X', 'TARNAUL', 'UC-45'],
            [603, 'IMSB (I-X) Naugazi', 'boys', 'I-X', 'TARNAUL', 'UC-43'],
            [604, 'IMSB (I-X) I-14', 'boys', 'I-X', 'TARNAUL', 'UC-44'],
            [605, 'IMSB (VI-X) Noon', 'boys', 'VI-X', 'TARNAUL', 'UC-45'],
            [606, 'IMSB (I-X) Maira Akku', 'boys', 'I-X', 'TARNAUL', 'UC-45'],
            [607, 'IMSB (VI-X) Shah Allah Ditta', 'boys', 'VI-X', 'TARNAUL', 'UC-49'],
            [608, 'IMSB (VI-X) Golra', 'boys', 'VI-X', 'TARNAUL', 'UC-50'],
            [609, 'IMSB (I-X) Badana Kalan', 'boys', 'I-X', 'TARNAUL', 'UC-46'],
            [610, 'IMSG (I-X) Sangjani', 'girls', 'I-X', 'TARNAUL', 'UC-45'],
            [611, 'IMSG (I-X) Jhangi Syedan (F.A.)', 'girls', 'I-X', 'TARNAUL', 'UC-45'],
            [612, 'IMCG (I-XII) Shah Allah Ditta', 'girls', 'I-XII', 'TARNAUL', 'UC-49'],
            [613, 'IMCG (I-XII) Badana Kalan', 'girls', 'I-XII', 'TARNAUL', 'UC-46'],
            [614, 'IMCG (I-XII) Tarnaul', 'girls', 'I-XII', 'TARNAUL', 'UC-47'],
            [615, 'IMCG (I-XII) Golra', 'girls', 'I-XII', 'TARNAUL', 'UC-50'],
            [616, 'IMSG (VI-X) I-14/3', 'girls', 'VI-X', 'TARNAUL', 'UC-44'],
            [617, 'IMSG (I-X) Naugazi (F.A)', 'girls', 'I-X', 'TARNAUL', 'UC-43'],
            [618, 'IMSG (I-X) BQB', 'girls', 'I-X', 'TARNAUL', 'UC-43'],
            [619, 'IMSB (I-VIII) Dhoke Jouri', 'boys', 'I-VIII', 'TARNAUL', 'UC-45'],
            [620, 'IMSB (I-VIII) Dhoke Paracha', 'boys', 'I-VIII', 'TARNAUL', 'UC-45'],
            [621, 'IMSB (I-X) Maira Beri(F.A)', 'boys', 'I-X', 'TARNAUL', 'UC-45'],
            [622, 'IMSB (I-VIII) Chellow', 'boys', 'I-VIII', 'TARNAUL', 'UC-50'],
            [623, 'IMSG (I-VIII) Dhoke Jouri', 'girls', 'I-VIII', 'TARNAUL', 'UC-45'],
            [624, 'IMSG (I-VIII) Pind Paracha', 'girls', 'I-VIII', 'TARNAUL', 'UC-45'],
            [625, 'IMSG (I-VIII) Noon', 'girls', 'I-VIII', 'TARNAUL', 'UC-45'],
            [626, 'IMSG (I-VIII) Dhreak Mohri', 'girls', 'I-VIII', 'TARNAUL', 'UC-45'],
            [627, 'IMSG (I-X) Maira Beri', 'girls', 'I-X', 'TARNAUL', 'UC-45'],
            [628, 'IMSG (I-VIII) Dhoke Paracha', 'girls', 'I-VIII', 'TARNAUL', 'UC-45'],
            [629, 'IMSG (I-VIII) Sarae Kharboza (F.A)', 'girls', 'I-VIII', 'TARNAUL', 'UC-48'],
            [630, 'IMSB (I-V) Tarnaul', 'boys', 'I-V', 'TARNAUL', 'UC-47'],
            [631, 'IMSB (I-V) Tamman', 'boys', 'I-V', 'TARNAUL', 'UC-47'],
            [632, 'IMSB (I-V) Sang Jani', 'boys', 'I-V', 'TARNAUL', 'UC-45'],
            [633, 'IMSB (I-V) Dora', 'boys', 'I-V', 'TARNAUL', 'UC-47'],
            [634, 'IMSB (I-V) Sheikhpur', 'boys', 'I-V', 'TARNAUL', 'UC-47'],
            [635, 'IMSB (I-V) Pind Hoon', 'boys', 'I-V', 'TARNAUL', 'UC-47'],
            [636, 'IMSB (I-V) Noon', 'boys', 'I-V', 'TARNAUL', 'UC-45'],
            [637, 'IMSB (I-V) Shah Allah Ditta', 'boys', 'I-V', 'TARNAUL', 'UC-49'],
            [638, 'IMSB (I-V) Karamabad', 'boys', 'I-V', 'TARNAUL', 'UC-50'],
            [639, 'IMSB (I-V) Dhoke Lubana', 'boys', 'I-V', 'TARNAUL', 'UC-50'],
            [640, 'IMSB (I-V) JOHD', 'boys', 'I-V', 'TARNAUL', 'UC-43'],
            [641, 'IMSB (I-V) Sarae Karboza', 'boys', 'I-V', 'TARNAUL', 'UC-48'],
            [642, 'IMSB (I-V) Pind Parian', 'boys', 'I-V', 'TARNAUL', 'UC-48'],
            [643, 'IMSB (I-V) Dhreak Mohri', 'boys', 'I-V', 'TARNAUL', 'UC-45'],
            [644, 'IMSB (I-V) Seri Saral', 'boys', 'I-V', 'TARNAUL', 'UC-45'],
            [645, 'IMSB (I-V) Bokra', 'boys', 'I-V', 'TARNAUL', 'UC-44'],
            [646, 'IMSB (I-V) Jhangi Syedan (FA)', 'boys', 'I-V', 'TARNAUL', 'UC-45'],
            [647, 'IMSB (I-V) Golra', 'boys', 'I-V', 'TARNAUL', 'UC-50'],
            [648, 'IMSG (I-V) Bheka Syedan', 'girls', 'I-V', 'TARNAUL', 'UC-45'],
            [649, 'IMSG (I-V) Pind Parian', 'girls', 'I-V', 'TARNAUL', 'UC-48'],
            [650, 'IMSG (I-V) Sheikhpur', 'girls', 'I-V', 'TARNAUL', 'UC-47'],
            [651, 'IMSG (I-V) Dhoke Hashoo', 'girls', 'I-V', 'TARNAUL', 'UC-49'],
            [652, 'IMSG (I-V) Dhoke Suleman', 'girls', 'I-V', 'TARNAUL', 'UC-49'],
            [653, 'IMSG (I-V) Sarae Madhu', 'girls', 'I-V', 'TARNAUL', 'UC-49'],
            [654, 'IMSG (I-V) I-14/3', 'girls', 'I-V', 'TARNAUL', 'UC-44'],
            [655, 'IMS (I-VIII) D-17', 'co_education', 'I-VIII', 'TARNAUL', 'UC-39'],

            // ===== NILORE =====
            [701, 'IMCB,NILORE', 'boys', 'VI-XII', 'NILORE', 'UC-18'],
            [702, 'IMSB(VI-X) CHIRAH', 'boys', 'VI-X', 'NILORE', 'UC-09'],
            [703, 'IMSB(I-X) TUMAIR', 'boys', 'I-X', 'NILORE', 'UC-08'],
            [704, 'IMSB(I-X)JAGIOT', 'boys', 'I-X', 'NILORE', 'UC-08'],
            [705, 'IMSB(VI-X)JHANG SYDEN', 'boys', 'VI-X', 'NILORE', 'UC-45'],
            [706, 'IMSB(VI-X)TARLAI', 'boys', 'VI-X', 'NILORE', 'UC-19'],
            [707, 'IMSB(I-X) KHANNA DAK', 'boys', 'I-X', 'NILORE', 'UC-18'],
            [708, 'IMCB,JABA TALI', 'boys', 'I-XII', 'NILORE', 'UC-20'],
            [709, 'IMSB(I-X)KIRPA', 'boys', 'I-X', 'NILORE', 'UC-10'],
            [710, 'IMCG,NILORE', 'girls', 'VI-XII', 'NILORE', 'UC-18'],
            [711, 'IMCG CHIRAH', 'girls', 'VI-XII', 'NILORE', 'UC-09'],
            [712, 'IMCG,PUNJGRAN', 'girls', 'VI-XII', 'NILORE', 'UC-20'],
            [713, 'IMCG,TARLAI', 'girls', 'VI-XII', 'NILORE', 'UC-19'],
            [714, 'IMCG,JAGIOT', 'girls', 'I-XII', 'NILORE', 'UC-08'],
            [715, 'IMCG,PEHOUNT', 'girls', 'I-XII', 'NILORE', 'UC-21'],
            [716, 'IMCG,THANDA PANI (FA)', 'girls', 'I-XII', 'NILORE', 'UC-20'],
            [717, 'IMSB(I-X) KHANNA NAI ABADI', 'boys', 'I-X', 'NILORE', 'UC-18'],
            [718, 'IMSB(I-VIII) ALI PUR', 'boys', 'I-VIII', 'NILORE', 'UC-20'],
            [719, 'IMSB(I-VIII) DELLA', 'boys', 'I-VIII', 'NILORE', 'UC-20'],
            [720, 'IMSB(I-X) THANDA PANI', 'boys', 'I-X', 'NILORE', 'UC-52'],
            [721, 'IMSB(I-VIII) PEHOUNT', 'boys', 'I-VIII', 'NILORE', 'UC-21'],
            [722, 'IMSG(I-VIII) KH. DAK', 'girls', 'I-VIII', 'NILORE', 'UC-18'],
            [723, 'IMSG(I-X) NEW SHAKRIAL', 'girls', 'I-X', 'NILORE', 'UC-53'],
            [724, 'IMSG (I-VIII) KALIA (FA)', 'girls', 'I-VIII', 'NILORE', 'UC-09'],
            [725, 'IMSG(I-X) JABA TAILI', 'girls', 'I-X', 'NILORE', 'UC-20'],
            [726, 'IMSB(I-V)SOHAN', 'boys', 'I-V', 'NILORE', 'UC-21'],
            [727, 'IMSB(I-V)SHARIFABAD', 'boys', 'I-V', 'NILORE', 'UC-19'],
            [728, 'IMSB(I-V)KHADRAPPAR', 'boys', 'I-V', 'NILORE', 'UC-19'],
            [729, 'IMSB(I-V) CH. BANGIAL', 'boys', 'I-V', 'NILORE', 'UC-10'],
            [730, 'IMSB(I-V)CHIRAH', 'boys', 'I-V', 'NILORE', 'UC-09'],
            [731, 'IMSB(I-VIII) KIJNAH', 'boys', 'I-V', 'NILORE', 'UC-08'],
            [732, 'IMSB(I-V) MOHARA SOLINA', 'boys', 'I-V', 'NILORE', 'UC-09'],
            [733, 'IMSB(I-V) JHANG SYDEN', 'boys', 'I-V', 'NILORE', 'UC-45'],
            [734, 'IMSB(I-V)TARLAI', 'boys', 'I-V', 'NILORE', 'UC-19'],
            [735, 'IMSB(I-V)MOHARA', 'boys', 'I-V', 'NILORE', 'UC-08'],
            [736, 'IMSB(I-V)ARA', 'boys', 'I-V', 'NILORE', 'UC-10'],
            [737, 'IMSB(I-V)KHANNA KAK', 'boys', 'I-V', 'NILORE', 'UC-54'],
            [738, 'IMSB(I-V)PINDMISTRIAN', 'boys', 'I-V', 'NILORE', 'UC-52'],
            [739, 'IMSG(I-V)SHAKRIAL', 'girls', 'I-V', 'NILORE', 'UC-55'],
            [740, 'IMSG(I-V) KHANNA NAI ABADI', 'girls', 'I-V', 'NILORE', 'UC-18'],
            [741, 'IMSG(I-V) NO.I TARLAI', 'girls', 'I-V', 'NILORE', 'UC-19'],
            [742, 'IMSG(I-V) NO.2 TARLAI', 'girls', 'I-V', 'NILORE', 'UC-19'],
            [743, 'IMSG(I-V)TAMMA', 'girls', 'I-V', 'NILORE', 'UC-19'],
            [744, 'IMSG(I-V) ALI PUR (MV)', 'girls', 'I-V', 'NILORE', 'UC-20'],
            [745, 'IMSG(I-V)ALI PUR FRASH', 'girls', 'I-V', 'NILORE', 'UC-20'],
            [746, 'IMSG(I-V) SEVERA', 'girls', 'I-V', 'NILORE', 'UC-07'],
            [747, 'IMSG (I-V) CHOUNIAL BANGIAL', 'girls', 'I-V', 'NILORE', 'UC-10'],
            [748, 'IMSG(I-V)HERNO', 'girls', 'I-V', 'NILORE', 'UC-52'],
            [749, 'IMSG(I-X) DARKALA', 'girls', 'I-X', 'NILORE', 'UC-52'],
            [750, 'IMSG(I-V) DHOK FATHALL', 'girls', 'I-V', 'NILORE', 'UC-08'],
            [751, 'IMSG(I-V)TUMIAR', 'girls', 'I-V', 'NILORE', 'UC-08'],
            [752, 'IMSG(I-VIII) KIJNAH', 'girls', 'I-VIII', 'NILORE', 'UC-08'],
            [753, 'IMSG(I-V) SIMLY DAM', 'girls', 'I-V', 'NILORE', 'UC-08'],
            [754, 'IMSG(I-V) CHIRAH', 'girls', 'I-V', 'NILORE', 'UC-09'],
            [755, 'IMSG(I-V) KALIA', 'girls', 'I-V', 'NILORE', 'UC-09'],
            [756, 'IMSG(I-V) JHANG SYEDAN', 'girls', 'I-V', 'NILORE', 'UC-45'],
            [757, 'IMSG (I-V) CHAPPAR Ghasota (F.A)', 'girls', 'I-V', 'NILORE', 'UC-09'],
            [758, 'IMSG(I-V) CHAKHTAN', 'girls', 'I-V', 'NILORE', 'UC-09'],
            [759, 'IMSG(I-V) NILORE', 'girls', 'I-V', 'NILORE', 'UC-18'],
            [760, 'IMSG(I-V) PUNJGRAN (760)', 'girls', 'I-V', 'NILORE', 'UC-20'],
            [761, 'IMSG(I-VIII) SOHAN', 'girls', 'I-X', 'NILORE', 'UC-21'],
            [762, 'IMSB(I-V)NILORE', 'boys', 'I-V', 'NILORE', 'UC-18'],
            [763, 'IMSG(I-V) ALI PUR SOUTH', 'girls', 'I-V', 'NILORE', 'UC-20'],
            [764, 'IMSB(I-V) SIRRI', 'boys', 'I-V', 'NILORE', 'UC-52'],
            [765, 'IMSG(I-V) FRASH TOWN', 'girls', 'I-V', 'NILORE', 'UC-20'],
            [766, 'IMCG,KIRPA', 'girls', 'VI-XII', 'NILORE', 'UC-10'],
            [767, 'IMSB(I-V)BIATH', 'boys', 'I-V', 'NILORE', 'UC-09'],

            // ===== MODEL COLLEGES & POST-GRADUATE =====
            [801, 'IMCB, SIHALA, Islamabad.', 'boys', 'XI-XII', 'SIHALA', 'UC-14'],
            [802, 'IMPC H-8 ISLAMABAD', 'boys', 'XI-XIV', 'URBAN-II', 'UC-40'],
            [803, 'IMCB, F-10/4', 'boys', 'XI-XIV', 'URBAN-II', 'UC-29'],
            [804, 'IMCB, H-9', 'boys', 'XI-XIV', 'URBAN-II', 'UC-41'],
            [805, 'IMPCC (B), H-8/4', 'boys', 'XI-XIV', 'URBAN-II', 'UC-40'],
            [806, 'IMCG (PG), F-7/2', 'girls', 'XI-XIV', 'URBAN-I', 'UC-28'],
            [807, 'IMCG(PG), G-10/4', 'girls', 'XI-XIV', 'URBAN-II', 'UC-36'],
            [808, 'IMCG (MT) Humak', 'girls', 'XI-XIV', 'SIHALA', 'UC-13'],
            [809, 'IMCG, I-8/3', 'girls', 'XI-XIV', 'URBAN-II', 'UC-40'],
            [810, 'IMCG (PG), F-7/4, Islamabad', 'girls', 'XI-XIV', 'URBAN-I', 'UC-28'],
            [811, 'IMCG I-14/3', 'girls', 'XI-XII', 'TARNAUL', 'UC-44'],
            [812, 'IMCG (PG) Bharakahu Islamabad', 'girls', 'XI-XII', 'B-K', 'UC-04'],
            [813, 'HE&MC F-11/1', 'girls', 'XI-XIV', 'URBAN-II', 'UC-29'],
            [901, 'IMCB, F-10/3', 'boys', 'Model College', 'URBAN-II', 'UC-29'],
            [902, 'IMCB, F-11/1', 'boys', 'Model College', 'URBAN-II', 'UC-29'],
            [903, 'IMCG, F-11/3', 'girls', 'Model College', 'URBAN-II', 'UC-29'],
            [904, 'IMCB, F-7/3', 'boys', 'Model College', 'URBAN-I', 'UC-28'],
            [905, 'IMCB, F-8/4', 'boys', 'Model College', 'URBAN-I', 'UC-28'],
            [906, 'IMCB, G-10/4', 'boys', 'Model College', 'URBAN-II', 'UC-36'],
            [907, 'IMCB, G-11/1', 'boys', 'Model College', 'URBAN-II', 'UC-38'],
            [908, 'ICB G-6/3', 'boys', 'Model College', 'URBAN-I', 'UC-27'],
            [909, 'IMCB, I-10/1', 'boys', 'Model College', 'URBAN-II', 'UC-42'],
            [910, 'IMCB, I-8/3', 'boys', 'Model College', 'URBAN-II', 'UC-40'],
            [911, 'IMCCG (COM), F-10/3', 'girls', 'Model College', 'URBAN-II', 'UC-29'],
            [912, 'IMCG, ST. 25, F-6/2', 'girls', 'Model College', 'URBAN-I', 'UC-25'],
            [913, 'ICG, F-6/2', 'girls', 'Model College', 'URBAN-I', 'UC-25'],
            [914, 'IMCG, F-7/4', 'girls', 'Model College', 'URBAN-I', 'UC-28'],
            [915, 'IMCG, F-8/1', 'girls', 'Model College', 'URBAN-I', 'UC-28'],
            [916, 'IMCG, G-10/2', 'girls', 'Model College', 'URBAN-II', 'UC-37'],
            [917, 'IMCG, St. # 23, I-10/4', 'girls', 'Model College', 'URBAN-II', 'UC-42'],
            [918, 'IMCG, I-8/4', 'girls', 'Model College', 'URBAN-II', 'UC-40'],
            [919, 'IMCG, F-10/2', 'girls', 'Model College', 'URBAN-II', 'UC-29'],
            [920, 'IMCG, Korang Town', 'girls', 'Model College', 'SIHALA', 'UC-17'],
            [921, 'IMCG G-13/1', 'girls', 'Model College', 'TARNAUL', 'UC-39'],
            [922, 'IMCG G-14/4', 'girls', 'Model College', 'TARNAUL', 'UC-39'],
            [923, 'IMCB G-13/2', 'boys', 'Model College', 'TARNAUL', 'UC-39'],
            [924, 'IMCB G-15', 'boys', 'Model College', 'TARNAUL', 'UC-39'],
            [925, 'IMCB Pakistan Town', 'boys', 'Model College', 'SIHALA', 'UC-17'],
            [926, 'IMCB Maira Begwal', 'boys', 'Model College', 'B-K', 'UC-07'],
        ];

        // ------------------------------------------------------------------
        // 4. Insert all schools
        // ------------------------------------------------------------------
        $insertCount = 0;
        foreach ($schools as $school) {
            $sectorId = $sectors[$school[4]] ?? null;
            $ucId     = $ucs[$school[5]] ?? null;

            if (!$sectorId) {
                $this->command->error("Sector not found: {$school[4]} for EMIS {$school[0]}");
                continue;
            }
            if (!$ucId) {
                $this->command->error("Union Council not found: {$school[5]} for EMIS {$school[0]}");
                continue;
            }

            Institution::firstOrCreate(
                ['code' => (string) $school[0]],
                [
                    'name'               => $school[1],
                    'gender'             => $school[2],
                    'type'               => $school[3],
                    'sector_id'          => $sectorId,
                    'uc_id'              => $ucId,
                    'shift'              => 'morning',
                    'admission_status'   => 'not_started',
                    'has_matric_tech'    => false,
                    'has_transport'      => false,
                    'has_meal_program'   => false,
                    'has_evening_classes'=> false,
                    'is_active'          => true,
                ]
            );
            $insertCount++;
        }

        $this->command->info("{$insertCount} institutions processed.");

        // ------------------------------------------------------------------
        // 5. Set Cambridge flag for specific institutions
        //    (IMCG F-8/1, IMCB F-8/4, ICB G-6/3)
        // ------------------------------------------------------------------
        $cambridgeEmis = [915, 905, 908,913]; // EMIS codes for Cambridge schools
        foreach ($cambridgeEmis as $emis) {
            Institution::where('code', (string) $emis)->update(['is_cambridge' => true]);
        }

        // ------------------------------------------------------------------
        // 6. Verify total count
        // ------------------------------------------------------------------
        $total = Institution::count();
        $this->command->info("Total institutions in database: {$total}");

        if ($total !== 432) {
            $this->command->error("WARNING: Expected 432 schools but found {$total}!");
        } else {
            $this->command->info("Success: Exactly 432 schools are seeded.");
        }
    }
}
