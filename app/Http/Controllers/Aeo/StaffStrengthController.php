<?php

namespace App\Http\Controllers\Aeo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\StaffStrengthRegister;

class StaffStrengthController extends Controller
{
    private function resolveSector()
    {
        return Auth::user()->sectors()->first();
    }

    public function index(Request $request)
    {
        $sector = $this->resolveSector();

        if (! $sector) {
            return back()->with('error', 'No sector assigned to your account.');
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        $query = StaffStrengthRegister::with(['institution.sector', 'academicYear'])
            ->whereHas('institution', fn($q) => $q->where('sector_id', $sector->id))
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $registers = $query->orderBy('updated_at', 'desc')->paginate(30);

        return view('shared.staff-strength.index', [
            'registers'    => $registers,
            'academicYear' => $academicYear,
            'showRoute'    => 'aeo.staff-strength.show',
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
            'register'       => $register,
            'teachingEntries'=> $teachingEntries,
            'programEntries' => $programEntries,
            'indexRoute'     => 'aeo.staff-strength.index',
        ]);
    }
}
