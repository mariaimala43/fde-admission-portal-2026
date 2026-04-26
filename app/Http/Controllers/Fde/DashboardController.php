<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Institution;
use App\Models\DailyAdmission;
use App\Models\NewConstructionRoom;
use App\Models\Referral;
use App\Models\Sector;
use App\Models\AcademicYear;
use App\Models\InstitutionClass;

class DashboardController extends Controller
{
    public function index()
    {
        $today        = now()->toDateString();
        $academicYear = AcademicYear::where('is_active', true)->first();

        // ── Today's grand totals ───────────────────────────
        $todayTotals = DailyAdmission::where('admission_date', $today)
            ->selectRaw('
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls)     as regular,
                SUM(oosc_boys + oosc_girls)       as oosc,
                SUM(p2p_boys + p2p_girls)         as p2p,
                SUM(matric_tech_count)            as matric_tech
            ')
            ->first();

        // ── Cumulative grand totals ────────────────────────
        $cumulativeTotals = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->selectRaw('
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls)     as regular,
                SUM(oosc_boys + oosc_girls)       as oosc,
                SUM(p2p_boys + p2p_girls)         as p2p,
                SUM(matric_tech_count)            as matric_tech
            ')
            ->first();

        $seatTotals = InstitutionClass::where('is_active', true)
            ->selectRaw('SUM(total_seats) as seats, SUM(existing_enrollment) as existing')
            ->first();

        $availableCapacity = max(
            0,
            (int) ($seatTotals?->seats ?? 0)
            - (int) ($seatTotals?->existing ?? 0)
            - (int) ($cumulativeTotals?->regular ?? 0)
        );

        // ── Sector-wise breakdown (cumulative) ─────────────
        $sectorBreakdown = Sector::withCount('institutions')
            ->get()
            ->map(function ($sector) use ($academicYear, $today) {
                $institutionIds = $sector->institutions()->pluck('id');

                $cumul = DailyAdmission::whereIn('institution_id', $institutionIds)
                    ->where('academic_year_id', $academicYear?->id)
                    ->selectRaw('
                        SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                        SUM(oosc_boys + oosc_girls) as oosc,
                        SUM(p2p_boys + p2p_girls)   as p2p,
                        SUM(matric_tech_count)      as matric_tech
                    ')
                    ->first();

                $todayCount = DailyAdmission::whereIn('institution_id', $institutionIds)
                    ->where('admission_date', $today)
                    ->selectRaw('SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total')
                    ->value('total') ?? 0;

                $sector->cumul_total       = $cumul?->total       ?? 0;
                $sector->cumul_oosc        = $cumul?->oosc        ?? 0;
                $sector->cumul_p2p         = $cumul?->p2p         ?? 0;
                $sector->cumul_matric_tech = $cumul?->matric_tech ?? 0;
                $sector->today_total       = $todayCount;

                return $sector;
            });

        // ── Schools that haven't submitted today ───────────
        $submittedIds = DailyAdmission::where('admission_date', $today)
            ->pluck('institution_id')
            ->unique();

        $notSubmitted = Institution::whereNotIn('id', $submittedIds)
            ->where('is_active', true)
            ->with('sector')
            ->orderBy('sector_id')
            ->orderBy('name')
            ->get();

        // ── Total schools summary ──────────────────────────
        $totalSchools     = Institution::where('is_active', true)->count();
        $submittedToday   = $submittedIds->count();
        $notSubmittedCount = $totalSchools - $submittedToday;

        // ── New construction rooms summary ────────────────
        $newRoomsStats = (object) [
            'schools'        => NewConstructionRoom::count(),
            'total_rooms'    => NewConstructionRoom::sum('rooms_total'),
            'completed'      => NewConstructionRoom::where('construction_status', 'completed')->count(),
            'near_completion'=> NewConstructionRoom::where('construction_status', 'near_completion')->count(),
            'capacity_added' => NewConstructionRoom::sum('rooms_total') * 40,
        ];

        // ── Referral outcome stats ─────────────────────────
        $referralStats = Referral::selectRaw("
                COUNT(*)                                          as total,
                SUM(status = 'pending')                           as pending,
                SUM(status = 'accepted')                          as accepted,
                SUM(status = 'rejected')                          as rejected,
                SUM(admission_status = 'admitted')                as admitted,
                SUM(admission_status = 'not_admitted')            as not_admitted,
                SUM(test_result = 'fail')                         as test_failed
            ")->first();

        return view('fde.dashboard', compact(
            'todayTotals', 'cumulativeTotals', 'sectorBreakdown',
            'notSubmitted', 'totalSchools', 'submittedToday',
            'notSubmittedCount', 'today', 'referralStats', 'newRoomsStats',
            'availableCapacity'
        ));
    }
}
