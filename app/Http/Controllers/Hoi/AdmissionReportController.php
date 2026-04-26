<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\AcademicYear;
use Carbon\Carbon;
use App\Models\InstitutionSection;
use App\Exports\HoiAdmissionReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AdmissionReportController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    public function index(Request $request)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        if (!$institution) {
            return redirect()->route('hoi.profile.setup');
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        // Date range
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : ($academicYear?->admission_start && Carbon::parse($academicYear->admission_start)->lte(now())
                ? Carbon::parse($academicYear->admission_start)->startOfDay()
                : now()->startOfYear());

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        // Swap silently if from > to (prevents empty results from bad default dates)
        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        $hasEvening = in_array($institution->shift, ['evening', 'both']);

        // ── Class-wise cumulative summary ─────────────────────────────
        $classSummary = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                class_id,

                -- Regular admissions
                SUM(morning_boys)                                       AS morning_boys,
                SUM(morning_girls)                                      AS morning_girls,
                SUM(evening_boys)                                       AS evening_boys,
                SUM(evening_girls)                                      AS evening_girls,
                SUM(morning_boys  + morning_girls)                      AS morning_regular,
                SUM(evening_boys  + evening_girls)                      AS evening_regular,
                SUM(morning_boys  + morning_girls
                  + evening_boys  + evening_girls)                      AS regular_total,

                -- OOSC — shift split
                SUM(morning_oosc_boys)                                  AS morning_oosc_boys,
                SUM(morning_oosc_girls)                                 AS morning_oosc_girls,
                SUM(evening_oosc_boys)                                  AS evening_oosc_boys,
                SUM(evening_oosc_girls)                                 AS evening_oosc_girls,
                SUM(morning_oosc_boys  + morning_oosc_girls)            AS morning_oosc,
                SUM(evening_oosc_boys  + evening_oosc_girls)            AS evening_oosc,
                SUM(morning_oosc_boys  + morning_oosc_girls
                  + evening_oosc_boys  + evening_oosc_girls)            AS oosc_total,

                -- P2P — shift split
                SUM(morning_p2p_boys)                                   AS morning_p2p_boys,
                SUM(morning_p2p_girls)                                  AS morning_p2p_girls,
                SUM(evening_p2p_boys)                                   AS evening_p2p_boys,
                SUM(evening_p2p_girls)                                  AS evening_p2p_girls,
                SUM(morning_p2p_boys  + morning_p2p_girls)              AS morning_p2p,
                SUM(evening_p2p_boys  + evening_p2p_girls)              AS evening_p2p,
                SUM(morning_p2p_boys  + morning_p2p_girls
                  + evening_p2p_boys  + evening_p2p_girls)              AS p2p_total,

                -- Grand total (all categories consume seats)
                SUM(morning_boys      + morning_girls
                  + evening_boys      + evening_girls
                  + morning_oosc_boys + morning_oosc_girls
                  + evening_oosc_boys + evening_oosc_girls
                  + morning_p2p_boys  + morning_p2p_girls
                  + evening_p2p_boys  + evening_p2p_girls)              AS grand_total
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // ── Institution classes ───────────────────────────────────────
        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        // ── Day-by-day breakdown ──────────────────────────────────────
        $dailyRows = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->with('classModel')
            ->orderBy('admission_date')
            ->orderBy('class_id')
            ->get();

        // ── Grand totals ──────────────────────────────────────────────
        $grandMorningRegular = $classSummary->sum('morning_regular');
        $grandEveningRegular = $classSummary->sum('evening_regular');
        $grandRegular        = $classSummary->sum('regular_total');

        $grandMorningOosc    = $classSummary->sum('morning_oosc');
        $grandEveningOosc    = $classSummary->sum('evening_oosc');
        $grandOosc           = $classSummary->sum('oosc_total');

        $grandMorningP2p     = $classSummary->sum('morning_p2p');
        $grandEveningP2p     = $classSummary->sum('evening_p2p');
        $grandP2p            = $classSummary->sum('p2p_total');

        $grandTotal          = $classSummary->sum('grand_total');

        // Kept for backward compat with any existing views
        $grandMorning = $grandMorningRegular;
        $grandEvening = $grandEveningRegular;

        return view('hoi.admissions.report', compact(
            'institution', 'academicYear',
            'dailyRows', 'classSummary', 'classes',
            'grandMorning', 'grandEvening', 'grandRegular',
            'grandMorningRegular', 'grandEveningRegular',
            'grandMorningOosc', 'grandEveningOosc', 'grandOosc',
            'grandMorningP2p', 'grandEveningP2p', 'grandP2p',
            'grandTotal',
            'from', 'to', 'hasEvening'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHARED DATA BUILDER (used by index + exports)
    // ─────────────────────────────────────────────────────────────────
    private function buildReportData(Request $request): array
    {
        $user        = Auth::user();
        $institution = $user->institution;
        $academicYear = AcademicYear::where('is_active', true)->first();

        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : ($academicYear?->admission_start && Carbon::parse($academicYear->admission_start)->lte(now())
                ? Carbon::parse($academicYear->admission_start)->startOfDay()
                : now()->startOfYear());

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        $hasEvening = in_array($institution->shift, ['evening', 'both']);

        $classSummary = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                class_id,
                SUM(morning_boys)                                       AS morning_boys,
                SUM(morning_girls)                                      AS morning_girls,
                SUM(evening_boys)                                       AS evening_boys,
                SUM(evening_girls)                                      AS evening_girls,
                SUM(morning_boys  + morning_girls)                      AS morning_regular,
                SUM(evening_boys  + evening_girls)                      AS evening_regular,
                SUM(morning_boys  + morning_girls + evening_boys  + evening_girls) AS regular_total,
                SUM(morning_oosc_boys)                                  AS morning_oosc_boys,
                SUM(morning_oosc_girls)                                 AS morning_oosc_girls,
                SUM(evening_oosc_boys)                                  AS evening_oosc_boys,
                SUM(evening_oosc_girls)                                 AS evening_oosc_girls,
                SUM(morning_oosc_boys  + morning_oosc_girls)            AS morning_oosc,
                SUM(evening_oosc_boys  + evening_oosc_girls)            AS evening_oosc,
                SUM(morning_oosc_boys  + morning_oosc_girls + evening_oosc_boys + evening_oosc_girls) AS oosc_total,
                SUM(morning_p2p_boys)                                   AS morning_p2p_boys,
                SUM(morning_p2p_girls)                                  AS morning_p2p_girls,
                SUM(evening_p2p_boys)                                   AS evening_p2p_boys,
                SUM(evening_p2p_girls)                                  AS evening_p2p_girls,
                SUM(morning_p2p_boys  + morning_p2p_girls)              AS morning_p2p,
                SUM(evening_p2p_boys  + evening_p2p_girls)              AS evening_p2p,
                SUM(morning_p2p_boys  + morning_p2p_girls + evening_p2p_boys + evening_p2p_girls) AS p2p_total,
                SUM(morning_boys + morning_girls + evening_boys + evening_girls
                  + morning_oosc_boys + morning_oosc_girls + evening_oosc_boys + evening_oosc_girls
                  + morning_p2p_boys  + morning_p2p_girls  + evening_p2p_boys  + evening_p2p_girls) AS grand_total
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        $grandMorningRegular = $classSummary->sum('morning_regular');
        $grandEveningRegular = $classSummary->sum('evening_regular');
        $grandRegular        = $classSummary->sum('regular_total');
        $grandMorningOosc    = $classSummary->sum('morning_oosc');
        $grandEveningOosc    = $classSummary->sum('evening_oosc');
        $grandOosc           = $classSummary->sum('oosc_total');
        $grandMorningP2p     = $classSummary->sum('morning_p2p');
        $grandEveningP2p     = $classSummary->sum('evening_p2p');
        $grandP2p            = $classSummary->sum('p2p_total');
        $grandTotal          = $classSummary->sum('grand_total');

        return compact(
            'institution', 'academicYear', 'classSummary', 'classes',
            'grandMorningRegular', 'grandEveningRegular', 'grandRegular',
            'grandMorningOosc', 'grandEveningOosc', 'grandOosc',
            'grandMorningP2p', 'grandEveningP2p', 'grandP2p',
            'grandTotal', 'from', 'to', 'hasEvening'
        );
    }

    // ─────────────────────────────────────────────────────────────────
    //  EXCEL EXPORT
    // ─────────────────────────────────────────────────────────────────
    public function exportExcel(Request $request)
    {
        $data = $this->buildReportData($request);
        $filename = 'admission-report-' . $data['from']->format('Ymd') . '-' . $data['to']->format('Ymd') . '.xlsx';
        return Excel::download(new HoiAdmissionReportExport($data), $filename);
    }

    // ─────────────────────────────────────────────────────────────────
    //  PDF EXPORT
    // ─────────────────────────────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $data = $this->buildReportData($request);
        $filename = 'admission-report-' . $data['from']->format('Ymd') . '-' . $data['to']->format('Ymd') . '.pdf';
        $pdf = Pdf::loadView('hoi.admissions.report_pdf', $data)
                  ->setPaper('a4', 'landscape');
        return $pdf->download($filename);
    }

    // ─────────────────────────────────────────────────────────────────
    //  VACANCY POSITION REPORT  (institution-scoped)
    // ─────────────────────────────────────────────────────────────────
    public function vacancy()
    {
        $this->authorize('reports.vacancy');

        $user        = Auth::user();
        $institution = $user->institution;

        if (!$institution) {
            return redirect()->route('hoi.profile.setup');
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        // All active classes for this institution
        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        // Section names per class
        $sections = InstitutionSection::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->get()
            ->groupBy('class_id');

        // Year-to-date admitted per class (all verified/submitted daily records)
        $admitted = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->selectRaw('
                class_id,
                SUM(morning_boys + morning_girls + evening_boys + evening_girls
                  + morning_oosc_boys + morning_oosc_girls + evening_oosc_boys + evening_oosc_girls
                  + morning_p2p_boys  + morning_p2p_girls  + evening_p2p_boys  + evening_p2p_girls
                ) AS total_admitted
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // Build per-class vacancy rows
        $vacancyRows = $classes->map(function ($ic) use ($admitted, $sections) {
            $admit     = (int) ($admitted[$ic->class_id]?->total_admitted ?? 0);
            $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admit);
            $secs      = ($sections[$ic->class_id] ?? collect())->pluck('name')->join(', ') ?: '—';
            $secCount  = max(1, ($sections[$ic->class_id] ?? collect())->count());

            return (object) [
                'class_name'         => $ic->classModel?->name ?? '—',
                'is_ece'             => (bool) ($ic->classModel?->is_ece ?? false),
                'sections'           => $secCount,
                'section_names'      => $secs,
                'total_seats'        => $ic->total_seats,
                'existing_enrollment'=> $ic->existing_enrollment,
                'total_admitted'     => $admit,
                'available'          => $available,
                'is_full'            => $available === 0,
            ];
        });

        // Totals
        $totalSeats     = $vacancyRows->sum('total_seats');
        $totalExisting  = $vacancyRows->sum('existing_enrollment');
        $totalAdmitted  = $vacancyRows->sum('total_admitted');
        $totalAvailable = $vacancyRows->sum('available');
        $fullClasses    = $vacancyRows->where('is_full', true)->count();

        return view('hoi.reports.vacancy', compact(
            'institution', 'academicYear', 'vacancyRows',
            'totalSeats', 'totalExisting', 'totalAdmitted', 'totalAvailable', 'fullClasses'
        ));
    }
}
