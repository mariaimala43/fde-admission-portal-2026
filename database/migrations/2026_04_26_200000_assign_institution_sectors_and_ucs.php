<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Assigns correct sector_id and uc_id to all institutions based on the
 * authoritative school data file (csv_20260426_404642.txt).
 *
 * Also fixes union_councils.sector_id for incorrectly mapped UCs.
 *
 * Data: 432 school records mapped by EMIS code → (sector_id, uc_number).
 * UC numbers are integers; the migration resolves them to uc_id via the
 * union_councils table (matching on "UC-XX" prefix in the name column).
 */
return new class extends Migration
{
    // Sector IDs (from sectors table)
    private const SECTOR = [
        'Urban-I'  => 2,
        'Urban-II' => 3,
        'B-K'      => 4,   // also Bharakau
        'Tarnol'   => 5,
        'Sihala'   => 6,
        'Nilore'   => 7,   // also NILORE
    ];

    public function up(): void
    {
        // Build UC number → UC id lookup from DB names (e.g. "UC-08 Tumair" → 8 → id=8)
        $ucNumberToId = [];
        foreach (DB::table('union_councils')->get(['id', 'name']) as $uc) {
            if (preg_match('/^UC-(\d+)/i', $uc->name, $m)) {
                $ucNumberToId[(int) $m[1]] = $uc->id;
            }
        }

        $updated  = 0;
        $skipped  = [];

        // Model college EMIS codes — must always be in MODEL COLLEGES sector (id=1).
        // Force-correct their sector_id FIRST, in case the live DB has them wrong.
        $modelCollegeEmis = [
            '801','802','803','804','805','806','807','808','809','810','811','812','813',
            '901','902','903','904','905','906','907','908','909','910','911','912',
            '913','914','915','916','917','918','919','920','921','922','923','924','925','926',
        ];

        DB::table('institutions')
            ->whereIn('code', $modelCollegeEmis)
            ->update(['sector_id' => 1, 'updated_at' => now()]);

        // EMIS 812 was incorrectly typed as 'XI-XII' — must be 'Model College'
        DB::table('institutions')
            ->where('code', '812')
            ->update(['type' => 'Model College', 'updated_at' => now()]);

        // Build a flip-map for O(1) lookup during the main loop
        $modelCollegeCodes = array_flip(array_map('intval', $modelCollegeEmis));

        foreach ($this->getData() as [$emis, $sectorId, $ucNumber]) {
            $isModelCollege = isset($modelCollegeCodes[$emis]);

            // For model colleges: only update uc_id (keep sector_id=1)
            // For all others: update both sector_id and uc_id
            $updateData = ['updated_at' => now()];

            if (!$isModelCollege) {
                $updateData['sector_id'] = $sectorId;
            }

            if ($ucNumber !== null && isset($ucNumberToId[$ucNumber])) {
                $updateData['uc_id'] = $ucNumberToId[$ucNumber];
            }

            $rows = DB::table('institutions')
                ->where('code', (string) $emis)
                ->update($updateData);

            if ($rows > 0) {
                $updated++;
            } else {
                $skipped[] = $emis;
            }
        }

        // Fix UC → sector assignments
        $this->fixUcSectors($ucNumberToId);

        echo "  Updated {$updated} institutions." . PHP_EOL;
        if ($skipped) {
            echo '  EMIS codes not found in DB: ' . implode(', ', $skipped) . PHP_EOL;
        }
    }

    /**
     * Correct union_councils.sector_id based on authoritative data.
     * Only updates UCs whose sector differs from the authoritative mapping.
     */
    private function fixUcSectors(array $ucNumberToId): void
    {
        // UC number → correct sector_id (derived from school data analysis)
        $ucSectorFixes = [
            // B.K sector (4) — currently wrong for UC-08 to UC-11
             1 => 4,   2 => 4,   3 => 4,   4 => 4,   5 => 4,   6 => 4,   7 => 4,
             8 => 7,   9 => 7,  // UC-08, UC-09 → Nilore (were B.K)
            10 => 6,  11 => 6,  // UC-10, UC-11 → Sihala (were B.K)
            12 => 6,  13 => 6,  // UC-12, UC-13 → Sihala (were Nilore)
            14 => 6,  15 => 6,  16 => 6,
            17 => 6,  18 => 6,  // UC-17, UC-18 → Sihala (were Nilore)
            19 => 7,  20 => 7,  21 => 7,  // UC-19, UC-20 → Nilore (were Tarnol); UC-21 → Nilore (was Sihala)
            22 => 4,  23 => 4,  24 => 4,
            25 => 2,  26 => 2,  27 => 2,  28 => 2,
            29 => 3,  // UC-29 → Urban-II (was Urban-I)
            30 => 2,  31 => 2,  32 => 2,  33 => 2,
            34 => 3,  35 => 3,  36 => 3,  37 => 3,  38 => 3,
            39 => 5,  // UC-39 → Tarnol (was Urban-II)
            40 => 3,  41 => 3,  42 => 3,
            43 => 5,  44 => 5,  // UC-43, UC-44 → Tarnol (were Urban-II)
            45 => 5,  46 => 5,  47 => 5,  48 => 5,  49 => 5,  50 => 5,
        ];

        foreach ($ucSectorFixes as $ucNum => $sectorId) {
            $ucId = $ucNumberToId[$ucNum] ?? null;
            if (!$ucId) {
                continue;
            }
            DB::table('union_councils')
                ->where('id', $ucId)
                ->update(['sector_id' => $sectorId]);
        }
    }

    /**
     * Master data array: [emis_code, sector_id, uc_number|null]
     *
     * sector_id: see SECTOR constant above
     * uc_number: integer (1-50), or null if not assigned
     */
    private function getData(): array
    {
        return [
            // ── Model Colleges & Post-Graduate Colleges ───────────────────────
            // sector_id column is IGNORED for these (kept as MODEL COLLEGES=1).
            // Only the UC number is applied.
            [901, 1, 29], [902, 1, 29], [903, 1, 29],
            [904, 1, 28], [905, 1, 28],
            [906, 1, 36], [907, 1, 38],
            [908, 1, 27],
            [909, 1, 42], [910, 1, 40],
            [911, 1, 29],
            [912, 1, 25], [913, 1, 25],
            [914, 1, 28], [915, 1, 28],
            [916, 1, 37], [917, 1, 42], [918, 1, 40], [919, 1, 29],
            [920, 1, 15],
            [921, 1, 45], [922, 1, 39], [923, 1, 39], [924, 1, 45],
            [925, 1, 17],
            [926, 1,  7],
            [801, 1, 14],
            [802, 1, 40], [803, 1, 29], [804, 1, 41], [805, 1, 40],
            [806, 1, 28], [807, 1, 36],
            [808, 1, 13],
            [809, 1, 40], [810, 1, 28],
            [811, 1, 45],
            [812, 1,  4],
            [813, 1, 29],

            // ── B.K Sector ────────────────────────────────────────────────────
            // UC-01 Saidpur
            [408, 4,  1], [410, 4,  1], [411, 4,  1],
            [435, 4,  1], [452, 4,  1], [458, 4,  1],
            // UC-02 Noorpur Shahan
            [406, 4,  2], [418, 4,  2], [421, 4,  2], [430, 4,  2],
            [437, 4,  2], [447, 4,  2], [456, 4,  2],
            // UC-03 Malpur
            [439, 4,  3], [449, 4,  3], [457, 4,  3], [460, 4,  3],
            [462, 4,  3], [475, 4,  3], [478, 4,  3], [479, 4,  3],
            // UC-04 Kot Hathial (Shamal) Bharakahu
            [401, 4,  4], [403, 4,  4], [414, 4,  4], [417, 4,  4],
            [428, 4,  4], [436, 4,  4], [443, 4,  4], [464, 4,  4],
            [466, 4,  4], [471, 4,  4],
            // UC-05 Kot Hathial (Janoob)
            [423, 4,  5], [467, 4,  5], [470, 4,  5], [477, 4,  5],
            // UC-06 Phulgran
            [404, 4,  6], [412, 4,  6], [416, 4,  6], [419, 4,  6],
            [425, 4,  6], [426, 4,  6], [433, 4,  6], [450, 4,  6],
            [459, 4,  6], [465, 4,  6], [474, 4,  6],
            // UC-07 Pind Begwal
            [407, 4,  7], [413, 4,  7], [420, 4,  7], [429, 4,  7],
            [431, 4,  7], [432, 4,  7], [434, 4,  7], [453, 4,  7],
            [455, 4,  7], [461, 4,  7], [468, 4,  7], [473, 4,  7],
            // UC-22 Chak Shehzad
            [402, 4, 22], [441, 4, 22], [444, 4, 22], [445, 4, 22],
            [446, 4, 22], [469, 4, 22], [476, 4, 22],
            // UC-23 Kuri
            [405, 4, 23], [415, 4, 23], [424, 4, 23], [427, 4, 23],
            [438, 4, 23], [448, 4, 23], [454, 4, 23], [463, 4, 23],
            [472, 4, 23],
            // UC-24 Rawal Margala Town
            [409, 4, 24], [440, 4, 24], [442, 4, 24], [451, 4, 24],

            // ── Nilore Sector ─────────────────────────────────────────────────
            // UC-08 Tumair
            [703, 7,  8], [715, 7,  8], [721, 7,  8], [731, 7,  8],
            [732, 7,  8], [735, 7,  8], [750, 7,  8], [751, 7,  8],
            [752, 7,  8], [753, 7,  8], [758, 7,  8], [767, 7,  8],
            // UC-09 Charah
            [701, 7,  9], [702, 7,  9], [710, 7,  9], [711, 7,  9],
            [716, 7,  9], [719, 7,  9], [720, 7,  9], [724, 7,  9],
            [730, 7,  9], [738, 7,  9], [748, 7,  9], [749, 7,  9],
            [754, 7,  9], [755, 7,  9], [757, 7,  9], [759, 7,  9],
            [762, 7,  9], [764, 7,  9],
            // UC-10 Kirpa (shared; assigned Nilore per institution data)
            [709, 7, 10], [729, 7, 10], [736, 7, 10], [747, 7, 10], [766, 7, 10],
            // UC-17 Koral (one Nilore school)
            [727, 7, 17],
            // UC-18 Khana Dak (one Nilore school)
            [707, 7, 18],
            // UC-19 Tarlai Kalan
            [706, 7, 19], [713, 7, 19], [734, 7, 19], [741, 7, 19],
            [742, 7, 19], [743, 7, 19],
            // UC-20 Alipur
            [705, 7, 20], [712, 7, 20], [718, 7, 20], [728, 7, 20],
            [733, 7, 20], [744, 7, 20], [745, 7, 20], [756, 7, 20],
            [760, 7, 20], [763, 7, 20], [765, 7, 20],
            // UC-21 Sohan
            [717, 7, 21], [723, 7, 21], [726, 7, 21], [737, 7, 21],
            [740, 7, 21], [761, 7, 21],
            // UC-22 Chak Shehzad (Nilore schools)
            [708, 7, 22], [725, 7, 22],
            // UC-23 Kuri (Nilore schools)
            [704, 7, 23], [714, 7, 23],
            // UC-07 Pind Begwal (one Nilore school crosses boundary)
            [746, 7,  7],

            // ── Sihala Sector ─────────────────────────────────────────────────
            // UC-10 Kirpa (Sihala schools)
            [507, 6, 10], [513, 6, 10], [522, 6, 10], [524, 6, 10],
            [532, 6, 10], [533, 6, 10], [544, 6, 10], [553, 6, 10],
            [561, 6, 10], [572, 6, 10],
            // UC-11 Mughal
            [501, 6, 11], [505, 6, 11], [512, 6, 11], [523, 6, 11],
            [527, 6, 11], [528, 6, 11], [537, 6, 11], [541, 6, 11],
            [557, 6, 11], [558, 6, 11], [570, 6, 11], [571, 6, 11],
            // UC-12 Rawat
            [502, 6, 12], [514, 6, 12], [515, 6, 12], [525, 6, 12],
            [526, 6, 12], [543, 6, 12], [550, 6, 12], [565, 6, 12],
            [566, 6, 12], [567, 6, 12], [568, 6, 12],
            // UC-13 Humak
            [503, 6, 13], [509, 6, 13], [510, 6, 13], [516, 6, 13],
            [517, 6, 13], [519, 6, 13], [520, 6, 13], [534, 6, 13],
            [545, 6, 13], [547, 6, 13], [554, 6, 13], [555, 6, 13],
            [562, 6, 13], [563, 6, 13], [564, 6, 13], [575, 6, 13],
            // UC-14 Sihala
            [504, 6, 14], [511, 6, 14], [529, 6, 14], [530, 6, 14],
            [531, 6, 14], [535, 6, 14], [538, 6, 14], [540, 6, 14],
            [542, 6, 14], [548, 6, 14], [551, 6, 14], [556, 6, 14],
            // UC-15 Lohi Bhar
            [508, 6, 15], [539, 6, 15], [546, 6, 15], [552, 6, 15],
            [560, 6, 15], [573, 6, 15], [574, 6, 15],
            // UC-16 Darwala
            [518, 6, 16], [521, 6, 16], [536, 6, 16], [549, 6, 16], [559, 6, 16],
            // UC-17 Koral (Sihala school)
            [569, 6, 17],
            // UC-18 Khana Dak (Sihala schools)
            [506, 6, 18], [722, 6, 18], [739, 6, 18],

            // ── Tarnol Sector ─────────────────────────────────────────────────
            // UC-39 Maira Sumbal Jaffar
            [606, 5, 39], [618, 5, 39], [626, 5, 39], [627, 5, 39], [643, 5, 39],
            // UC-43 Sector I-11 & H-11
            [422, 5, 43],
            // UC-44 Bokra
            [645, 5, 44],
            // UC-45 Jhangi Saidan
            [604, 5, 45], [611, 5, 45], [616, 5, 45], [622, 5, 45],
            [646, 5, 45], [652, 5, 45], [654, 5, 45],
            // UC-46 Badhana Kalan
            [603, 5, 46], [605, 5, 46], [609, 5, 46], [613, 5, 46],
            [617, 5, 46], [625, 5, 46], [634, 5, 46], [635, 5, 46],
            [636, 5, 46], [638, 5, 46], [650, 5, 46], [651, 5, 46],
            // UC-47 Tarnol
            [601, 5, 47], [614, 5, 47], [630, 5, 47], [633, 5, 47],
            [640, 5, 47], [642, 5, 47], [649, 5, 47],
            // UC-48 Sarai Kharbuza
            [602, 5, 48], [610, 5, 48], [619, 5, 48], [620, 5, 48],
            [621, 5, 48], [623, 5, 48], [628, 5, 48], [629, 5, 48],
            [631, 5, 48], [632, 5, 48], [641, 5, 48], [653, 5, 48],
            [655, 5, 48],
            // UC-49 Shah Allah Ditta
            [607, 5, 49], [612, 5, 49], [637, 5, 49], [639, 5, 49], [644, 5, 49],
            // UC-50 Golra Sharif
            [608, 5, 50], [615, 5, 50], [647, 5, 50],
            // UC-29 (one Tarnol school: IMSG Bekha Syedan)
            [648, 5, 29],

            // ── Urban-I Sector ────────────────────────────────────────────────
            // UC-25 Sector F-6
            [211, 2, 25], [224, 2, 25], [226, 2, 25], [227, 2, 25],
            [228, 2, 25], [229, 2, 25], [232, 2, 25], [306, 2, 25],
            // UC-26 Sector G-6/1
            [201, 2, 26], [202, 2, 26], [203, 2, 26], [204, 2, 26],
            [205, 2, 26], [212, 2, 26],
            // UC-27 Sector G-6/2
            [206, 2, 27], [207, 2, 27], [208, 2, 27], [209, 2, 27],
            [210, 2, 27], [304, 2, 27], [305, 2, 27],
            // UC-28 F-7 F-8 F-9
            [221, 2, 28], [223, 2, 28], [225, 2, 28], [230, 2, 28],
            [231, 2, 28], [235, 2, 28], [236, 2, 28], [237, 2, 28],
            [249, 2, 28], [300, 2, 28], [301, 2, 28], [317, 2, 28],
            // UC-30 Sector G-7/3 G-7/4
            [215, 2, 30], [217, 2, 30], [218, 2, 30], [219, 2, 30],
            [220, 2, 30], [303, 2, 30], [308, 2, 30], [316, 2, 30],
            // UC-31 Sector G-7/1 G-7/2
            [213, 2, 31], [214, 2, 31], [216, 2, 31], [222, 2, 31],
            [295, 2, 31], [307, 2, 31],
            // UC-32 Sector G-8/3 G-8/4
            [238, 2, 32], [240, 2, 32], [241, 2, 32], [242, 2, 32], [315, 2, 32],
            // UC-33 Sector G-8/1 G-8/2
            [239, 2, 33], [243, 2, 33], [244, 2, 33], [245, 2, 33],
            [246, 2, 33], [247, 2, 33], [248, 2, 33], [297, 2, 33],
            // UC-40 (two Urban-I schools in E-8 area)
            [233, 2, 40], [234, 2, 40],

            // ── Urban-II Sector ───────────────────────────────────────────────
            // UC-29 Sector F-10 F-11
            [264, 3, 29], [265, 3, 29], [266, 3, 29], [276, 3, 29],
            // UC-34 Sector G-9
            [250, 3, 34], [251, 3, 34], [252, 3, 34], [253, 3, 34],
            [254, 3, 34], [255, 3, 34], [256, 3, 34], [263, 3, 34],
            [296, 3, 34], [299, 3, 34],
            // UC-35 Sector G-9/2
            [257, 3, 35], [258, 3, 35], [259, 3, 35], [260, 3, 35],
            [261, 3, 35], [262, 3, 35],
            // UC-36 Sector G-10/3 G-10/4
            [268, 3, 36], [269, 3, 36], [270, 3, 36], [298, 3, 36],
            // UC-37 Sector G-10/1 G-10/2
            [267, 3, 37], [271, 3, 37], [272, 3, 37], [273, 3, 37],
            // UC-38 Sector G-11
            [274, 3, 38], [275, 3, 38], [277, 3, 38], [278, 3, 38], [302, 3, 38],
            // UC-40 I-8 & H8 (Urban-II schools)
            [279, 3, 40], [280, 3, 40], [284, 3, 40], [285, 3, 40],
            [313, 3, 40], [314, 3, 40],
            // UC-41 I-9 & H9
            [281, 3, 41], [282, 3, 41], [283, 3, 41], [286, 3, 41],
            [292, 3, 41], [293, 3, 41], [294, 3, 41], [309, 3, 41],
            // UC-42 Sector I-10 & H-10
            [287, 3, 42], [288, 3, 42], [289, 3, 42], [290, 3, 42],
            [291, 3, 42], [310, 3, 42],
            // No UC assigned (empty in source)
            [311, 3, null],
            [312, 3, null],
        ];
    }

    public function down(): void
    {
        // Bulk data correction — no safe rollback.
        // To revert, restore from a DB backup or re-run the original import.
    }
};
