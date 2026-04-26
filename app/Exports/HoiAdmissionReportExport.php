<?php
// app/Exports/HoiAdmissionReportExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\InstitutionSection;

class HoiAdmissionReportExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(protected array $data) {}

    public function title(): string
    {
        return 'Admission Report';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, 'B' => 7,  'C' => 18, 'D' => 16,
            'E' => 12, 'F' => 11, 'G' => 11, 'H' => 11,
            'I' => 11, 'J' => 11, 'K' => 11, 'L' => 12,
        ];
    }

    public function array(): array
    {
        $inst = $this->data['institution'];
        $year = $this->data['academicYear']?->name ?? '';
        $from = $this->data['from']->format('d M Y');
        $to   = $this->data['to']->format('d M Y');

        $rows = [
            ["ADMISSION REPORT — {$inst->name}", '', '', '', '', '', '', '', '', '', '', ''],
            ["Academic Year: {$year}   |   Period: {$from} – {$to}", '', '', '', '', '', '', '', '', '', '', ''],
            [''],
            // Summary row
            [
                "Total Admitted: {$this->data['grandTotal']}",
                '', '',
                "Regular: {$this->data['grandRegular']}",
                '', '',
                "OOSC: {$this->data['grandOosc']}",
                '', '',
                "P2G: {$this->data['grandP2p']}",
                '', '',
            ],
            [''],
            // Column headers
            [
                'Class', 'Sec', 'Existing Students', 'Newly Admitted', 'Total Seats',
                'Regular Boys', 'Regular Girls',
                'OOSC Boys', 'OOSC Girls',
                'P2G Boys', 'P2G Girls',
                'Total',
            ],
        ];

        foreach ($this->data['classes'] as $ic) {
            $s        = $this->data['classSummary'][$ic->class_id] ?? null;
            $admitted = $s ? (int) $s->grand_total : 0;
            $secCount = InstitutionSection::where('institution_id', $ic->institution_id)
                            ->where('class_id', $ic->class_id)->count() ?: 1;

            $rows[] = [
                $ic->classModel?->name,
                $secCount,
                $ic->existing_enrollment,
                $admitted,
                $ic->total_seats,
                ($s?->morning_boys  ?? 0) + ($s?->evening_boys  ?? 0),
                ($s?->morning_girls ?? 0) + ($s?->evening_girls ?? 0),
                ($s?->morning_oosc_boys  ?? 0) + ($s?->evening_oosc_boys  ?? 0),
                ($s?->morning_oosc_girls ?? 0) + ($s?->evening_oosc_girls ?? 0),
                ($s?->morning_p2p_boys  ?? 0) + ($s?->evening_p2p_boys  ?? 0),
                ($s?->morning_p2p_girls ?? 0) + ($s?->evening_p2p_girls ?? 0),
                $admitted,
            ];
        }

        // Grand total row
        $cs = $this->data['classSummary'];
        $rows[] = [
            'GRAND TOTAL',
            '',
            $this->data['classes']->sum('existing_enrollment'),
            $this->data['grandTotal'],
            $this->data['classes']->sum('total_seats'),
            $cs->sum('morning_boys')      + $cs->sum('evening_boys'),
            $cs->sum('morning_girls')     + $cs->sum('evening_girls'),
            $cs->sum('morning_oosc_boys') + $cs->sum('evening_oosc_boys'),
            $cs->sum('morning_oosc_girls')+ $cs->sum('evening_oosc_girls'),
            $cs->sum('morning_p2p_boys')  + $cs->sum('evening_p2p_boys'),
            $cs->sum('morning_p2p_girls') + $cs->sum('evening_p2p_girls'),
            $this->data['grandTotal'],
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->array());

        return [
            // Title row
            1 => [
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A1628']],
            ],
            // Date row
            2 => [
                'font' => ['italic' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            ],
            // Summary row
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '1E3A5F']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            ],
            // Column header row
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Grand total row
            $lastRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A1628']],
            ],
        ];
    }
}
