<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\StaffPostType;
use App\Models\StaffStrengthRegister;
use App\Models\StaffStrengthEntry;
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

        if ($request->filled('type')) {
            $query->whereHas('institution', fn($q) =>
                $q->where('type', $request->type)
            );
        }

        $registers = $query->orderBy('updated_at', 'desc')->paginate(30);

        $institutionTypes = Institution::distinct()->pluck('type')->sort()->values();

        return view('fde.staff-strength.index', compact(
            'registers',
            'academicYear',
            'sectors',
            'institutionTypes',
        ));
    }

    public function show(StaffStrengthRegister $staffStrength)
    {
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

        return view('fde.staff-strength.show', compact(
            'register',
            'teachingEntries',
            'programEntries',
        ));
    }

    public function edit(StaffStrengthRegister $staffStrength)
    {
        $this->authorize('update', $staffStrength);

        $register = $staffStrength->load(['institution', 'academicYear', 'entries.postType']);

        $teachingTypes = StaffPostType::forLevel($register->institution->type)
            ->where('section', 'teaching')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $programTypes = StaffPostType::where('section', 'program')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $entries = $register->entries->keyBy('post_type_id');

        return view('fde.staff-strength.edit', compact(
            'register',
            'teachingTypes',
            'programTypes',
            'entries',
        ));
    }

    public function update(Request $request, StaffStrengthRegister $staffStrength)
    {
        $this->authorize('update', $staffStrength);

        foreach ($request->input('entries', []) as $postTypeId => $data) {
            StaffStrengthEntry::updateOrCreate(
                [
                    'register_id'  => $staffStrength->id,
                    'post_type_id' => (int) $postTypeId,
                ],
                [
                    'sanctioned_posts'  => (int) ($data['sanctioned_posts']  ?? 0),
                    'filled_posts'      => (int) ($data['filled_posts']      ?? 0),
                    'sacked_employees'  => (int) ($data['sacked_employees']  ?? 0),
                    'daily_wagers_in'   => (int) ($data['daily_wagers_in']   ?? 0),
                    'daily_wagers_out'  => (int) ($data['daily_wagers_out']  ?? 0),
                    'study_leave'       => (int) ($data['study_leave']       ?? 0),
                    'deputationist_in'  => (int) ($data['deputationist_in']  ?? 0),
                    'deputationist_out' => (int) ($data['deputationist_out'] ?? 0),
                    'temporary_in'      => (int) ($data['temporary_in']      ?? 0),
                    'temporary_out'     => (int) ($data['temporary_out']     ?? 0),
                    'number_of_posts'   => (int) ($data['number_of_posts']   ?? 0),
                ]
            );
        }

        $staffStrength->update([
            'fde_remarks' => $request->input('fde_remarks'),
        ]);

        return redirect()->route('fde.staff-strength.show', $staffStrength)
            ->with('success', 'Staff strength register updated.');
    }

    public function lock(StaffStrengthRegister $staffStrength)
    {
        $this->authorize('lock', $staffStrength);

        $staffStrength->update([
            'status'    => 'locked',
            'locked_by' => Auth::id(),
            'locked_at' => now(),
        ]);

        return back()->with('success', 'Register locked.');
    }

    public function unlock(StaffStrengthRegister $staffStrength)
    {
        $this->authorize('lock', $staffStrength);

        $staffStrength->update([
            'status'    => 'submitted',
            'locked_by' => null,
            'locked_at' => null,
        ]);

        return back()->with('success', 'Register unlocked.');
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
