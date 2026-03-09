<?php
// SAVE AS: app/Http/Controllers/Hoi/AdmissionCorrectionController.php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AdmissionCorrection;
use App\Models\Classes;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\AcademicYear;

class AdmissionCorrectionController extends Controller
{
    // ── List HOI's own past submissions (correctable records) ─────────
    public function index(Request $request)
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);

        $academicYear = AcademicYear::where('is_active', true)->first();

        // Build submissions query with filters
        $query = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->whereIn('status', ['verified', 'locked'])
            ->with('classModel');

        // Filter: date from
        if ($request->filled('date_from')) {
            $query->whereDate('admission_date', '>=', $request->date_from);
        }
        // Filter: date to
        if ($request->filled('date_to')) {
            $query->whereDate('admission_date', '<=', $request->date_to);
        }
        // Filter: class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        // Filter: correction status — join to corrections table
        if ($request->filled('correction_status')) {
            if ($request->correction_status === 'none') {
                // No correction request exists
                $query->whereNotIn('id', function ($sub) use ($institution) {
                    $sub->select(\DB::raw('CONCAT(institution_id,"_",class_id,"_",admission_date)'))
                        ->from('admission_corrections')
                        ->where('institution_id', $institution->id);
                });
            } else {
                $query->whereExists(function ($sub) use ($institution, $request) {
                    $sub->select(\DB::raw(1))
                        ->from('admission_corrections')
                        ->whereColumn('admission_corrections.class_id', 'daily_admissions.class_id')
                        ->whereColumn('admission_corrections.admission_date', 'daily_admissions.admission_date')
                        ->where('admission_corrections.institution_id', $institution->id)
                        ->where('admission_corrections.status', $request->correction_status);
                });
            }
        }

        $submissions = $query->orderByDesc('admission_date')
            ->orderBy('class_id')
            ->paginate(20)
            ->withQueryString();

        // All corrections keyed by admission_date + class_id for status display
        $corrections = AdmissionCorrection::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->orderByDesc('created_at')
            ->get()
            ->keyBy(fn($c) => $c->admission_date->toDateString() . '_' . $c->class_id);

        // Classes for filter dropdown — only classes this institution has submitted for
        $classes = Classes::whereHas('dailyAdmissions', fn($q) =>
            $q->where('institution_id', $institution->id)
        )->orderBy('order')->get(['id', 'name']);

        return view('hoi.corrections.index', compact(
            'institution', 'submissions', 'corrections', 'academicYear', 'classes'
        ));
    }

    // ── Show correction request form for a specific record ────────────
    public function create(Request $request)
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);

        $request->validate([
            'date'     => 'required|date',
            'class_id' => 'required|integer|exists:classes,id',
        ]);

        $entry = DailyAdmission::where('institution_id', $institution->id)
            ->where('admission_date', $request->date)
            ->where('class_id', $request->class_id)
            ->whereIn('status', ['verified', 'locked'])
            ->with('classModel')
            ->firstOrFail();

        // Check no pending correction already exists for this record
        $existingPending = AdmissionCorrection::where('institution_id', $institution->id)
            ->where('admission_date', $request->date)
            ->where('class_id', $request->class_id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return redirect()->route('hoi.corrections.index')
                ->with('warning', 'A correction request is already pending for this record.');
        }

        $hasEvening = in_array($institution->shift, ['evening', 'both']);

        return view('hoi.corrections.create', compact('entry', 'institution', 'hasEvening'));
    }

    // ── Store correction request ──────────────────────────────────────
    public function store(Request $request)
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);

        $request->validate([
            'admission_date'       => 'required|date',
            'class_id'             => 'required|integer|exists:classes,id',
            'reason'               => 'required|string|max:1000',
            'new_morning_boys'     => 'required|integer|min:0|max:9999',
            'new_morning_girls'    => 'required|integer|min:0|max:9999',
            'new_evening_boys'     => 'nullable|integer|min:0|max:9999',
            'new_evening_girls'    => 'nullable|integer|min:0|max:9999',
            'new_morning_oosc_boys'  => 'nullable|integer|min:0|max:9999',
            'new_morning_oosc_girls' => 'nullable|integer|min:0|max:9999',
            'new_morning_p2p_boys'   => 'nullable|integer|min:0|max:9999',
            'new_morning_p2p_girls'  => 'nullable|integer|min:0|max:9999',
            'new_evening_oosc_boys'  => 'nullable|integer|min:0|max:9999',
            'new_evening_oosc_girls' => 'nullable|integer|min:0|max:9999',
            'new_evening_p2p_boys'   => 'nullable|integer|min:0|max:9999',
            'new_evening_p2p_girls'  => 'nullable|integer|min:0|max:9999',
        ]);

        $entry = DailyAdmission::where('institution_id', $institution->id)
            ->where('admission_date', $request->admission_date)
            ->where('class_id', $request->class_id)
            ->whereIn('status', ['verified', 'locked'])
            ->firstOrFail();

        $academicYear = AcademicYear::where('is_active', true)->first();

        AdmissionCorrection::create([
            'institution_id'   => $institution->id,
            'class_id'         => $request->class_id,
            'academic_year_id' => $academicYear?->id,
            'admission_date'   => $request->admission_date,
            'reason'           => $request->reason,
            'requested_by'     => Auth::id(),

            // Snapshot old values
            'old_morning_boys'        => (int) $entry->morning_boys,
            'old_morning_girls'       => (int) $entry->morning_girls,
            'old_evening_boys'        => (int) $entry->evening_boys,
            'old_evening_girls'       => (int) $entry->evening_girls,
            'old_morning_oosc_boys'   => (int) $entry->morning_oosc_boys,
            'old_morning_oosc_girls'  => (int) $entry->morning_oosc_girls,
            'old_morning_p2p_boys'    => (int) $entry->morning_p2p_boys,
            'old_morning_p2p_girls'   => (int) $entry->morning_p2p_girls,
            'old_evening_oosc_boys'   => (int) $entry->evening_oosc_boys,
            'old_evening_oosc_girls'  => (int) $entry->evening_oosc_girls,
            'old_evening_p2p_boys'    => (int) $entry->evening_p2p_boys,
            'old_evening_p2p_girls'   => (int) $entry->evening_p2p_girls,

            // New values from HOI
            'new_morning_boys'        => (int) ($request->new_morning_boys       ?? 0),
            'new_morning_girls'       => (int) ($request->new_morning_girls      ?? 0),
            'new_evening_boys'        => (int) ($request->new_evening_boys       ?? 0),
            'new_evening_girls'       => (int) ($request->new_evening_girls      ?? 0),
            'new_morning_oosc_boys'   => (int) ($request->new_morning_oosc_boys  ?? 0),
            'new_morning_oosc_girls'  => (int) ($request->new_morning_oosc_girls ?? 0),
            'new_morning_p2p_boys'    => (int) ($request->new_morning_p2p_boys   ?? 0),
            'new_morning_p2p_girls'   => (int) ($request->new_morning_p2p_girls  ?? 0),
            'new_evening_oosc_boys'   => (int) ($request->new_evening_oosc_boys  ?? 0),
            'new_evening_oosc_girls'  => (int) ($request->new_evening_oosc_girls ?? 0),
            'new_evening_p2p_boys'    => (int) ($request->new_evening_p2p_boys   ?? 0),
            'new_evening_p2p_girls'   => (int) ($request->new_evening_p2p_girls  ?? 0),
        ]);

        return redirect()->route('hoi.corrections.index')
            ->with('success', 'Correction request submitted. FDE Cell will review shortly.');
    }
}
