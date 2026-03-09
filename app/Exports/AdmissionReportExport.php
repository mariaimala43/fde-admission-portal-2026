<?php
// ════════════════════════════════════════════════════════════
//  app/Exports/AdmissionReportExport.php
// ════════════════════════════════════════════════════════════

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdmissionReportExport implements WithMultipleSheets
{
    public function __construct(protected array $data) {}

    public function sheets(): array
    {
        return [
            new Sheets\OverallSummarySheet($this->data),
            new Sheets\SchoolWiseSheet($this->data),
        ];
    }
}
