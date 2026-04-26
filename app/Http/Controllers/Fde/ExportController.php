<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Institution;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\Classes;
use App\Models\Sector;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdmissionReportExport;
use App\Exports\VacancyReportExport;
use App\Exports\OoscReportExport;

/**
 * SAVE AS: app/Http/Controllers/Fde/ExportController.php
 *
 * ══════════════════════════════════════════════════════════════════════
 *  DUAL-ROLE EXPORT CONTROLLER (per RolesSeeder):
 *
 *  FDE Cell  →  exports all institutions (reports.export permission)
 *  AEO       →  exports their assigned sector only (reports.export permission)
 *                scoped automatically via aeo_sectors pivot table
 *
 *  The same three export methods serve both roles.
 *  $this->sectorIds() returns null (FDE) or [sector_id, ..] (AEO).
 * ══════════════════════════════════════════════════════════════════════
 */
class ExportController extends Controller
{
    use AuthorizesRequests;
    // ─────────────────────────────────────────────────────────────────
    //  SECTOR SCOPE HELPER  (mirrors ReportDashboardController)
    // ─────────────────────────────────────────────────────────────────
    private function sectorIds(): ?array
    {
        $user = Auth::user();

        if ($user->hasRole('aeo')) {
            // AEO sectors come from aeo_sectors pivot table
            $ids = $user->sectors()->pluck('sectors.id')->toArray();
            return !empty($ids) ? $ids : [-1];
        }

        return null; // FDE — no restriction
    }

