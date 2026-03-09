<?php

namespace App\Http\Controllers\Aeo;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\InstitutionSection;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;
use App\Models\Sector;

/**
 * SAVE AS: app/Http/Controllers/Aeo/DashboardController.php
 *
 * Handles TWO roles:
 *   AEO      → scoped to single sector via aeo_sectors pivot (sectors() belongsToMany)
 *   Director → sees ALL sectors system-wide (no sector restriction)
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user         = Auth::user();
        $academicYear = AcademicYear::where('is_active', true)->first();
        $isDirector   = $user->hasRole('director');

        // ── Scope ─────────────────────────────────────────────────────
        // Director → all sectors + all institutions
        // AEO      → single sector via aeo_sectors pivot
        // ─────────────────────────────────────────────────────────────
        if ($isDirector) {
            $sectors      = Sector::orderBy('name')->get();
            $institutions = Institution::where('is_active', true)
                ->with('sector')
                ->orderBy('sector_id')
                ->orderBy('name')
                ->get();

            $currentSector = null; // Director has no single sector
        } else {
            $currentSector = $user->sectors()->first();

            if (! $currentSector) {
                Auth::logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'No sector assigned to your account. Please contact FDE Cell.');
            }

            $sectors      = collect([$currentSector]);
            $institutions = Institution::where('sector_id', $currentSector->id)
                ->where('is_active', true)
                ->with('sector')
                ->orderBy('name')
                ->get();
        }

        $institutionIds = $institutions->pluck('id');

        // ── Seat data per institution (class-level) ───────────────────
        $seatData = InstitutionClass::whereIn('institution_id', $institutionIds)
            ->where('is_active', true)
            ->with('classModel')
            ->get()
            ->groupBy('institution_id');

        // ── Section counts per institution per class ──────────────────
        $sectionCounts = InstitutionSection::whereIn('institution_id', $institutionIds)
            ->selectRaw('institution_id, class_id, COUNT(*) as section_count')
            ->groupBy('institution_id', 'class_id')
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        // ── Cumulative admissions per institution per class ───────────
        $admissionData = DailyAdmission::whereIn('institution_id', $institutionIds)
            ->where('academic_year_id', $academicYear?->id)
            ->selectRaw('
                institution_id,
                class_id,
                SUM(
                    morning_boys  + evening_boys  +
                    morning_girls + evening_girls +
                    oosc_boys     + oosc_girls    +
                    p2p_boys      + p2p_girls
                ) as total_admitted
            ')
            ->groupBy('institution_id', 'class_id')
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        // ── Sector-level summary ──────────────────────────────────────
        // Iterates over $sectors (1 for AEO, many for Director).
        // Attaches computed totals as properties on each Sector model.
        // ─────────────────────────────────────────────────────────────
        $sectorSummary = $sectors->map(function ($sector) use ($institutions, $seatData, $admissionData) {
            $sectorInsts   = $institutions->where('sector_id', $sector->id);
            $sectorInstIds = $sectorInsts->pluck('id');

            $totalSeats    = 0;
            $totalExisting = 0;
            $totalAdmitted = 0;

            foreach ($sectorInstIds as $instId) {
                $classes    = $seatData[$instId]     ?? collect();
                $admissions = $admissionData[$instId] ?? collect();

                $totalSeats    += $classes->sum('total_seats');
                $totalExisting += $classes->sum('existing_enrollment');
                $totalAdmitted += $admissions->sum('total_admitted');
            }

            // Attach computed props directly on the Sector model
            $sector->school_count     = $sectorInsts->count();
            $sector->total_seats      = $totalSeats;
            $sector->total_existing   = $totalExisting;
            $sector->total_admitted   = $totalAdmitted;
            $sector->total_available  = max(0, $totalSeats - $totalExisting - $totalAdmitted);
            $sector->total_enrollment = $totalExisting + $totalAdmitted;

            return $sector;
        });

        // ── Grand totals (across all visible sectors) ─────────────────
        $grand = [
            'schools'    => $institutions->count(),
            'seats'      => $sectorSummary->sum('total_seats'),
            'existing'   => $sectorSummary->sum('total_existing'),
            'admitted'   => $sectorSummary->sum('total_admitted'),
            'available'  => $sectorSummary->sum('total_available'),
            'enrollment' => $sectorSummary->sum('total_enrollment'),
        ];

        return view('aeo.dashboard', compact(
            'sectors',          // full collection — used in sector summary table
            'currentSector',    // single Sector for AEO (null for Director)
            'isDirector',       // blade uses this for heading/label switching
            'institutions',
            'seatData',
            'sectionCounts',
            'admissionData',
            'sectorSummary',    // collection with computed totals per sector
            'grand',
            'academicYear'
        ));
    }
}
