<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Sector;
use App\Models\StaffStrengthRegister;
use App\Exports\StaffStrengthExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class StaffStrengthController extends Controller
{
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectors      = Sector::orderBy('name')->get();

        $query = StaffStrengthRegister::with(['institution.sector', 'academicYear'])
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id));

        if ($request->filled('sector_id')) {
            $query->whereHas('institution', fn($q) =>
                $q->where('sector_id', $request->sector_id)
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $registers = $query->orderBy('updated_at', 'desc')->paginate(30);

        return view('shared.staff-strength.index', [
            'registers'    => $registers,
            'academicYear' => $academicYear,
            'sectors'      => $sectors,
            'showRoute'    => 'director.staff-strength.show',
        ]);
    }

    public function show(StaffStrengthRegister $staffStrength)
    {
        $this->authorize('view', $staffStrength);

        $register = $staffStrength->load([
            'institution.sector',
            'academicYear',
            'submittedBy',
            'lockedBy',
            'entries.postType',
        ]);

        $teachingEntries = $register->entries
            ->filter(fn($e) => $e->postType->section === 'teaching')
            ->sortBy('postType.sort_order');

        $programEntries = $register->entries
            ->filter(fn($e) => $e->postType->section === 'program')
            ->sortBy('postType.sort_order');

        return view('shared.staff-strength.show', [
            'register'        => $register,
            'teachingEntries' => $teachingEntries,
            'programEntries'  => $programEntries,
            'indexRoute'      => 'director.staff-strength.index',
            'exportPdfRoute'  => 'director.staff-strength.export-pdf',
            'exportExcelRoute'=> 'director.staff-strength.export-excel',
        ]);
    }

    public function exportPdf(StaffStrengthRegister $staffStrength)
    {
        $this->authorize('export', $staffStrength);

        $register = $staffStrength->load([
            'institution.sector',
            'academicYear',
            'submittedBy',
            'lockedBy',
            'entries.postType',
        ]);

        $teachingEntries = $register->entries
            ->filter(fn($e) => $e->postType->section === 'teaching')
            ->sortBy('postType.sort_order');

        $programEntries = $register->entries
            ->filter(fn($e) => $e->postType->section === 'program')
            ->sortBy('postType.sort_order');

        $pdf = Pdf::loadView('fde.staff-strength.pdf', compact(
            'register',
            'teachingEntries',
            'programEntries',
        ))->setPaper('a3', 'landscape');

        $filename = 'staff-strength-' . $register->institution->code . '-' . $register->academicYear->name . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(StaffStrengthRegister $staffStrength)
    {
        $this->authorize('export', $staffStrength);

        $filename = 'staff-strength-' . $staffStrength->institution->code . '-' . $staffStrength->academicYear->name . '.xlsx';

        return Excel::download(new StaffStrengthExport($staffStrength), $filename);
    }
}
