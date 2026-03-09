<?php
// app/Exports/OoscReportExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OoscReportExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(protected array $data) {}

    public function title(): string { return 'OOSC & P2P Report'; }

    public function columnWidths(): array
    {
        return [
            'A' => 38, 'B' => 16, 'C' => 12, 'D' => 12,
            'E' => 12, 'F' => 12, 'G' => 12, 'H' => 12,
        ];
    }

    public function array(): array
    {
        $from = $this->data['from']->format('d M Y');
        $to   = $this->data['to']->format('d M Y');

        $rows = [
            ['FDE OOSC & PRIVATE-TO-PUBLIC TRACKING REPORT', '', '', '', '', '', '', ''],
            ["Period: {$from} to {$to}", '', '', '', '', '', '', ''],
            [''],
            ['School', 'Sector', 'OOSC Boys', 'OOSC Girls', 'OOSC Total', 'P2P Boys', 'P2P Girls', 'P2P Total'],
        ];

        foreach ($this->data['institutions'] as $inst) {
            $d      = $this->data['ooscData'][$inst->id] ?? null;
            $rows[] = [
                $inst->name,
                $inst->sector?->name ?? '',
                (int)($d?->oosc_boys  ?? 0),
                (int)($d?->oosc_girls ?? 0),
                (int)($d?->oosc_total ?? 0),
                (int)($d?->p2p_boys   ?? 0),
                (int)($d?->p2p_girls  ?? 0),
                (int)($d?->p2p_total  ?? 0),
            ];
        }

        $rows[] = [
            'GRAND TOTAL', '',
            $this->data['ooscData']->sum('oosc_boys'),
            $this->data['ooscData']->sum('oosc_girls'),
            $this->data['ooscData']->sum('oosc_total'),
            $this->data['ooscData']->sum('p2p_boys'),
            $this->data['ooscData']->sum('p2p_girls'),
            $this->data['ooscData']->sum('p2p_total'),
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $last = count($this->data['institutions']) + 5;

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
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
