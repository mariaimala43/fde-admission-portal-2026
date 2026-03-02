<?php

namespace App\Imports;

use App\Models\Institution;
use App\Models\Sector;
use App\Models\UnionCouncil;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SchoolsImport implements
    ToCollection,
    WithHeadingRow,
    SkipsEmptyRows
{
    public array $errors   = [];
    public int   $imported = 0;
    public int   $skipped  = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // row 1 is header

            // Clean values
            $ucName      = trim($row['uc']          ?? '');
            $sectorName  = trim($row['sector']       ?? '');
            $schoolName  = trim($row['school_name']  ?? '');
            $type        = trim($row['type']         ?? '');
            $gender      = trim($row['gender']       ?? '');
            $shift       = strtolower(trim($row['shift'] ?? 'morning'));

            // Skip if essential fields are empty
            if (!$ucName || !$sectorName || !$schoolName) {
                $this->skipped++;
                continue;
            }

            // ── Find or Create UC ──────────────────────────
            $uc = UnionCouncil::firstOrCreate(
                ['name' => $ucName],
                [
                    'code'      => strtoupper(preg_replace('/\s+/', '-', $ucName)),
                    'is_active' => true,
                ]
            );

            // ── Find or Create Sector ──────────────────────
           $sectorCode = strtoupper(preg_replace('/\s+/', '-', $sectorName));

                $sector = Sector::firstOrCreate(
                    [
                        'uc_id' => $uc->id,
                        'code'  => $sectorCode,
                    ],
                    [
                        'name'      => $sectorName,
                        'uc_id'     => $uc->id,
                        'code'      => $sectorCode,
                        'is_active' => true,
                    ]
                );

            // ── Normalize Gender ───────────────────────────
            $genderMap = [
                'boys'          => 'boys',
                'boy'           => 'boys',
                'male'          => 'boys',
                'girls'         => 'girls',
                'girl'          => 'girls',
                'female'        => 'girls',
                'co-education'  => 'co_education',
                'co education'  => 'co_education',
                'coeducation'   => 'co_education',
                'co_education'  => 'co_education',
                'mixed'         => 'co_education',
            ];
            $genderNorm = $genderMap[strtolower($gender)] ?? 'boys';

            // ── Normalize Shift ────────────────────────────
            $shiftMap = [
                'morning'   => 'morning',
                'morning'   => 'morning',
                'm'         => 'morning',
                'evening'   => 'evening',
                'e'         => 'evening',
                'both'      => 'both',
                'double'    => 'both',
            ];
            $shiftNorm = $shiftMap[$shift] ?? 'morning';

            // ── Normalize Type ─────────────────────────────
            $validTypes = [
                'I-V', 'I-VIII', 'I-X', 'I-XII',
                'VI-VIII', 'VI-X', 'VI-XII', 'Model College'
            ];
            $typeNorm = in_array($type, $validTypes) ? $type : 'I-V';

            // ── Create Institution ─────────────────────────
            try {
                $institution = Institution::firstOrCreate(
                    ['name' => $schoolName],
                    [
                        'sector_id'          => $sector->id,
                        'uc_id'              => $uc->id,
                        'type'               => $typeNorm,
                        'gender'             => $genderNorm,
                        'shift'              => $shiftNorm,
                        'admission_status'   => 'not_started',
                        'has_matric_tech'    => false,
                        'has_transport'      => false,
                        'has_meal_program'   => false,
                        'has_evening_classes'=> false,
                        'is_active'          => true,
                    ]
                );

                // Set cambridge if eligible
                if ($institution->isCambridgeEligible()) {
                    \DB::table('institutions')
                        ->where('id', $institution->id)
                        ->update(['is_cambridge' => true]);
                }

                $this->imported++;

            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNum} ({$schoolName}): " . $e->getMessage();
                $this->skipped++;
            }
        }
    }
}
