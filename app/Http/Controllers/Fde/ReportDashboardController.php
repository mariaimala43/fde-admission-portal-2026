<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Institution;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\Classes;
use App\Models\Sector;
use App\Models\UnionCouncil;
use App\Models\AcademicYear;
use Carbon\Carbon;
use App\Models\NewConstructionRoom;

/**
 * DUAL-ROLE REPORT CONTROLLER
 *
 * FDE Cell → all data system-wide (sectorIds = null)
 * AEO      → own assigned sectors only (sectorIds = [id, ...])
 * Director → all data system-wide, read-only (sectorIds = null)
 *
 * All three roles share the same blade views (fde.reports.*).
 * Sector scoping is applied transparently via sectorIds() helper.
 */
class ReportDashboardController extends Controller
{
    use AuthorizesRequests;

    // ─────────────────────────────────────────────────────────────────
    //  SCOPE HELPERS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Returns sector IDs for AEO, null for FDE/Director (no restriction).
     */
    private function sectorIds(): ?array
    {
        $user = Auth::user();

        if ($user->hasRole('aeo')) {
            $ids = $user->sectors()->pluck('sectors.id')->toArray();
            return !empty($ids) ? $ids : [-1]; // -1 ensures AEO with no sectors sees nothing
        }

        return null; // FDE + Director: no restriction
    }

    /**
     * Returns the route prefix for export links based on the current user's role.
     * Ensures Directors and AEOs are not sent to FDE-only export routes.
     */
    private function exportRoutePrefix(): string
    {
        $user = Auth::user();
        if ($user->hasRole('director')) return 'director';
        if ($user->hasRole('aeo'))      return 'aeo';
        return 'fde';
    }

    /**
     * Translates sector IDs into institution IDs for DailyAdmission scoping.
     * Returns null when there is no restriction (FDE/Director).
     */
    private function scopeInstIds(?array $sectorIds): ?array
    {
        if ($sectorIds === null) {
            return null;
        }

        $ids = Institution::whereIn('sector_id', $sectorIds)->pluck('id')->toArray();
        return !empty($ids) ? $ids : [-1]; // -1 ensures scoped user sees nothing if no schools
    }

    // ─────────────────────────────────────────────────────────────────
    //  MAIN DASHBOARD WITH ALL CHARTS
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $this->authorize('reports.dashboard');

        $sectorIds   = $this->sectorIds();
        $instIds     = $this->scopeInstIds($sectorIds);
        $academicYear = AcademicYear::where('is_active', true)->first();
        $today        = now()->toDateString();

        // Limit sectors shown to AEO's assigned ones; FDE/Director see all
        $sectors = $sectorIds !== null
            ? Sector::with('institutions')->whereIn('id', $sectorIds)->get()
            : Sector::with('institutions')->get();

        // ── Grand totals ──────────────────────────────────────────────
        $grandTotals = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->when($instIds, fn($q) => $q->whereIn('institution_id', $instIds))
            ->selectRaw('
                SUM(morning_boys+evening_boys)                    as total_reg_boys,
                SUM(morning_girls+evening_girls)                  as total_reg_girls,
                SUM(oosc_boys)                                    as total_oosc_boys,
                SUM(oosc_girls)                                   as total_oosc_girls,
                SUM(p2p_boys)                                     as total_p2p_boys,
                SUM(p2p_girls)                                    as total_p2p_girls,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls)             as total_regular,
                SUM(oosc_boys+oosc_girls)                         as total_oosc,
                SUM(p2p_boys+p2p_girls)                           as total_p2p,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as grand_total,
                SUM(morning_boys+evening_boys+oosc_boys+p2p_boys) as all_boys,
                SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as all_girls
            ')
            ->first();

