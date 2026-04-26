<?php
// SAVE AS: app/Http/Controllers/Fde/AdmissionCorrectionController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Mail\AdmissionCorrectionDecided;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\AdmissionCorrection;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\DailyAdmission;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\Sector;

class AdmissionCorrectionController extends Controller
{
    // ── List all correction requests ──────────────────────────────────
    public function index(Request $request)
    {
        $query = AdmissionCorrection::with([
            'institution', 'classModel', 'requestedBy', 'reviewedBy'
        ])->latest();

        if ($request->filled('sector_id')) {
            $query->whereHas('institution', fn($q) => $q->where('sector_id', $request->sector_id));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('admission_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('admission_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('institution', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $corrections  = $query->paginate(25)->withQueryString();
        $pendingCount = AdmissionCorrection::where('status', 'pending')->count();

        $sectors = Sector::orderBy('name')->get(['id', 'name']);

        $institutions = Institution::where('is_active', true)
            ->when($request->sector_id, fn($q) => $q->where('sector_id', $request->sector_id))
            ->orderBy('name')->get(['id', 'name']);

        $classes = Classes::where('is_active', true)->orderBy('order')->get(['id', 'name']);

        return view('fde.corrections.index', compact(
            'corrections', 'pendingCount', 'sectors', 'institutions', 'classes'
        ));
    }

    // ── Show single correction with old vs new comparison ─────────────
    public function show(AdmissionCorrection $correction)
    {
        $correction->load(['institution', 'classModel', 'requestedBy', 'reviewedBy']);

        $hasEvening = in_array($correction->institution->shift, ['evening', 'both']);

        return view('fde.corrections.show', compact('correction', 'hasEvening'));
    }

    // ── Approve — update daily_admissions + adjust existing_enrollment ─
    public function approve(Request $request, AdmissionCorrection $correction)
    {
        abort_if(!$correction->isPending(), 422, 'This correction has already been reviewed.');

        $request->validate(['fde_note' => 'nullable|string|max:500']);

        DB::transaction(function () use ($correction, $request) {

            // Update the daily_admissions record with new values
            $entry = DailyAdmission::where('institution_id', $correction->institution_id)
                ->where('class_id', $correction->class_id)
                ->where('admission_date', $correction->admission_date)
                ->first();

            if ($entry) {
                $entry->update([
                    'morning_boys'        => $correction->new_morning_boys,
                    'morning_girls'       => $correction->new_morning_girls,
                    'evening_boys'        => $correction->new_evening_boys,
                    'evening_girls'       => $correction->new_evening_girls,
                    'morning_oosc_boys'   => $correction->new_morning_oosc_boys,
                    'morning_oosc_girls'  => $correction->new_morning_oosc_girls,
                    'morning_p2p_boys'    => $correction->new_morning_p2p_boys,
                    'morning_p2p_girls'   => $correction->new_morning_p2p_girls,
                    'evening_oosc_boys'   => $correction->new_evening_oosc_boys,
                    'evening_oosc_girls'  => $correction->new_evening_oosc_girls,
                    'evening_p2p_boys'    => $correction->new_evening_p2p_boys,
                    'evening_p2p_girls'   => $correction->new_evening_p2p_girls,
                    // Keep backward-compat columns in sync
                    'oosc_boys'  => $correction->new_morning_oosc_boys + $correction->new_evening_oosc_boys,
                    'oosc_girls' => $correction->new_morning_oosc_girls + $correction->new_evening_oosc_girls,
                    'p2p_boys'   => $correction->new_morning_p2p_boys + $correction->new_evening_p2p_boys,
                    'p2p_girls'  => $correction->new_morning_p2p_girls + $correction->new_evening_p2p_girls,
                ]);
            }

            // Adjust existing_enrollment by net difference
            // netDiff = newTotal - oldTotal (can be positive or negative)
            $diff = $correction->netDiff();

            if ($diff > 0) {
                InstitutionClass::where('institution_id', $correction->institution_id)
                    ->where('class_id', $correction->class_id)
                    ->increment('existing_enrollment', $diff);
            } elseif ($diff < 0) {
                InstitutionClass::where('institution_id', $correction->institution_id)
                    ->where('class_id', $correction->class_id)
                    ->decrement('existing_enrollment', abs($diff));
            }

            // Mark correction as approved
            $correction->update([
                'status'      => 'approved',
                'reviewed_by' => Auth::id(),
                'fde_note'    => $request->fde_note,
                'reviewed_at' => now(),
            ]);

            AuditLog::record(
                'approved',
                'AdmissionCorrection',
                $correction->id,
                [
                    'morning_boys'       => $correction->old_morning_boys,
                    'morning_girls'      => $correction->old_morning_girls,
                    'evening_boys'       => $correction->old_evening_boys,
                    'evening_girls'      => $correction->old_evening_girls,
                    'morning_oosc_boys'  => $correction->old_morning_oosc_boys,
                    'morning_oosc_girls' => $correction->old_morning_oosc_girls,
                    'morning_p2p_boys'   => $correction->old_morning_p2p_boys,
                    'morning_p2p_girls'  => $correction->old_morning_p2p_girls,
                    'evening_oosc_boys'  => $correction->old_evening_oosc_boys,
                    'evening_oosc_girls' => $correction->old_evening_oosc_girls,
                    'evening_p2p_boys'   => $correction->old_evening_p2p_boys,
                    'evening_p2p_girls'  => $correction->old_evening_p2p_girls,
                ],
                [
                    'morning_boys'       => $correction->new_morning_boys,
                    'morning_girls'      => $correction->new_morning_girls,
                    'evening_boys'       => $correction->new_evening_boys,
                    'evening_girls'      => $correction->new_evening_girls,
                    'morning_oosc_boys'  => $correction->new_morning_oosc_boys,
                    'morning_oosc_girls' => $correction->new_morning_oosc_girls,
                    'morning_p2p_boys'   => $correction->new_morning_p2p_boys,
                    'morning_p2p_girls'  => $correction->new_morning_p2p_girls,
                    'evening_oosc_boys'  => $correction->new_evening_oosc_boys,
                    'evening_oosc_girls' => $correction->new_evening_oosc_girls,
                    'evening_p2p_boys'   => $correction->new_evening_p2p_boys,
                    'evening_p2p_girls'  => $correction->new_evening_p2p_girls,
                ],
                'Correction approved. FDE note: ' . ($request->fde_note ?? 'none'),
                $correction->institution_id
            );
        });

        // ── Notify HOI via email ──────────────────────────────────────────
        $correction->loadMissing(['institution', 'classModel', 'requestedBy']);
        if ($correction->requestedBy?->email) {
            try {
                Mail::to($correction->requestedBy->email)
                    ->send(new AdmissionCorrectionDecided($correction, 'approved'));
            } catch (\Throwable) {
                // Email failure must not break the approval action
            }
        }

        return redirect()->route('fde.corrections.index')
            ->with('success', "Correction approved. Enrollment updated (net change: {$correction->netDiff()}).");
    }

    // ── Reject ────────────────────────────────────────────────────────
    public function reject(Request $request, AdmissionCorrection $correction)
    {
        abort_if(!$correction->isPending(), 422, 'This correction has already been reviewed.');

        $request->validate(['fde_note' => 'required|string|max:500']);

        $correction->update([
            'status'      => 'rejected',
            'reviewed_by' => Auth::id(),
            'fde_note'    => $request->fde_note,
            'reviewed_at' => now(),
        ]);

        AuditLog::record(
            'rejected',
            'AdmissionCorrection',
            $correction->id,
            null,
            null,
            'Correction rejected. FDE note: ' . $request->fde_note,
            $correction->institution_id
        );

        // ── Notify HOI via email ──────────────────────────────────────────
        $correction->loadMissing(['institution', 'classModel', 'requestedBy']);
        if ($correction->requestedBy?->email) {
            try {
                Mail::to($correction->requestedBy->email)
                    ->send(new AdmissionCorrectionDecided($correction, 'rejected'));
            } catch (\Throwable) {
                // Email failure must not break the rejection action
            }
        }

        return redirect()->route('fde.corrections.index')
            ->with('success', 'Correction request rejected.');
    }
}
