<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;
use App\Models\Sector;
use App\Models\NewConstructionRoom;
use Carbon\Carbon;

/**
 * SAVE AS: app/Http/Controllers/Director/DashboardController.php
 *
 * Executive dashboard for Director / DG / Secretary.
 * Read-only, system-wide view. No data entry.
 */
class DashboardController extends Controller
{
    public function index()
    {


        $academicYear = AcademicYear::where('is_active', true)->first();
        $today        = now()->toDateString();
        $thisWeekStart = now()->startOfWeek()->toDateString();

        // ── 1. SYSTEM-WIDE GRAND TOTALS ───────────────────────────────
        $grand = InstitutionClass::where('is_active', true)
            ->selectRaw('
                SUM(total_seats)          as total_seats,
                SUM(existing_enrollment)  as total_existing,
                COUNT(DISTINCT institution_id) as configured_schools
            ')
            ->first();

        $admTotals = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->selectRaw('
                SUM(morning_boys + evening_boys + morning_girls + evening_girls + oosc_boys + oosc_girls + p2p_boys + p2p_girls) as total_admitted,
                SUM(morning_boys + evening_boys + oosc_boys + p2p_boys)    as total_boys,
                SUM(morning_girls + evening_girls + oosc_girls + p2p_girls) as total_girls,
                SUM(oosc_boys + oosc_girls)   as total_oosc,
                SUM(p2p_boys  + p2p_girls)    as total_p2p,
                SUM(matric_tech_count)        as total_matric_tech
            ')
            ->first();

        $totalAdmitted   = (int) ($admTotals->total_admitted ?? 0);
        $totalSeats      = (int) ($grand->total_seats        ?? 0);
        $totalExisting   = (int) ($grand->total_existing     ?? 0);
        $totalAvailable  = max(0, $totalSeats - $totalExisting - $totalAdmitted);
        $totalEnrollment = $totalExisting + $totalAdmitted;
        $fillRate        = $totalSeats > 0 ? round($totalEnrollment / $totalSeats * 100, 1) : 0;

        $totalSchools      = Institution::where('is_active', true)->count();
        $configuredSchools = (int) ($grand->configured_schools ?? 0);

        // ── 2. TODAY'S ACTIVITY ───────────────────────────────────────
        $todayAdm = DailyAdmission::where('admission_date', $today)
            ->selectRaw('
                SUM(morning_boys + evening_boys + morning_girls + evening_girls + oosc_boys + oosc_girls + p2p_boys + p2p_girls) as total,
                SUM(morning_boys + evening_boys + oosc_boys + p2p_boys)    as boys,
                SUM(morning_girls + evening_girls + oosc_girls + p2p_girls) as girls,
                COUNT(DISTINCT institution_id) as submitting_schools
            ')
            ->first();

        $submittedToday   = (int) ($todayAdm->submitting_schools ?? 0);
        $notSubmittedToday = $configuredSchools - $submittedToday;

        // ── 3. SECTOR-WISE SUMMARY ────────────────────────────────────
        $sectors = Sector::orderBy('name')->get();

        $sectorSummary = $sectors->map(function ($sector) use ($academicYear) {
            $instIds = Institution::where('sector_id', $sector->id)
                ->where('is_active', true)->pluck('id');

            $seats = InstitutionClass::whereIn('institution_id', $instIds)
                ->where('is_active', true)
                ->selectRaw('SUM(total_seats) as s, SUM(existing_enrollment) as e')
                ->first();

            $adm = DailyAdmission::whereIn('institution_id', $instIds)
                ->where('academic_year_id', $academicYear?->id)
                ->selectRaw('
                    SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as admitted,
                    SUM(morning_boys+evening_boys+oosc_boys+p2p_boys)    as boys,
                    SUM(morning_girls+evening_girls+oosc_girls+p2p_girls) as girls,
                    SUM(oosc_boys+oosc_girls) as oosc,
                    SUM(p2p_boys+p2p_girls)   as p2p,
                    SUM(matric_tech_count)    as matric_tech
                ')
                ->first();

            $roomsRow = NewConstructionRoom::whereIn('institution_id', $instIds)
                ->selectRaw('SUM(rooms_total) as total, SUM(rooms_allocated) as allocated')
                ->first();

            $sectorNewRoomsTotal     = (int) ($roomsRow->total     ?? 0);
            $sectorNewRoomsAllocated = (int) ($roomsRow->allocated ?? 0);
            $sectorNewRoomsRemaining = max(0, $sectorNewRoomsTotal - $sectorNewRoomsAllocated);

            $s         = (int)($seats->s ?? 0);
            $e         = (int)($seats->e ?? 0);
            $admitted  = (int)($adm->admitted ?? 0);
            $total_enr = $e + $admitted;
            $fill      = $s > 0 ? round($total_enr / $s * 100, 1) : 0;

            return [
                'name'               => $sector->name,
                'schools'            => $instIds->count(),
                'seats'              => $s,
                'existing'           => $e,
                'admitted'           => $admitted,
                'boys'               => (int)($adm->boys  ?? 0),
                'girls'              => (int)($adm->girls ?? 0),
                'oosc'               => (int)($adm->oosc  ?? 0),
                'p2p'                => (int)($adm->p2p   ?? 0),
                'matric_tech'        => (int)($adm->matric_tech ?? 0),
                'available'          => max(0, $s - $e - $admitted),
                'enrollment'         => $total_enr,
                'fill_rate'          => $fill,
                'new_rooms_total'     => $sectorNewRoomsTotal,
                'new_rooms_allocated' => $sectorNewRoomsAllocated,
                'new_rooms_remaining' => $sectorNewRoomsRemaining,
            ];
        });

        // ── 4. GENDER SPLIT ───────────────────────────────────────────
        $genderSplit = [
            'boys'  => (int)($admTotals->total_boys  ?? 0),
            'girls' => (int)($admTotals->total_girls ?? 0),
        ];
        $totalGender = $genderSplit['boys'] + $genderSplit['girls'];
        $genderSplit['boys_pct']  = $totalGender > 0 ? round($genderSplit['boys']  / $totalGender * 100, 1) : 0;
        $genderSplit['girls_pct'] = $totalGender > 0 ? round($genderSplit['girls'] / $totalGender * 100, 1) : 0;

        // ── 5. 7-DAY ADMISSION TREND ──────────────────────────────────
        $trend = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->where('admission_date', '>=', now()->subDays(6)->toDateString())
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

        $trendDays = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $row  = $trend->get($date);
            $trendDays->push([
                'date'  => Carbon::parse($date)->format('D d'),
                'total' => (int)($row->total ?? 0),
                'boys'  => (int)($row->boys  ?? 0),
                'girls' => (int)($row->girls ?? 0),
            ]);
        }

        // ── 6. TOP 10 SCHOOLS BY ADMISSIONS ──────────────────────────
        $topSchools = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->selectRaw('institution_id, SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total')
            ->groupBy('institution_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('institution.sector')
            ->get()
            ->map(fn($r) => [
                'name'   => $r->institution?->name   ?? '—',
                'sector' => $r->institution?->sector?->name ?? '—',
                'total'  => (int)$r->total,
            ]);

        // ── 7. OOSC + P2P + MATRIC TECH TOTALS ───────────────────────
        $ooscTotal      = (int)($admTotals->total_oosc        ?? 0);
        $p2pTotal       = (int)($admTotals->total_p2p         ?? 0);
        $matricTechYear = (int)($admTotals->total_matric_tech ?? 0);
        $ooscPct        = $totalAdmitted > 0 ? round($ooscTotal / $totalAdmitted * 100, 1) : 0;

        $matricTechToday = (int) DailyAdmission::whereDate('admission_date', $today)
            ->sum('matric_tech_count');

        // ── New Construction Rooms (system-wide) ──────────────────────
        $roomsRow = NewConstructionRoom::selectRaw(
            'SUM(rooms_total) as total, SUM(rooms_allocated) as allocated'
        )->first();

        $newRoomsTotal       = (int) ($roomsRow->total     ?? 0);
        $newRoomsAllocated   = (int) ($roomsRow->allocated ?? 0);
        $newRoomsRemaining   = max(0, $newRoomsTotal - $newRoomsAllocated);
        $schoolsWithNewRooms = NewConstructionRoom::distinct('institution_id')->count('institution_id');

        // ── 8. SCHOOL TYPE BREAKDOWN ──────────────────────────────────
        $byType = Institution::where('is_active', true)
            ->selectRaw("type, COUNT(*) as count")
            ->groupBy('type')
            ->pluck('count', 'type');

        // ── 9. SUBMISSION COMPLIANCE (this week) ─────────────────────
        $weeklyCompliance = DailyAdmission::where('admission_date', '>=', $thisWeekStart)
            ->selectRaw('admission_date, COUNT(DISTINCT institution_id) as schools')
            ->groupBy('admission_date')
            ->orderBy('admission_date')
            ->get()
            ->map(fn($r) => [
                'day'     => Carbon::parse($r->admission_date)->format('D'),
                'schools' => (int)$r->schools,
                'pct'     => $configuredSchools > 0 ? round($r->schools / $configuredSchools * 100) : 0,
            ]);

        return view('director.dashboard', compact(
            'academicYear',
            'today',
            'totalSeats', 'totalExisting', 'totalAdmitted', 'totalAvailable',
            'totalEnrollment', 'fillRate',
            'totalSchools', 'configuredSchools',
            'todayAdm', 'submittedToday', 'notSubmittedToday',
            'sectorSummary',
            'genderSplit',
            'trendDays',
            'topSchools',
            'ooscTotal', 'p2pTotal', 'ooscPct',
            'matricTechToday', 'matricTechYear',
            'newRoomsTotal', 'newRoomsAllocated', 'newRoomsRemaining', 'schoolsWithNewRooms',
            'byType',
            'weeklyCompliance'
        ));
    }
}
