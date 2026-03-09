<?php
// app/Exports/Sheets/SchoolWiseSheet.php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SchoolWiseSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected array $headerRows = [];

    public function __construct(protected array $data) {}

    public function title(): string { return 'School-wise Breakdown'; }

    public function columnWidths(): array
    {
        return [
            'A' => 38, 'B' => 16, 'C' => 10, 'D' => 10,
            'E' => 10, 'F' => 10, 'G' => 10, 'H' => 12,
            'I' => 12, 'J' => 12,
        ];
    }

    public function array(): array
    {
        $rows   = [['School', 'Sector', 'Class', 'Seats', 'Existing', 'Regular', 'OOSC', 'P2P', 'Admitted', 'Remaining']];
        $rowNum = 2;

        foreach ($this->data['institutions'] as $inst) {
            $instSeatData = $this->data['seatData'][$inst->id] ?? collect();
            $instAdmData  = $this->data['admissionData'][$inst->id] ?? collect();

            $instSeats    = $instSeatData->sum('total_seats');
            $instExisting = $instSeatData->sum('existing_enrollment');
            $instAdmitted = $instAdmData->sum('total_admitted');

            // School header row
            $rows[] = [
                $inst->name,
                $inst->sector?->name ?? '',
                'TOTAL',
                $instSeats,
                $instExisting,
                $instAdmData->sum(fn($r) => ($r->reg_boys  ?? 0) + ($r->reg_girls  ?? 0)),
                $instAdmData->sum(fn($r) => ($r->oosc_boys ?? 0) + ($r->oosc_girls ?? 0)),
                $instAdmData->sum(fn($r) => ($r->p2p_boys  ?? 0) + ($r->p2p_girls  ?? 0)),
                $instAdmitted,
                max(0, $instSeats - $instExisting - $instAdmitted),
            ];
            $this->headerRows[] = $rowNum++;

            // Class detail rows
            foreach ($instSeatData->sortBy('class_id') as $ic) {
                $adm       = $instAdmData[$ic->class_id] ?? null;
                $admitted  = $adm?->total_admitted ?? 0;
                $rows[]    = [
                    '  └ ' . ($ic->classModel?->name ?? ''),
                    '', '',
                    $ic->total_seats,
                    $ic->existing_enrollment,
                    ($adm?->reg_boys  ?? 0) + ($adm?->reg_girls  ?? 0),
                    ($adm?->oosc_boys ?? 0) + ($adm?->oosc_girls ?? 0),
                    ($adm?->p2p_boys  ?? 0) + ($adm?->p2p_girls  ?? 0),
                    $admitted,
                    max(0, $ic->total_seats - $ic->existing_enrollment - $admitted),
                ];
                $rowNum++;
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            ],
        ];

        foreach ($this->headerRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A1628']],
            ];
        }

        return $styles;
    }
}