    // ─────────────────────────────────────────────────────────────────
    //  MASTER REPORT EXPORT  (PDF + Excel)
    //  AEO: scoped to their sector; FDE: all institutions
    // ─────────────────────────────────────────────────────────────────
    public function masterReport(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $this->authorize('reports.export');

        // AEO cannot access master report (FDE-only in RolesSeeder)
        // masterReport is only registered under fde. routes in web.php
        // This authorize check is a safety net.
        abort_if(Auth::user()->hasRole('aeo'), 403, 'Access denied.');

        $academicYear = AcademicYear::where('is_active', true)->first();

        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $sectorId   = $request->input('sector_id');
        $type       = $request->input('type');
        $gender     = $request->input('gender');
        $classLevel = $request->input('class_level'); // 'all' | 'ece' | 'non_ece'
        $format     = $request->input('format', 'pdf');

        $institutions = Institution::with(['sector', 'unionCouncil'])
            ->where('is_active', true)
            ->where('classes_configured', true)
            ->when($sectorId, fn($q) => $q->where('sector_id', $sectorId))
            ->when($type,     fn($q) => $q->where('type', $type))
            ->when($gender,   fn($q) => $q->where('gender', $gender))
            ->orderBy('sector_id')->orderBy('name')
            ->get();

        $allClasses = Classes::orderBy('is_ece')->orderBy('order')
            ->when($classLevel === 'ece',     fn($q) => $q->where('is_ece', true))
            ->when($classLevel === 'non_ece', fn($q) => $q->where('is_ece', false))
            ->get();

        $seatData = InstitutionClass::whereIn('institution_id', $institutions->pluck('id'))
            ->where('is_active', true)
            ->with('classModel')
            ->get()
            ->groupBy('institution_id');

        $admissionData = DailyAdmission::whereIn('institution_id', $institutions->pluck('id'))
            ->where('academic_year_id', $academicYear?->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                institution_id, class_id,
                SUM(morning_boys+evening_boys)   as reg_boys,
                SUM(morning_girls+evening_girls)  as reg_girls,
                SUM(oosc_boys)  as oosc_boys, SUM(oosc_girls) as oosc_girls,
                SUM(p2p_boys)   as p2p_boys,  SUM(p2p_girls)  as p2p_girls,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total_admitted
            ')
            ->groupBy('institution_id', 'class_id')
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        // Build overallByClass summary
        $overallByClass = [];
        foreach ($allClasses as $class) {
            $totalSeats = $totalExisting = $totalRegular = $totalOosc = $totalP2p = $totalAdmitted = $schoolCount = 0;

            foreach ($institutions as $inst) {
                $seat = ($seatData[$inst->id] ?? collect())->firstWhere('class_id', $class->id);
                if (! $seat) {
                    continue;
                }
                $schoolCount++;
                $adm            = $admissionData[$inst->id][$class->id] ?? null;
                $totalSeats    += $seat->total_seats;
                $totalExisting += $seat->existing_enrollment;
                $totalRegular  += ($adm?->reg_boys  ?? 0) + ($adm?->reg_girls  ?? 0);
                $totalOosc     += ($adm?->oosc_boys ?? 0) + ($adm?->oosc_girls ?? 0);
                $totalP2p      += ($adm?->p2p_boys  ?? 0) + ($adm?->p2p_girls  ?? 0);
                $totalAdmitted += $adm?->total_admitted ?? 0;
            }

            if ($schoolCount === 0) {
                continue;
            }

            $overallByClass[$class->id] = [
                'class'           => $class,
                'school_count'    => $schoolCount,
                'total_seats'     => $totalSeats,
                'total_existing'  => $totalExisting,
                'total_regular'   => $totalRegular,
                'total_oosc'      => $totalOosc,
                'total_p2p'       => $totalP2p,
                'total_admitted'  => $totalAdmitted,
                'total_filled'    => $totalExisting + $totalAdmitted,
                'total_remaining' => max(0, $totalSeats - ($totalExisting + $totalAdmitted)),
            ];
        }

        $grand = [
            'seats'     => collect($overallByClass)->sum('total_seats'),
            'existing'  => collect($overallByClass)->sum('total_existing'),
            'regular'   => collect($overallByClass)->sum('total_regular'),
            'oosc'      => collect($overallByClass)->sum('total_oosc'),
            'p2p'       => collect($overallByClass)->sum('total_p2p'),
            'admitted'  => collect($overallByClass)->sum('total_admitted'),
            'filled'    => collect($overallByClass)->sum('total_filled'),
            'remaining' => collect($overallByClass)->sum('total_remaining'),
        ];

        $data = compact(
            'institutions', 'allClasses', 'seatData', 'admissionData',
            'overallByClass', 'grand', 'from', 'to', 'academicYear'
        );

        if ($format === 'excel') {
            return Excel::download(
                new AdmissionReportExport($data),
                'master-report-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('fde.exports.master-pdf', $data)->setPaper('a3', 'landscape');
        return $pdf->download('master-report-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf');
    }

    // ─────────────────────────────────────────────────────────────────
    //  VACANCY REPORT EXPORT  (FDE = all; AEO = own sector)
    // ─────────────────────────────────────────────────────────────────
    public function vacancyReport(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $this->authorize('reports.export');

        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectorIds    = $this->sectorIds();
        $format       = $request->input('format', 'pdf');

        // AEO cannot bypass their scope via sector_id query param
        $sectorId = $request->input('sector_id');
        if ($sectorIds !== null && $sectorId && ! in_array((int) $sectorId, $sectorIds)) {
            $sectorId = null;
        }

        $institutions = Institution::with(['sector', 'unionCouncil'])
            ->where('is_active', true)
            ->where('classes_configured', true)
            ->when($sectorIds,  fn($q) => $q->whereIn('sector_id', $sectorIds))
            ->when($sectorId,   fn($q) => $q->where('sector_id', $sectorId))
            ->orderBy('sector_id')->orderBy('name')
            ->get();

        $seatData = InstitutionClass::whereIn('institution_id', $institutions->pluck('id'))
            ->with('classModel')
            ->get()
            ->groupBy('institution_id');

        $admData = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereIn('institution_id', $institutions->pluck('id'))
            ->selectRaw('institution_id,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        $data = compact('institutions', 'seatData', 'admData', 'academicYear');

        if ($format === 'excel') {
            return Excel::download(new VacancyReportExport($data), 'vacancy-report.xlsx');
        }

        $pdf = Pdf::loadView('fde.exports.vacancy-pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('vacancy-report.pdf');
    }

    // ─────────────────────────────────────────────────────────────────
    //  OOSC REPORT EXPORT  (FDE = all; AEO = own sector)
    // ─────────────────────────────────────────────────────────────────
    public function ooscReport(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $this->authorize('reports.export');

        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectorIds    = $this->sectorIds();
        $format       = $request->input('format', 'pdf');

        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $institutions = Institution::with(['sector'])
            ->where('is_active', true)
            ->where('classes_configured', true)
            ->when($sectorIds, fn($q) => $q->whereIn('sector_id', $sectorIds))
            ->orderBy('sector_id')->orderBy('name')
            ->get();

        $ooscData = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('institution_id', $institutions->pluck('id'))
            ->selectRaw('
                institution_id,
                SUM(oosc_boys)            as oosc_boys,
                SUM(oosc_girls)           as oosc_girls,
                SUM(oosc_boys+oosc_girls) as oosc_total,
                SUM(p2p_boys)             as p2p_boys,
                SUM(p2p_girls)            as p2p_girls,
                SUM(p2p_boys+p2p_girls)   as p2p_total
            ')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        $data = compact('institutions', 'ooscData', 'from', 'to', 'academicYear');

        if ($format === 'excel') {
            return Excel::download(new OoscReportExport($data), 'oosc-report.xlsx');
        }

        $pdf = Pdf::loadView('fde.exports.oosc-pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('oosc-report.pdf');
    }
}
