<?php

// SAVE AS: app/Http/Controllers/Aeo/MonitoringController.php

namespace App\Http\Controllers\Aeo;

use App\Http\Controllers\Controller;
use App\Models\AdmissionMonitoring;
use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    // ── Resolve AEO's sector via pivot ────────────────────────────────
    private function resolveSector(): ?Sector
    {
        return Auth::user()->sectors()->first();
    }

    // ── Redirect to login when no sector assigned ─────────────────────
    private function noSectorRedirect(): \Illuminate\Http\RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'No sector assigned to your account. Please contact FDE Cell.');
    }

    // ── Index — all monitoring records in this AEO's sector ───────────
    public function index(Request $request)
    {
        $sector = $this->resolveSector();

        if (! $sector) {
            return $this->noSectorRedirect();
        }
        $academicYear = AcademicYear::where('is_active', true)->first();

        // All institution IDs in this sector
        $institutionIds = Institution::where('sector_id', $sector->id)
            ->where('is_active', true)
            ->pluck('id');

        $query = AdmissionMonitoring::with(['institution', 'classModel'])
            ->whereIn('institution_id', $institutionIds)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->latest();

        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }
        if ($request->filled('test_status')) {
            $query->where('test_status', $request->test_status);
        }
        if ($request->filled('merit_status')) {
            $query->where('merit_status', $request->merit_status);
        }
        if ($request->filled('doc_status')) {
            $query->where('doc_status', $request->doc_status);
        }

        $records = $query->paginate(40)->withQueryString();

        // Institutions for filter dropdown (this sector only)
        $institutions = Institution::where('sector_id', $sector->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Summary stats for this sector
        $base = AdmissionMonitoring::whereIn('institution_id', $institutionIds)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id));

        $stats = (object) [
            'total'            => (clone $base)->count(),
            'test_passed'      => (clone $base)->where('test_status', 'passed')->count(),
            'merit_published'  => (clone $base)->where('merit_status', 'published')->count(),
            'docs_complete'    => (clone $base)->where('doc_status', 'complete')->count(),
            'docs_provisional' => (clone $base)->whereIn('doc_status', ['provisional', 'affidavit_case'])->count(),
        ];

        return view('aeo.monitoring.index', compact(
            'sector', 'records', 'institutions',
            'academicYear', 'stats'
        ));
    }

    // ── Show — single record (read-only, sector-scoped) ───────────────
    public function show(AdmissionMonitoring $monitoring)
    {
        $sector = $this->resolveSector();

        if (! $sector) {
            return $this->noSectorRedirect();
        }

        // Ensure record belongs to a school in this AEO's sector
        abort_if(
            $monitoring->institution->sector_id !== $sector->id,
            403,
            'This record belongs to a different sector.'
        );

        $monitoring->load([
            'institution',
            'classModel',
            'dailyAdmission',
            'audits.changedBy',
        ]);

        return view('aeo.monitoring.show', compact('monitoring', 'sector'));
    }
}
