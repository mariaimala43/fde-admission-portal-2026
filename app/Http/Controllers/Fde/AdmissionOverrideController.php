<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Classes;
use App\Models\DailyAdmission;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\AcademicYear;

/**
 * SAVE AS: app/Http/Controllers/Fde/AdmissionOverrideController.php
 *
 * Add these routes inside the fde middleware group in web.php:
 *
 *   use App\Http\Controllers\Fde\AdmissionOverrideController;
 *
 *   Route::get( 'admissions',                      [AdmissionOverrideController::class, 'index']   )->name('admissions.index');
 *   Route::post('admissions/{admission}/override', [AdmissionOverrideController::class, 'override'])->name('admissions.override');
 *   Route::post('admissions/{admission}/return',   [AdmissionOverrideController::class, 'return']  )->name('admissions.return');
 */
class AdmissionOverrideController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    //  INDEX — filterable list of all daily admission records
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectors      = Sector::orderBy('name')->get();

        $query = DailyAdmission::with(['institution', 'institution.sector', 'classModel'])
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->orderBy('admission_date', 'desc')
            ->orderBy('institution_id');

        // ── Filters ───────────────────────────────────────────────────
        if ($request->filled('sector_id')) {
            $query->whereHas('institution', fn($q) =>
                $q->where('sector_id', $request->sector_id)
            );
        }

        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }

        if ($request->filled('emis')) {
            $query->whereHas('institution', fn($q) =>
                $q->where('code', 'like', '%' . $request->emis . '%')
            );
        }

        if ($request->filled('school_name')) {
            $query->whereHas('institution', fn($q) =>
                $q->where('name', 'like', '%' . $request->school_name . '%')
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('shift')) {
            if ($request->shift === 'morning') {
                $query->where(fn($q) => $q->where('morning_boys', '>', 0)->orWhere('morning_girls', '>', 0));
            } elseif ($request->shift === 'evening') {
                $query->where(fn($q) => $q->where('evening_boys', '>', 0)->orWhere('evening_girls', '>', 0));
            }
        }

        if ($request->filled('date')) {
            $query->where('admission_date', $request->date);
        } elseif ($request->filled('date_from') || $request->filled('date_to')) {
            $query->when($request->date_from, fn($q) => $q->where('admission_date', '>=', $request->date_from))
                  ->when($request->date_to,   fn($q) => $q->where('admission_date', '<=', $request->date_to));
        }

        $admissions = $query->paginate(30)->withQueryString();

        // For institution filter dropdown — only institutions with admission records
        $institutions = Institution::whereHas('dailyAdmissions')
            ->when($request->sector_id, fn($q) => $q->where('sector_id', $request->sector_id))
            ->orderBy('name')
            ->get(['id', 'name']);

        $classes = Classes::where('is_active', true)->orderBy('order')->get(['id', 'name']);

        // Summary counts for the stat bar
        $baseQuery = DailyAdmission::when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id));
        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'draft'      => (clone $baseQuery)->where('status', 'draft')->count(),
            'verified'   => (clone $baseQuery)->whereIn('status', ['verified', 'locked'])->count(),
            'returned'   => (clone $baseQuery)->where('status', 'returned')->count(),
            'overridden' => (clone $baseQuery)->whereNotNull('overridden_by')->count(),
        ];

        return view('fde.admissions.index', compact(
            'admissions', 'sectors', 'institutions', 'classes', 'stats', 'academicYear'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  OVERRIDE — unlock a verified/locked entry so HOI can re-edit
    // ─────────────────────────────────────────────────────────────────
    public function override(Request $request, DailyAdmission $admission)
    {
        $request->validate([
            'override_reason' => 'required|string|min:10|max:500',
        ], [
            'override_reason.required' => 'A reason is required before overriding this entry.',
            'override_reason.min'      => 'Please provide a meaningful reason (at least 10 characters).',
        ]);

        // Only verified or locked entries can be overridden
        if (! in_array($admission->status, ['verified', 'locked'])) {
            return back()->with('error', "Only verified or locked entries can be overridden. Current status: {$admission->statusLabel()}.");
        }

        $admission->update([
            'status'          => 'draft',           // HOI can now re-edit
            'overridden_by'   => Auth::id(),
            'override_reason' => $request->override_reason,
            'overridden_at'   => now(),
            // Clear verification stamps
            'verified_by'     => null,
            'verified_at'     => null,
        ]);

        return back()->with('success',
            "Entry for {$admission->institution->name} ({$admission->classModel?->name}, {$admission->admission_date->format('d M Y')}) has been unlocked. HOI can now re-edit."
        );
    }

    // ─────────────────────────────────────────────────────────────────
    //  RETURN — send back to HOI with a mandatory reason
    // ─────────────────────────────────────────────────────────────────
    public function return(Request $request, DailyAdmission $admission)
    {
        $request->validate([
            'return_reason' => 'required|string|min:10|max:500',
        ], [
            'return_reason.required' => 'A return reason is required.',
            'return_reason.min'      => 'Please provide a meaningful reason (at least 10 characters).',
        ]);

        // Can return draft or verified entries (not locked — those need override first)
        if ($admission->status === 'locked') {
            return back()->with('error', 'This entry is locked. Use Override to unlock it first, then return.');
        }

        $admission->update([
            'status'        => 'returned',
            'return_reason' => $request->return_reason,
            // Clear verification if it was verified
            'verified_by'   => null,
            'verified_at'   => null,
        ]);

        return back()->with('success',
            "Entry returned to {$admission->institution->name} with your note. HOI must correct and re-submit."
        );
    }
}
