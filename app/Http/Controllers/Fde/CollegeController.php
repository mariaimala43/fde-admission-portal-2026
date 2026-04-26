<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\DailyAdmission;
use App\Models\Classes;
use App\Models\Sector;
use App\Models\UnionCouncil;
use App\Models\AcademicYear;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollegeController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    //  SHARED ADMISSION TOTALS QUERY
    //  Returns collection keyed by institution_id
    // ─────────────────────────────────────────────────────────────────

    private function admissionTotals(array $institutionIds, ?int $yearId): \Illuminate\Support\Collection
    {
        if (empty($institutionIds)) {
            return collect();
        }

        $query = DailyAdmission::whereIn('institution_id', $institutionIds)
            ->selectRaw('
                institution_id,
                SUM(morning_boys  + evening_boys  + oosc_boys  + p2p_boys)  as total_boys,
                SUM(morning_girls + evening_girls + oosc_girls + p2p_girls) as total_girls,
                SUM(morning_boys  + evening_boys  + morning_girls + evening_girls
                    + oosc_boys   + oosc_girls    + p2p_boys     + p2p_girls) as total_admitted
            ')
            ->groupBy('institution_id');

        // Only filter by academic year when one is active
        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        return $query->get()->keyBy('institution_id');
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHARED LIST BUILDER  (model / ex-fg)
    // ─────────────────────────────────────────────────────────────────

    private function collegeList(Request $request, string $type): array
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectors      = Sector::orderBy('name')->get();
        $ucs          = UnionCouncil::orderBy('code')->get();

        $search   = $request->input('search');
        $sectorId = $request->input('sector_id');
        $ucId     = $request->input('uc_id');

        $institutions = Institution::with(['sector', 'unionCouncil', 'users'])
            ->where('type', $type)
            ->where('is_active', true)
            ->when($search,   fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($sectorId, fn ($q) => $q->where('sector_id', $sectorId))
            ->when($ucId,     fn ($q) => $q->where('uc_id', $ucId))
            ->orderBy('name')
            ->get();

        $ids      = $institutions->pluck('id')->toArray();
        $admStats = $this->admissionTotals($ids, $academicYear?->id);

        // Attach stats directly onto each model instance
        $institutions->each(function ($inst) use ($admStats) {
            $stat = $admStats->get($inst->id);
            $inst->total_boys     = (int) ($stat->total_boys     ?? 0);
            $inst->total_girls    = (int) ($stat->total_girls    ?? 0);
            $inst->total_admitted = (int) ($stat->total_admitted ?? 0);
        });

        $totalColleges = $institutions->count();
        $totalAdmitted = $institutions->sum('total_admitted');
        $totalBoys     = $institutions->sum('total_boys');
        $totalGirls    = $institutions->sum('total_girls');

        return compact(
            'institutions', 'sectors', 'ucs', 'academicYear',
            'search', 'sectorId', 'ucId',
            'totalColleges', 'totalAdmitted', 'totalBoys', 'totalGirls',
            'type'
        );
    }

    // ─────────────────────────────────────────────────────────────────
    //  MODEL COLLEGES LIST
    // ─────────────────────────────────────────────────────────────────

    public function modelColleges(Request $request)
    {
        // HOI: redirect straight to their own profile
        if (Auth::user()->hasRole('hoi')) {
            $inst = Auth::user()->institution;
            if ($inst && $inst->type === 'Model College') {
                return redirect()->route('fde.colleges.profile', $inst);
            }
            abort(403, 'Access restricted to your institution.');
        }

        $data = $this->collegeList($request, 'Model College');

        return view('fde.colleges.index', $data);
    }

    // ─────────────────────────────────────────────────────────────────
    //  EX-FG COLLEGES LIST
    // ─────────────────────────────────────────────────────────────────

    public function exFgColleges(Request $request)
    {
        // HOI: redirect straight to their own profile
        if (Auth::user()->hasRole('hoi')) {
            $inst = Auth::user()->institution;
            if ($inst && $inst->type === 'Ex-FG College') {
                return redirect()->route('fde.colleges.profile', $inst);
            }
            abort(403, 'Access restricted to your institution.');
        }

        $data = $this->collegeList($request, 'Ex-FG College');

        return view('fde.colleges.index', $data);
    }

    // ─────────────────────────────────────────────────────────────────
    //  COLLEGE PROFILE
    // ─────────────────────────────────────────────────────────────────

    public function profile(Institution $institution)
    {
        // HOI guard — can only view their own institution
        if (Auth::user()->hasRole('hoi')) {
            $myInst = Auth::user()->institution;
            if (! $myInst || $myInst->id !== $institution->id) {
                abort(403, 'You may only view your own institution profile.');
            }
        }

        // Must be a Model or Ex-FG college
        if (! in_array($institution->type, ['Model College', 'Ex-FG College'])) {
            abort(404, 'Institution is not a Model or Ex-FG College.');
        }

        $institution->load(['sector', 'unionCouncil']);

        $academicYear = AcademicYear::where('is_active', true)->first();

        // Current HOI — first active user with hoi role attached to this institution
        $hoi = $institution->users()
            ->where('is_active', true)
            ->get()
            ->first(fn ($u) => $u->hasRole('hoi'));

        // Grand admission totals
        $totalsQuery = DailyAdmission::where('institution_id', $institution->id)
            ->selectRaw('
                SUM(morning_boys  + evening_boys  + oosc_boys  + p2p_boys)  as total_boys,
                SUM(morning_girls + evening_girls + oosc_girls + p2p_girls) as total_girls,
                SUM(morning_boys  + evening_boys  + morning_girls + evening_girls
                    + oosc_boys   + oosc_girls    + p2p_boys     + p2p_girls) as total_admitted
            ');

        if ($academicYear) {
            $totalsQuery->where('academic_year_id', $academicYear->id);
        }

        $totals = $totalsQuery->first();

        // Class-wise breakdown — use a plain query then attach class names separately
        $classSummaryQuery = DailyAdmission::where('institution_id', $institution->id)
            ->selectRaw('
                class_id,
                SUM(morning_boys  + evening_boys  + oosc_boys  + p2p_boys)  as boys,
                SUM(morning_girls + evening_girls + oosc_girls + p2p_girls) as girls,
                SUM(morning_boys  + evening_boys  + morning_girls + evening_girls
                    + oosc_boys   + oosc_girls    + p2p_boys     + p2p_girls) as total
            ')
            ->groupBy('class_id')
            ->orderBy('class_id');

        if ($academicYear) {
            $classSummaryQuery->where('academic_year_id', $academicYear->id);
        }

        $classSummaryRaw = $classSummaryQuery->get();

        // Attach class names (cannot use ->with() on a raw selectRaw query builder)
        $classIds  = $classSummaryRaw->pluck('class_id')->filter()->toArray();
        $classMap  = Classes::whereIn('id', $classIds)->get()->keyBy('id');

        $classSummary = $classSummaryRaw->map(function ($row) use ($classMap) {
            $row->classModel = $classMap->get($row->class_id);
            return $row;
        });

        return view('fde.colleges.profile', compact(
            'institution', 'hoi', 'academicYear', 'totals', 'classSummary'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  PDF EXPORT
    //  Route: GET colleges/export-pdf/{type}  → name: fde.colleges.export-pdf
    //  $type is 'model' or 'ex-fg' from the URL segment
    // ─────────────────────────────────────────────────────────────────

    public function exportPdf(Request $request, string $type)
    {
        $collegeType = $type === 'ex-fg' ? 'Ex-FG College' : 'Model College';

        $academicYear = AcademicYear::where('is_active', true)->first();

        $search   = $request->input('search');
        $sectorId = $request->input('sector_id');
        $ucId     = $request->input('uc_id');

        $institutions = Institution::with(['sector', 'unionCouncil', 'users'])
            ->where('type', $collegeType)
            ->where('is_active', true)
            ->when($search,   fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($sectorId, fn ($q) => $q->where('sector_id', $sectorId))
            ->when($ucId,     fn ($q) => $q->where('uc_id', $ucId))
            ->orderBy('name')
            ->get();

        $ids      = $institutions->pluck('id')->toArray();
        $admStats = $this->admissionTotals($ids, $academicYear?->id);

        $institutions->each(function ($inst) use ($admStats) {
            $stat = $admStats->get($inst->id);
            $inst->total_boys     = (int) ($stat->total_boys     ?? 0);
            $inst->total_girls    = (int) ($stat->total_girls    ?? 0);
            $inst->total_admitted = (int) ($stat->total_admitted ?? 0);
        });

        $generatedAt   = now()->format('d M Y, h:i A');
        $totalAdmitted = $institutions->sum('total_admitted');
        $totalBoys     = $institutions->sum('total_boys');
        $totalGirls    = $institutions->sum('total_girls');

        $pdf = Pdf::loadView('fde.colleges.pdf', compact(
            'institutions', 'collegeType', 'academicYear',
            'generatedAt', 'totalAdmitted', 'totalBoys', 'totalGirls'
        ))->setPaper('a4', 'landscape');

        $slug     = $type === 'ex-fg' ? 'ex-fg-colleges' : 'model-colleges';
        $filename = "{$slug}-" . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
