<?php

// SAVE AS: app/Http/Controllers/Fde/StudentTransferController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StudentTransfer;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\Classes;
use App\Models\AcademicYear;

class StudentTransferController extends Controller
{
    // ── List all transfers system-wide ────────────────────────────────
    public function index(Request $request)
    {
        $query = StudentTransfer::with(['fromInstitution', 'toInstitution', 'classModel', 'initiatedBy'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('cross_sector')) {
            $query->where('is_cross_sector', true);
        }
        if ($request->filled('from_institution')) {
            $query->where('from_institution_id', $request->from_institution);
        }
        if ($request->filled('to_institution')) {
            $query->where('to_institution_id', $request->to_institution);
        }

        $transfers    = $query->paginate(30)->withQueryString();
        $institutions = Institution::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('fde.transfers.index', compact('transfers', 'institutions'));
    }

    // ── Show create form ──────────────────────────────────────────────
    public function create()
    {
        $institutions = Institution::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $classes      = Classes::orderBy('id')->get(['id', 'name']);

        return view('fde.transfers.create', compact('institutions', 'classes'));
    }

    // ── Store — batch: one request per student row ────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'from_institution_id'        => 'required|exists:institutions,id',
            'to_institution_id'          => 'required|exists:institutions,id|different:from_institution_id',
            'students'                   => 'required|array|min:1',
            'students.*.class_id'        => 'required|exists:classes,id',
            'students.*.student_name'    => 'nullable|string|max:100',
            'students.*.father_name'     => 'nullable|string|max:100',
            'students.*.notes'           => 'nullable|string|max:500',
        ]);

        $fromId       = $request->from_institution_id;
        $toId         = $request->to_institution_id;
        $academicYear = AcademicYear::where('is_active', true)->first();
        $errors       = [];

        // ── Detect cross-sector once for the whole batch ──────────────
        $fromInstitution = \App\Models\Institution::find($fromId);
        $toInstitution   = \App\Models\Institution::find($toId);
        $isCrossSector   = $fromInstitution && $toInstitution
            && $fromInstitution->sector_id !== $toInstitution->sector_id;

        foreach ($request->students as $index => $student) {
            $classId = $student['class_id'];
            $rowNum  = $index + 1;

            // Sending school must have the class
            $fromHas = InstitutionClass::where('institution_id', $fromId)
                ->where('class_id', $classId)->where('is_active', true)->exists();

            if (!$fromHas) {
                $errors[] = "Row {$rowNum}: The sending school does not have this class.";
                continue;
            }

            // Receiving school must have the class
            $toHas = InstitutionClass::where('institution_id', $toId)
                ->where('class_id', $classId)->where('is_active', true)->exists();

            if (!$toHas) {
                $errors[] = "Row {$rowNum}: The receiving school does not have this class.";
                continue;
            }

            StudentTransfer::create([
                'from_institution_id' => $fromId,
                'to_institution_id'   => $toId,
                'class_id'            => $classId,
                'academic_year_id'    => $academicYear?->id,
                'student_name'        => $student['student_name'] ?? null,
                'father_name'         => $student['father_name']  ?? null,
                'notes'               => $student['notes']        ?? null,
                'initiated_by'        => Auth::id(),
                'initiated_by_role'   => 'fde_cell',
                'is_cross_sector'     => $isCrossSector,
                'status'              => 'pending',
            ]);
        }

        if (!empty($errors)) {
            return back()
                ->withErrors($errors)
                ->withInput()
                ->with('warning', count($errors) . ' row(s) were skipped due to errors.');
        }

        $count = count($request->students);
        $msg   = "{$count} transfer request(s) created successfully.";

        if ($isCrossSector) {
            $msg .= " ⚠️ Cross-sector transfer — involves schools from different sectors.";
        }

        return redirect()->route('fde.transfers.index')->with('success', $msg);
    }

    // ── Show single transfer ──────────────────────────────────────────
    public function show(StudentTransfer $transfer)
    {
        $transfer->load(['fromInstitution', 'toInstitution', 'classModel', 'initiatedBy', 'actionedBy']);

        return view('fde.transfers.show', compact('transfer'));
    }

    // ── Force accept ──────────────────────────────────────────────────
    public function accept(StudentTransfer $transfer)
    {
        abort_if(!$transfer->isActionable(), 422, 'This transfer cannot be accepted.');

        DB::transaction(function () use ($transfer) {
            InstitutionClass::where('institution_id', $transfer->from_institution_id)
                ->where('class_id', $transfer->class_id)
                ->decrement('existing_enrollment');

            InstitutionClass::where('institution_id', $transfer->to_institution_id)
                ->where('class_id', $transfer->class_id)
                ->increment('existing_enrollment');

            $transfer->update([
                'status'      => 'accepted',
                'actioned_by' => Auth::id(),
                'accepted_at' => now(),
            ]);
        });

        return redirect()->route('fde.transfers.show', $transfer)
            ->with('success', 'Transfer accepted. Enrollment counts updated.');
    }

    // ── Approve cross-sector ─────────────────────────────────────────
    public function approveCrossSector(Request $request, StudentTransfer $transfer)
    {
        abort_if(!$transfer->isCrossSector(),   422, 'This is not a cross-sector transfer.');
        abort_if($transfer->isCrossSectorApproved(), 422, 'Already approved for cross-sector.');
        abort_if(!$transfer->isActionable(),    422, 'Transfer is no longer actionable.');

        $request->validate([
            'cross_sector_note' => 'required|string|min:10|max:500',
        ]);

        $transfer->update([
            'cross_sector_note'        => $request->cross_sector_note,
            'cross_sector_approved_by' => Auth::id(),
            'cross_sector_approved_at' => now(),
        ]);

        return redirect()->route('fde.transfers.show', $transfer)
            ->with('success', 'Cross-sector transfer approved. You can now force-accept or reject it.');
    }

    // ── Reject ────────────────────────────────────────────────────────
    public function reject(Request $request, StudentTransfer $transfer)
    {
        abort_if(!$transfer->isActionable(), 422, 'This transfer cannot be rejected.');

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $transfer->update([
            'status'           => 'rejected',
            'actioned_by'      => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
            'rejected_at'      => now(),
        ]);

        return redirect()->route('fde.transfers.show', $transfer)
            ->with('success', 'Transfer rejected.');
    }

    // ── Cancel ────────────────────────────────────────────────────────
    public function cancel(Request $request, StudentTransfer $transfer)
    {
        abort_if(!$transfer->isActionable(), 422, 'This transfer can no longer be cancelled.');

        $request->validate(['cancellation_reason' => 'nullable|string|max:500']);

        $transfer->update([
            'status'              => 'cancelled',
            'actioned_by'         => Auth::id(),
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_at'        => now(),
        ]);

        return redirect()->route('fde.transfers.index')
            ->with('success', 'Transfer cancelled.');
    }
}
