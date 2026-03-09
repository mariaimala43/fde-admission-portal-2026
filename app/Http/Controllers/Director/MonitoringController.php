<?php

// SAVE AS: app/Http/Controllers/Director/MonitoringController.php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AdmissionMonitoring;
use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\Sector;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    // ── System-wide monitoring index (read-only) ──────────────────────
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $query = AdmissionMonitoring::with(['institution.sector', 'classModel'])
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->latest();

        if ($request->filled('sector_id')) {
            $query->whereHas('institution', fn($q) => $q->where('sector_id', $request->sector_id));
        }

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

        $records      = $query->paginate(40)->withQueryString();
        $sectors      = Sector::orderBy('name')->get(['id', 'name']);
        $institutions = Institution::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sector_id']);

        // Summary stats
        $baseQuery = AdmissionMonitoring::when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id));

        $stats = (object) [
            'total'             => (clone $baseQuery)->count(),
            'test_passed'       => (clone $baseQuery)->where('test_status', 'passed')->count(),
            'merit_published'   => (clone $baseQuery)->where('merit_status', 'published')->count(),
            'docs_complete'     => (clone $baseQuery)->where('doc_status', 'complete')->count(),
            'docs_provisional'  => (clone $baseQuery)->whereIn('doc_status', ['provisional', 'affidavit_case'])->count(),
        ];

        return view('director.monitoring.index', compact(
            'records', 'sectors', 'institutions',
            'academicYear', 'stats'
        ));
    }

    // ── Show single record (read-only) ────────────────────────────────
    public function show(AdmissionMonitoring $monitoring)
    {
        $monitoring->load([
            'institution.sector',
            'classModel',
            'dailyAdmission',
            'audits.changedBy',
        ]);

        return view('director.monitoring.show', compact('monitoring'));
    }
}
