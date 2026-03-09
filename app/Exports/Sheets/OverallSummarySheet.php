<?php
// app/Exports/Sheets/OverallSummarySheet.php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OverallSummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(protected array $data) {}

    public function title(): string { return 'Overall Class Summary'; }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 12, 'C' => 14, 'D' => 14,
            'E' => 14, 'F' => 12, 'G' => 12, 'H' => 14,
            'I' => 14, 'J' => 14,
        ];
    }

    public function array(): array
    {
        $from = $this->data['from']->format('d M Y');
        $to   = $this->data['to']->format('d M Y');
        $year = $this->data['academicYear']?->name ?? '';

        $rows = [
            ['FDE MASTER ADMISSION REPORT', '', '', '', '', '', '', '', '', ''],
            ["Academic Year: {$year}", '', '', "Period: {$from} to {$to}", '', '', '', '', '', ''],
            [''],
            ['Class', 'Schools', 'Total Seats', 'Existing', 'Regular', 'OOSC', 'P2P', 'Total Admitted', 'Total Filled', 'Remaining'],
        ];

        foreach ($this->data['overallByClass'] as $row) {
            $rows[] = [
                $row['class']->name,
                $row['school_count'],
                $row['total_seats'],
                $row['total_existing'],
                $row['total_regular'],
                $row['total_oosc'],
                $row['total_p2p'],
                $row['total_admitted'],
                $row['total_filled'],
                $row['total_remaining'],
            ];
        }

        $g      = $this->data['grand'];
        $rows[] = ['GRAND TOTAL', '', $g['seats'], $g['existing'], $g['regular'], $g['oosc'], $g['p2p'], $g['admitted'], $g['filled'], $g['remaining']];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $last = count($this->data['overallByClass']) + 5;

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A1628']],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            ],
            $last => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
            ],
        ];
    }
}