        // ── Today totals ──────────────────────────────────────────────
        $todayTotals = DailyAdmission::where('admission_date', $today)
            ->when($instIds, fn($q) => $q->whereIn('institution_id', $instIds))
            ->selectRaw('
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as boys,
                SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as girls
            ')
            ->first();

        // ── Seat summary ──────────────────────────────────────────────
        $seatSummary = InstitutionClass::when($instIds, fn($q) => $q->whereIn('institution_id', $instIds))
            ->selectRaw('
                SUM(total_seats)         as total_seats,
                SUM(existing_enrollment) as total_existing
            ')
            ->first();

        $totalAdmitted  = $grandTotals->grand_total ?? 0;
        $totalSeats     = $seatSummary->total_seats ?? 0;
        $totalExisting  = $seatSummary->total_existing ?? 0;
        $totalFilled    = $totalExisting + $totalAdmitted;
        $totalRemaining = max(0, $totalSeats - $totalFilled);

        // ── Chart 1: Daily admissions trend (last 30 days) ────────────
        $dailyTrend = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->when($instIds, fn($q) => $q->whereIn('institution_id', $instIds))
            ->where('admission_date', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('
                admission_date,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as boys,
                SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as girls
            ')
            ->groupBy('admission_date')
            ->orderBy('admission_date')
            ->get()
            ->keyBy(fn($r) => $r->admission_date->toDateString());

        // Fill missing dates with 0
        $trendLabels = [];
        $trendTotal  = [];
        $trendBoys   = [];
        $trendGirls  = [];
        for ($i = 29; $i >= 0; $i--) {
            $date          = now()->subDays($i)->toDateString();
            $row           = $dailyTrend[$date] ?? null;
            $trendLabels[] = now()->subDays($i)->format('d M');
            $trendTotal[]  = (int) ($row?->total ?? 0);
            $trendBoys[]   = (int) ($row?->boys  ?? 0);
            $trendGirls[]  = (int) ($row?->girls ?? 0);
        }

        // ── Chart 2: Sector comparison ────────────────────────────────
        $sectorStats = $sectors->map(function ($sector) use ($academicYear) {
            $ids = $sector->institutions()->pluck('id');

            $adm = DailyAdmission::whereIn('institution_id', $ids)
                ->where('academic_year_id', $academicYear?->id)
                ->selectRaw('
                    SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                    SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as boys,
                    SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as girls,
                    SUM(oosc_boys+oosc_girls)                             as oosc,
                    SUM(p2p_boys+p2p_girls)                               as p2p
                ')
                ->first();

            $seats = InstitutionClass::whereIn('institution_id', $ids)
                ->selectRaw('SUM(total_seats) as seats, SUM(existing_enrollment) as existing')
                ->first();

            $sector->adm_total   = (int) ($adm?->total   ?? 0);
            $sector->adm_boys    = (int) ($adm?->boys    ?? 0);
            $sector->adm_girls   = (int) ($adm?->girls   ?? 0);
            $sector->adm_oosc    = (int) ($adm?->oosc    ?? 0);
            $sector->adm_p2p     = (int) ($adm?->p2p     ?? 0);
            $sector->total_seats = (int) ($seats?->seats    ?? 0);
            $sector->fill_rate   = $sector->total_seats > 0
                ? round((((int)($seats?->existing ?? 0) + $sector->adm_total) / $sector->total_seats) * 100)
                : 0;

            return $sector;
        });

        // ── Chart 3: Gender breakdown (pie) ───────────────────────────
        $genderData = [
            'reg_boys'   => (int) ($grandTotals->total_reg_boys   ?? 0),
            'reg_girls'  => (int) ($grandTotals->total_reg_girls  ?? 0),
            'oosc_boys'  => (int) ($grandTotals->total_oosc_boys  ?? 0),
            'oosc_girls' => (int) ($grandTotals->total_oosc_girls ?? 0),
            'p2p_boys'   => (int) ($grandTotals->total_p2p_boys   ?? 0),
            'p2p_girls'  => (int) ($grandTotals->total_p2p_girls  ?? 0),
        ];

        // ── Chart 4: Category breakdown (bar) ─────────────────────────
        $categoryData = [
            'regular' => (int) ($grandTotals->total_regular ?? 0),
            'oosc'    => (int) ($grandTotals->total_oosc    ?? 0),
            'p2p'     => (int) ($grandTotals->total_p2p     ?? 0),
        ];

        // ── Chart 5: School fill rates (progress) ─────────────────────
        $schoolFillRates = Institution::where('is_active', true)
            ->where('classes_configured', true)
            ->when($sectorIds, fn($q) => $q->whereIn('sector_id', $sectorIds))
            ->with('sector')
            ->get()
            ->map(function ($inst) use ($academicYear) {
                $seat = InstitutionClass::where('institution_id', $inst->id)
                    ->selectRaw('SUM(total_seats) as seats, SUM(existing_enrollment) as existing')
                    ->first();

                $admitted = DailyAdmission::where('institution_id', $inst->id)
                    ->where('academic_year_id', $academicYear?->id)
                    ->selectRaw('SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total')
                    ->value('total') ?? 0;

                $seats     = (int) ($seat?->seats    ?? 0);
                $existing  = (int) ($seat?->existing ?? 0);
                $filled    = $existing + (int) $admitted;
                $remaining = max(0, $seats - $filled);
                $fillRate  = $seats > 0 ? round(($filled / $seats) * 100) : 0;

                $inst->seats     = $seats;
                $inst->existing  = $existing;
                $inst->admitted  = (int) $admitted;
                $inst->filled    = $filled;
                $inst->remaining = $remaining;
                $inst->fill_rate = $fillRate;

                return $inst;
            })
            ->sortByDesc('fill_rate');

        // ── Weekly trend (last 8 weeks) ───────────────────────────────
        $weeklyTrend = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->when($instIds, fn($q) => $q->whereIn('institution_id', $instIds))
            ->where('admission_date', '>=', now()->subWeeks(7)->startOfWeek())
            ->selectRaw('
                YEARWEEK(admission_date, 1) as week_key,
                MIN(admission_date)          as week_start,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total
            ')
            ->groupBy('week_key')
            ->orderBy('week_key')
            ->get();

        $weekLabels = $weeklyTrend->map(fn($r) => Carbon::parse($r->week_start)->format('d M'))->toArray();
        $weekTotals = $weeklyTrend->pluck('total')->map(fn($v) => (int) $v)->toArray();

        // ── OOSC tracking by sector ────────────────────────────────────
        $ooscBySector = $sectorStats->map(fn($s) => [
            'name' => $s->name,
            'oosc' => $s->adm_oosc,
            'p2p'  => $s->adm_p2p,
        ])->values();

        // ── Submission status ──────────────────────────────────────────
        $totalConfigured   = Institution::where('is_active', true)
            ->where('classes_configured', true)
            ->when($sectorIds, fn($q) => $q->whereIn('sector_id', $sectorIds))
            ->count();
        $submittedToday    = DailyAdmission::where('admission_date', $today)
            ->when($instIds, fn($q) => $q->whereIn('institution_id', $instIds))
            ->distinct('institution_id')->count('institution_id');
        $notSubmittedToday = $totalConfigured - $submittedToday;

        // IDs of institutions that HAVE submitted today
        $submittedTodayIds = DailyAdmission::where('admission_date', $today)
            ->when($instIds, fn($q) => $q->whereIn('institution_id', $instIds))
            ->distinct()->pluck('institution_id');

        // Institutions that have NOT submitted today
        $notSubmittedSchools = Institution::where('is_active', true)
            ->where('classes_configured', true)
            ->when($sectorIds, fn($q) => $q->whereIn('sector_id', $sectorIds))
            ->whereNotIn('id', $submittedTodayIds)
            ->with('sector')
            ->orderBy('sector_id')
            ->orderBy('name')
            ->get();

        // ── College stats (Model + Ex-FG) for report cards ────────────
        $modelCollegeIds = Institution::where('type', 'Model College')->pluck('id');
        $exFgCollegeIds  = Institution::where('type', 'Ex-FG College')->pluck('id');

        $modelCollegeStats = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereIn('institution_id', $modelCollegeIds)
            ->selectRaw('
                SUM(morning_boys + evening_boys + oosc_boys + p2p_boys)     as total_boys,
                SUM(morning_girls + evening_girls + oosc_girls + p2p_girls) as total_girls,
                SUM(morning_boys + evening_boys + morning_girls + evening_girls
                    + oosc_boys + oosc_girls + p2p_boys + p2p_girls)        as total_admitted
            ')
            ->first();

        $exFgCollegeStats = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereIn('institution_id', $exFgCollegeIds)
            ->selectRaw('
                SUM(morning_boys + evening_boys + oosc_boys + p2p_boys)     as total_boys,
                SUM(morning_girls + evening_girls + oosc_girls + p2p_girls) as total_girls,
                SUM(morning_boys + evening_boys + morning_girls + evening_girls
                    + oosc_boys + oosc_girls + p2p_boys + p2p_girls)        as total_admitted
            ')
            ->first();

        $modelCollegeCount = $modelCollegeIds->count();
        $exFgCollegeCount  = $exFgCollegeIds->count();
            // ── New Construction Rooms summary ────────────────────────────────
        $newRooms = (object) [
            'total_schools'   => NewConstructionRoom::count(),
            'total_rooms'     => NewConstructionRoom::sum('rooms_total'),
            'rooms_allocated' => NewConstructionRoom::sum('rooms_allocated'),
            'rooms_remaining' => NewConstructionRoom::sum('rooms_total') - NewConstructionRoom::sum('rooms_allocated'),
            'completed'       => NewConstructionRoom::where('construction_status', 'completed')->count(),
            'near_completion' => NewConstructionRoom::where('construction_status', 'near_completion')->count(),
            'total_seats'     => NewConstructionRoom::sum('rooms_total') * 40,
        ];

        return view('fde.reports.dashboard', compact(
            'academicYear', 'grandTotals', 'todayTotals',
            'totalSeats', 'totalExisting', 'totalFilled', 'totalRemaining', 'totalAdmitted',
            'trendLabels', 'trendTotal', 'trendBoys', 'trendGirls',
            'weekLabels', 'weekTotals',
            'sectorStats', 'genderData', 'categoryData',
            'schoolFillRates', 'ooscBySector',
            'totalConfigured', 'submittedToday', 'notSubmittedToday',
            'notSubmittedSchools',
            'today',
            'modelCollegeStats', 'exFgCollegeStats',
            'modelCollegeCount', 'exFgCollegeCount',
             'newRooms'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SECTOR / UC WISE SUMMARY
    // ─────────────────────────────────────────────────────────────────
    public function sectorReport(Request $request)
    {
        $this->authorize('reports.sector');

        $sectorIds    = $this->sectorIds();
        $instIds      = $this->scopeInstIds($sectorIds);
        $academicYear = AcademicYear::where('is_active', true)->first();

        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to   = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        // Only sectors in scope
        $sectors = $sectorIds !== null
            ? Sector::with(['institutions.institutionClasses'])->whereIn('id', $sectorIds)->get()
            : Sector::with(['institutions.institutionClasses'])->get();

        $sectorReport = $sectors->map(function ($sector) use ($academicYear, $from, $to) {
            $instIds = $sector->institutions->pluck('id');

            // UC-wise breakdown within sector
            $ucBreakdown = UnionCouncil::where('sector_id', $sector->id)
                ->with('institutions')
                ->get()
                ->map(function ($uc) use ($academicYear, $from, $to) {
                    $ucInstIds = $uc->institutions->pluck('id');

                    $adm = DailyAdmission::whereIn('institution_id', $ucInstIds)
                        ->where('academic_year_id', $academicYear?->id)
                        ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
                        ->selectRaw('
                            SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                            SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as boys,
                            SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as girls,
                            SUM(oosc_boys+oosc_girls)                             as oosc,
                            SUM(p2p_boys+p2p_girls)                               as p2p
                        ')
                        ->first();

                    $seats = InstitutionClass::whereIn('institution_id', $ucInstIds)
                        ->selectRaw('SUM(total_seats) as seats, SUM(existing_enrollment) as existing')
                        ->first();

                    $uc->total_admitted = (int) ($adm?->total   ?? 0);
                    $uc->total_boys     = (int) ($adm?->boys    ?? 0);
                    $uc->total_girls    = (int) ($adm?->girls   ?? 0);
                    $uc->total_oosc     = (int) ($adm?->oosc    ?? 0);
                    $uc->total_p2p      = (int) ($adm?->p2p     ?? 0);
                    $uc->total_seats    = (int) ($seats?->seats    ?? 0);
                    $uc->total_existing = (int) ($seats?->existing ?? 0);
                    $uc->school_count   = $uc->institutions->count();
                    $uc->fill_rate      = $uc->total_seats > 0
                        ? round((($uc->total_existing + $uc->total_admitted) / $uc->total_seats) * 100)
                        : 0;

                    return $uc;
                });

            $adm = DailyAdmission::whereIn('institution_id', $instIds)
                ->where('academic_year_id', $academicYear?->id)
                ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                    SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as boys,
                    SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as girls,
                    SUM(oosc_boys+oosc_girls)                             as oosc,
                    SUM(p2p_boys+p2p_girls)                               as p2p
                ')
                ->first();

            $seats = InstitutionClass::whereIn('institution_id', $instIds)
                ->selectRaw('SUM(total_seats) as seats, SUM(existing_enrollment) as existing')
                ->first();

            return [
                'sector'         => $sector,
                'uc_breakdown'   => $ucBreakdown,
                'total_admitted' => (int) ($adm?->total   ?? 0),
                'total_boys'     => (int) ($adm?->boys    ?? 0),
                'total_girls'    => (int) ($adm?->girls   ?? 0),
                'total_oosc'     => (int) ($adm?->oosc    ?? 0),
                'total_p2p'      => (int) ($adm?->p2p     ?? 0),
                'total_seats'    => (int) ($seats?->seats    ?? 0),
                'total_existing' => (int) ($seats?->existing ?? 0),
                'school_count'   => $sector->institutions->count(),
            ];
        });

        $exportPrefix = $this->exportRoutePrefix();
        return view('fde.reports.sector', compact(
            'sectorReport', 'from', 'to', 'academicYear', 'exportPrefix'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  INSTITUTION VACANCY REPORT
    // ─────────────────────────────────────────────────────────────────
    public function vacancyReport(Request $request)
    {
        $this->authorize('reports.vacancy');

        $sectorIds    = $this->sectorIds();
        $academicYear = AcademicYear::where('is_active', true)->first();

        // Build sectors list scoped to user
        $sectors = $sectorIds !== null
            ? Sector::whereIn('id', $sectorIds)->orderBy('name')->get()
            : Sector::orderBy('name')->get();

        // Prevent AEO bypassing their scope via sector_id query param
        $sectorId = $request->input('sector_id');
        if ($sectorIds !== null && $sectorId && ! in_array((int) $sectorId, $sectorIds)) {
            $sectorId = null;
        }

        $type   = $request->input('type');
        $gender = $request->input('gender');

        $institutions = Institution::with(['sector', 'unionCouncil'])
            ->where('is_active', true)
            ->where('classes_configured', true)
            ->when($sectorIds, fn($q) => $q->whereIn('sector_id', $sectorIds)) // AEO scope
            ->when($sectorId, fn($q) => $q->where('sector_id', $sectorId))     // user filter
            ->when($type,     fn($q) => $q->where('type', $type))
            ->when($gender,   fn($q) => $q->where('gender', $gender))
            ->orderBy('sector_id')
            ->orderBy('name')
            ->get();

        $seatData = InstitutionClass::whereIn('institution_id', $institutions->pluck('id'))
            ->where('is_active', true)
            ->with('classModel')
            ->get()
            ->groupBy('institution_id');

        $admData = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereIn('institution_id', $institutions->pluck('id'))
            ->selectRaw('
                institution_id, class_id,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as boys,
                SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as girls
            ')
            ->groupBy('institution_id', 'class_id')
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        $exportPrefix = $this->exportRoutePrefix();
        return view('fde.reports.vacancy', compact(
            'institutions', 'seatData', 'admData',
            'sectors', 'sectorId', 'type', 'gender', 'academicYear', 'exportPrefix'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  GENDER ANALYTICS REPORT
    // ─────────────────────────────────────────────────────────────────
    public function genderReport(Request $request)
    {
        $this->authorize('reports.gender');

        $sectorIds    = $this->sectorIds();
        $instIds      = $this->scopeInstIds($sectorIds);
        $academicYear = AcademicYear::where('is_active', true)->first();

        $sectors = $sectorIds !== null
            ? Sector::whereIn('id', $sectorIds)->orderBy('name')->get()
            : Sector::orderBy('name')->get();

        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to   = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        // Overall gender totals (scoped)
        $overall = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->when($instIds, fn($q) => $q->whereIn('institution_id', $instIds))
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                SUM(morning_boys+evening_boys)   as reg_boys,   SUM(morning_girls+evening_girls)   as reg_girls,
                SUM(oosc_boys)                   as oosc_boys,  SUM(oosc_girls)                    as oosc_girls,
                SUM(p2p_boys)                    as p2p_boys,   SUM(p2p_girls)                     as p2p_girls,
                SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as total_boys,
                SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as total_girls
            ')
            ->first();

        // Per sector gender breakdown (scoped sectors only)
        $bySector = $sectors->map(function ($sector) use ($academicYear, $from, $to) {
            $ids = $sector->institutions()->pluck('id');
            $adm = DailyAdmission::whereIn('institution_id', $ids)
                ->where('academic_year_id', $academicYear?->id)
                ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as boys,
                    SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as girls,
                    SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total
                ')
                ->first();

            return [
                'name'  => $sector->name,
                'boys'  => (int) ($adm?->boys  ?? 0),
                'girls' => (int) ($adm?->girls ?? 0),
                'total' => (int) ($adm?->total ?? 0),
            ];
        });

        // Per class gender breakdown
        $byClass = Classes::orderBy('is_ece')->orderBy('order')
            ->get()
            ->map(function ($class) use ($academicYear, $from, $to, $instIds) {
                $adm = DailyAdmission::where('class_id', $class->id)
                    ->where('academic_year_id', $academicYear?->id)
                    ->when($instIds, fn($q) => $q->whereIn('institution_id', $instIds))
                    ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
                    ->selectRaw('
                        SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as boys,
                        SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as girls
                    ')
                    ->first();

                return [
                    'name'  => $class->name,
                    'boys'  => (int) ($adm?->boys  ?? 0),
                    'girls' => (int) ($adm?->girls ?? 0),
                ];
            })->filter(fn($r) => $r['boys'] + $r['girls'] > 0);

        return view('fde.reports.gender', compact(
            'overall', 'bySector', 'byClass', 'from', 'to', 'academicYear'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  OOSC TRACKING REPORT
    // ─────────────────────────────────────────────────────────────────
    public function ooscReport(Request $request)
    {
        $this->authorize('reports.oosc');

        $sectorIds    = $this->sectorIds();
        $academicYear = AcademicYear::where('is_active', true)->first();

        $sectors = $sectorIds !== null
            ? Sector::whereIn('id', $sectorIds)->orderBy('name')->get()
            : Sector::orderBy('name')->get();

        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to   = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        // Prevent AEO bypassing sector scope
        $sectorId = $request->input('sector_id');
        if ($sectorIds !== null && $sectorId && ! in_array((int) $sectorId, $sectorIds)) {
            $sectorId = null;
        }

        $institutions = Institution::with(['sector', 'unionCouncil'])
            ->where('is_active', true)
            ->where('classes_configured', true)
            ->when($sectorIds, fn($q) => $q->whereIn('sector_id', $sectorIds)) // AEO scope
            ->when($sectorId, fn($q) => $q->where('sector_id', $sectorId))     // user filter
            ->orderBy('sector_id')
            ->orderBy('name')
            ->get();

        $ooscData = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('institution_id', $institutions->pluck('id'))
            ->selectRaw('
                institution_id,
                SUM(oosc_boys)                 as oosc_boys,
                SUM(oosc_girls)                as oosc_girls,
                SUM(oosc_boys+oosc_girls)      as oosc_total,
                SUM(p2p_boys)                  as p2p_boys,
                SUM(p2p_girls)                 as p2p_girls,
                SUM(p2p_boys+p2p_girls)        as p2p_total,
                SUM(morning_boys+evening_boys)  as reg_boys,
                SUM(morning_girls+evening_girls) as reg_girls
            ')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        $grandOosc = $ooscData->sum('oosc_total');
        $grandP2p  = $ooscData->sum('p2p_total');

        // Sector-wise OOSC summary (scoped sectors only)
        $sectorOosc = $sectors->map(function ($sector) use ($academicYear, $from, $to) {
            $ids = $sector->institutions()->pluck('id');
            $row = DailyAdmission::whereIn('institution_id', $ids)
                ->where('academic_year_id', $academicYear?->id)
                ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    SUM(oosc_boys)            as oosc_boys,
                    SUM(oosc_girls)           as oosc_girls,
                    SUM(oosc_boys+oosc_girls) as oosc_total,
                    SUM(p2p_boys)             as p2p_boys,
                    SUM(p2p_girls)            as p2p_girls,
                    SUM(p2p_boys+p2p_girls)   as p2p_total
                ')
                ->first();

            return [
                'name'       => $sector->name,
                'oosc_boys'  => (int) ($row?->oosc_boys  ?? 0),
                'oosc_girls' => (int) ($row?->oosc_girls ?? 0),
                'oosc_total' => (int) ($row?->oosc_total ?? 0),
                'p2p_boys'   => (int) ($row?->p2p_boys   ?? 0),
                'p2p_girls'  => (int) ($row?->p2p_girls  ?? 0),
                'p2p_total'  => (int) ($row?->p2p_total  ?? 0),
            ];
        });

        $exportPrefix = $this->exportRoutePrefix();
        return view('fde.reports.oosc', compact(
            'institutions', 'ooscData', 'sectorOosc',
            'grandOosc', 'grandP2p',
            'sectors', 'sectorId', 'from', 'to', 'academicYear', 'exportPrefix'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  AI REPORT STUDIO  (FDE Cell only)
    // ─────────────────────────────────────────────────────────────────
    public function aiStudio()
    {
        $this->authorize('reports.ai-studio');

        return view('fde.reports.ai_studio');
    }
}
