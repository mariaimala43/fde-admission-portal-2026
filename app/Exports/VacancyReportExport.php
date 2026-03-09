<?php
// app/Exports/VacancyReportExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VacancyReportExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(protected array $data) {}

    public function title(): string { return 'Vacancy Position'; }

    public function columnWidths(): array
    {
        return [
            'A' => 38, 'B' => 16, 'C' => 10, 'D' => 12,
            'E' => 12, 'F' => 12, 'G' => 12, 'H' => 12, 'I' => 12,
        ];
    }

    public function array(): array
    {
        $year = $this->data['academicYear']?->name ?? '';

        $rows = [
            ['FDE INSTITUTION VACANCY POSITION REPORT', '', '', '', '', '', '', '', ''],
            ["Academic Year: {$year}", '', '', '', '', '', '', '', ''],
            [''],
            ['School', 'Sector', 'Type', 'Gender', 'Total Seats', 'Existing', 'Admitted', 'Filled', 'Remaining'],
        ];

        foreach ($this->data['institutions'] as $inst) {
            $seats     = $this->data['seatData'][$inst->id] ?? collect();
            $totalS    = $seats->sum('total_seats');
            $totalE    = $seats->sum('existing_enrollment');
            $admitted  = (int)($this->data['admData'][$inst->id]?->total ?? 0);
            $filled    = $totalE + $admitted;
            $remaining = max(0, $totalS - $filled);

            $rows[] = [
                $inst->name,
                $inst->sector?->name ?? '',
                $inst->type,
                ucfirst(str_replace('_', ' ', $inst->gender)),
                $totalS,
                $totalE,
                $admitted,
                $filled,
                $remaining,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A1628']],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            ],
        ];
    }
}
