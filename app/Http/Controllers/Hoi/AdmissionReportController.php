<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\AcademicYear;
use Carbon\Carbon;

class AdmissionReportController extends Controller
{
    public function index(Request $request)
    {
        $user         = Auth::user();
        $institution  = $user->institution;

        if (!$institution) {
            return redirect()->route('hoi.profile.setup');
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        // Date range — default: full academic year (admission_start to today)
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : ($academicYear?->admission_start
                ? Carbon::parse($academicYear->admission_start)->startOfDay()
                : now()->startOfYear());

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        // ── Day-by-day breakdown ──────────────────────────
        $dailyRows = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->with('classModel')
            ->orderBy('admission_date')
            ->orderBy('class_id')
            ->get();

        // ── Class-wise cumulative summary ─────────────────
        $classSummary = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                class_id,
                SUM(boys_count)                                                             as reg_boys,
                SUM(girls_count)                                                            as reg_girls,
                SUM(oosc_boys)                                                              as oosc_boys,
                SUM(oosc_girls)                                                             as oosc_girls,
                SUM(p2p_boys)                                                               as p2p_boys,
                SUM(p2p_girls)                                                              as p2p_girls,
                SUM(boys_count + girls_count + oosc_boys + oosc_girls + p2p_boys + p2p_girls) as total
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // ── Institution classes with seat info ────────────
        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        // ── Grand totals ──────────────────────────────────
        $grandRegular  = $classSummary->sum(fn($r) => $r->reg_boys + $r->reg_girls);
        $grandOosc     = $classSummary->sum(fn($r) => $r->oosc_boys + $r->oosc_girls);
        $grandP2p      = $classSummary->sum(fn($r) => $r->p2p_boys + $r->p2p_girls);
        $grandTotal    = $classSummary->sum('total');

        return view('hoi.admissions.report', compact(
            'institution', 'academicYear',
            'dailyRows', 'classSummary', 'classes',
            'grandRegular', 'grandOosc', 'grandP2p', 'grandTotal',
            'from', 'to'
        ));
    }
}
