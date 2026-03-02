<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Institution;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\Sector;
use App\Models\AcademicYear;
use Carbon\Carbon;

class SchoolsReportController extends Controller
{
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectors      = Sector::orderBy('name')->get();

        // ── Filters ────────────────────────────────────────
        $sectorId   = $request->input('sector_id');
        $type       = $request->input('type');
        $gender     = $request->input('gender');

        // ── Schools query ──────────────────────────────────
        $query = Institution::with('sector')
            ->where('is_active', true)
            ->when($sectorId, fn($q) => $q->where('sector_id', $sectorId))
            ->when($type,     fn($q) => $q->where('type', $type))
            ->when($gender,   fn($q) => $q->where('gender', $gender))
            ->orderBy('sector_id')
            ->orderBy('name');

        $institutions = $query->get();

        // ── Admission summary per school ───────────────────
        $admissionSummary = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereIn('institution_id', $institutions->pluck('id'))
            ->selectRaw('
                institution_id,
                SUM(boys_count + girls_count + oosc_boys + oosc_girls + p2p_boys + p2p_girls) as total,
                SUM(boys_count + girls_count)   as regular,
                SUM(oosc_boys + oosc_girls)     as oosc,
                SUM(p2p_boys + p2p_girls)       as p2p
            ')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        // ── Seat summary per school ────────────────────────
        $seatSummary = InstitutionClass::whereIn('institution_id', $institutions->pluck('id'))
            ->selectRaw('institution_id, SUM(total_seats) as seats, SUM(existing_enrollment) as enrolled')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        return view('fde.schools.index', compact(
            'institutions', 'sectors', 'admissionSummary',
            'seatSummary', 'sectorId', 'type', 'gender', 'academicYear'
        ));
    }

    public function show(Institution $institution, Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $institution->load('sector');

        // Day-by-day rows
        $dailyRows = DailyAdmission::where('institution_id', $institution->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->with('classModel')
            ->orderBy('admission_date')
            ->orderBy('class_id')
            ->get();

        // Class summary
        $classSummary = DailyAdmission::where('institution_id', $institution->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                class_id,
                SUM(boys_count)   as reg_boys,  SUM(girls_count)  as reg_girls,
                SUM(oosc_boys)    as oosc_boys,  SUM(oosc_girls)   as oosc_girls,
                SUM(p2p_boys)     as p2p_boys,   SUM(p2p_girls)    as p2p_girls,
                SUM(boys_count + girls_count + oosc_boys + oosc_girls + p2p_boys + p2p_girls) as total
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        $grandRegular = $classSummary->sum(fn($r) => $r->reg_boys + $r->reg_girls);
        $grandOosc    = $classSummary->sum(fn($r) => $r->oosc_boys + $r->oosc_girls);
        $grandP2p     = $classSummary->sum(fn($r) => $r->p2p_boys + $r->p2p_girls);
        $grandTotal   = $classSummary->sum('total');

        return view('fde.schools.show', compact(
            'institution', 'dailyRows', 'classSummary', 'classes',
            'grandRegular', 'grandOosc', 'grandP2p', 'grandTotal',
            'from', 'to', 'academicYear'
        ));
    }
}
