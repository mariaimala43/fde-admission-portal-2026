<?php

// SAVE AS: app/Http/Controllers/Hoi/StudentTransferController.php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StudentTransfer;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\AcademicYear;

class StudentTransferController extends Controller
{
    // ── List all transfers involving this HOI's school ────────────────
    public function index()
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);

        $outgoing = StudentTransfer::where('from_institution_id', $institution->id)
            ->with(['toInstitution', 'classModel', 'initiatedBy'])
            ->latest()
            ->get();

        $incoming = StudentTransfer::where('to_institution_id', $institution->id)
            ->with(['fromInstitution', 'classModel', 'initiatedBy'])
            ->latest()
            ->get();

        return view('hoi.transfers.index', compact('outgoing', 'incoming', 'institution'));
    }

    // ── Show create form ──────────────────────────────────────────────
    public function create()
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);

        $myClasses = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        $institutions = Institution::where('id', '!=', $institution->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('hoi.transfers.create', compact('institution', 'myClasses', 'institutions'));
    }

    // ── Store — batch: one request per student row ────────────────────
    public function store(Request $request)
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);

        $request->validate([
            'to_institution_id'          => 'required|exists:institutions,id',
            'students'                   => 'required|array|min:1',
            'students.*.class_id'        => 'required|exists:classes,id',
            'students.*.student_name'    => 'nullable|string|max:100',
            'students.*.father_name'     => 'nullable|string|max:100',
            'students.*.notes'           => 'nullable|string|max:500',
        ]);

        $toId         = $request->to_institution_id;
        $academicYear = AcademicYear::where('is_active', true)->first();
        $errors       = [];

        foreach ($request->students as $index => $student) {
            $classId = $student['class_id'];
            $rowNum  = $index + 1;

            // Sending school must have the class
            $fromHas = InstitutionClass::where('institution_id', $institution->id)
                ->where('class_id', $classId)->where('is_active', true)->exists();

            if (!$fromHas) {
                $errors[] = "Row {$rowNum}: Your school does not have this class.";
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
                'from_institution_id' => $institution->id,
                'to_institution_id'   => $toId,
                'class_id'            => $classId,
                'academic_year_id'    => $academicYear?->id,
                'student_name'        => $student['student_name'] ?? null,
                'father_name'         => $student['father_name']  ?? null,
                'notes'               => $student['notes']        ?? null,
                'initiated_by'        => Auth::id(),
                'initiated_by_role'   => 'hoi',
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
        return redirect()->route('hoi.transfers.index')
            ->with('success', "{$count} transfer request(s) submitted successfully. Waiting for the receiving school to accept.");
    }

    // ── Show single transfer ──────────────────────────────────────────
    public function show(StudentTransfer $transfer)
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);

        abort_if(
            $transfer->from_institution_id !== $institution->id &&
            $transfer->to_institution_id   !== $institution->id,
            403
        );

        $transfer->load(['fromInstitution', 'toInstitution', 'classModel', 'initiatedBy', 'actionedBy']);

        $isReceiving = $transfer->to_institution_id   === $institution->id;
        $isSending   = $transfer->from_institution_id === $institution->id;

        return view('hoi.transfers.show', compact('transfer', 'institution', 'isReceiving', 'isSending'));
    }

    // ── Accept (receiving HOI only) ───────────────────────────────────
    public function accept(Request $request, StudentTransfer $transfer)
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);
        abort_if($transfer->to_institution_id !== $institution->id, 403);
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

        return redirect()->route('hoi.transfers.show', $transfer)
            ->with('success', 'Transfer accepted. Enrollment counts have been updated.');
    }

    // ── Reject (receiving HOI only) ───────────────────────────────────
    public function reject(Request $request, StudentTransfer $transfer)
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);
        abort_if($transfer->to_institution_id !== $institution->id, 403);
        abort_if(!$transfer->isActionable(), 422, 'This transfer cannot be rejected.');

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $transfer->update([
            'status'           => 'rejected',
            'actioned_by'      => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
            'rejected_at'      => now(),
        ]);

        return redirect()->route('hoi.transfers.show', $transfer)
            ->with('success', 'Transfer request rejected.');
    }

    // ── Request more info (receiving HOI only) ────────────────────────
    public function requestInfo(Request $request, StudentTransfer $transfer)
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);
        abort_if($transfer->to_institution_id !== $institution->id, 403);
        abort_if(!$transfer->isPending(), 422, 'Info can only be requested on pending transfers.');

        $request->validate(['info_request_note' => 'required|string|max:500']);

        $transfer->update([
            'status'            => 'info_requested',
            'actioned_by'       => Auth::id(),
            'info_request_note' => $request->info_request_note,
            'info_requested_at' => now(),
        ]);

        return redirect()->route('hoi.transfers.show', $transfer)
            ->with('success', 'Info requested. The sending school has been notified.');
    }

    // ── Cancel (sending HOI only) ─────────────────────────────────────
    public function cancel(Request $request, StudentTransfer $transfer)
    {
        $institution = Auth::user()->institution;
        abort_if(!$institution, 403);
        abort_if($transfer->from_institution_id !== $institution->id, 403);
        abort_if(!$transfer->isActionable(), 422, 'This transfer can no longer be cancelled.');

        $request->validate(['cancellation_reason' => 'nullable|string|max:500']);

        $transfer->update([
            'status'              => 'cancelled',
            'actioned_by'         => Auth::id(),
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_at'        => now(),
        ]);

        return redirect()->route('hoi.transfers.index')
            ->with('success', 'Transfer request cancelled.');
    }
}
