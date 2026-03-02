<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\DailyAdmission;
use App\Models\Classes;
use App\Models\Sector;
use App\Models\AcademicYear;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        $sectors      = Sector::orderBy('name')->get();
        $classes      = Classes::where('is_ece', false)->orderBy('order')->get();
        $academicYear = AcademicYear::where('is_active', true)->first();

        // ── Hero stats ─────────────────────────────────────
        $totalInstitutions = Institution::where('is_active', true)->count();

        $openInstitutions  = Institution::where('is_active', true)
            ->where('admission_status', 'open')
            ->where('classes_configured', true)
            ->count();

        $totalSeatsAvailable = InstitutionClass::whereHas('institution', fn($q) =>
                $q->where('is_active', true)->where('admission_status', 'open')
            )
            ->selectRaw('
                SUM(total_seats) - SUM(existing_enrollment) as available
            ')
            ->value('available') ?? 0;

        $totalAdmittedThisYear = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->selectRaw('SUM(boys_count+girls_count+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total')
            ->value('total') ?? 0;

        // ── Filtered school query ──────────────────────────
        $query = Institution::with(['sector', 'institutionClasses.classModel'])
            ->where('is_active', true)
            ->where('classes_configured', true)
            ->where('admission_status', 'open');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->boolean('has_transport')) {
            $query->where('has_transport', true);
        }

        if ($request->boolean('has_meal_program')) {
            $query->where('has_meal_program', true);
        }

        if ($request->boolean('has_matric_tech')) {
            $query->where('has_matric_tech', true);
        }

        if ($request->boolean('has_evening_classes')) {
            $query->where('has_evening_classes', true);
        }

        if ($request->boolean('is_cambridge')) {
            $query->where('is_cambridge', true);
        }

        if ($request->boolean('has_ece')) {
            $query->where('has_ece', true);
        }

        $institutions = $query->orderBy('name')->get();

        // ── Admission totals per institution ───────────────
        $admissionTotals = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereIn('institution_id', $institutions->pluck('id'))
            ->selectRaw('
                institution_id,
                SUM(boys_count+girls_count+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total_admitted
            ')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        // ── Seat data per institution ──────────────────────
        $seatData = InstitutionClass::whereIn('institution_id', $institutions->pluck('id'))
            ->where('is_active', true)
            ->with('classModel')
            ->get()
            ->groupBy('institution_id');

        // ── Filter by class availability if requested ──────
        if ($request->filled('class_id')) {
            $classId = $request->class_id;
            $institutions = $institutions->filter(function ($inst) use ($seatData, $admissionTotals, $classId) {
                $classSeat = ($seatData[$inst->id] ?? collect())
                    ->firstWhere('class_id', $classId);
                if (!$classSeat) return false;
                $admitted  = $admissionTotals[$inst->id]?->total_admitted ?? 0;
                $available = $classSeat->total_seats - $classSeat->existing_enrollment - $admitted;
                return $available > 0;
            });
        }

        return view('portal.index', compact(
            'institutions', 'sectors', 'classes',
            'seatData', 'admissionTotals', 'academicYear',
            'totalInstitutions', 'openInstitutions',
            'totalSeatsAvailable', 'totalAdmittedThisYear'
        ));
    }

    public function show(Institution $institution)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $institution->load(['sector']);

        $seatData = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        $admissionTotal = DailyAdmission::where('institution_id', $institution->id)
            ->where('academic_year_id', $academicYear?->id)
            ->selectRaw('
                class_id,
                SUM(boys_count+girls_count+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total_admitted
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        return view('portal.show', compact(
            'institution', 'seatData', 'admissionTotal', 'academicYear'
        ));
    }
}
