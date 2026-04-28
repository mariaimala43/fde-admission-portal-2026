<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDailyAdmissionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AdmissionEditGrant;
use App\Models\AuditLog;
use App\Models\AdmissionMonitoring;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\InstitutionSection;
use App\Models\AcademicYear;
use App\Models\NewConstructionRoom;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DailyAdmissionController extends Controller
{
    use AuthorizesRequests;

    // ─────────────────────────────────────────────────────────────────
    //  SHOW ENTRY FORM
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $this->authorize('admission.create');

        $user        = Auth::user();
        $institution = $user->institution;

        if (! $institution) {
            return redirect()->route('hoi.profile.setup');
        }

        if (! $institution->classes_configured) {
            return redirect()->route('hoi.classes.setup')
                ->with('error', 'Please configure your classes and sections first.');
        }

        $today        = now()->toDateString();
        $academicYear = AcademicYear::where('is_active', true)->first();

        // ── Selected date (date picker — defaults to today) ───────────
        $selectedDate = request('date', $today);
        if ($selectedDate > $today) {
            $selectedDate = $today;
        }
        if ($academicYear && $selectedDate < $academicYear->start_date) {
            $selectedDate = $today;
        }
        $isToday = ($selectedDate === $today);

        // Option A: cutoff is no longer a gate — always false for form purposes
        $isPastCutoff = false;
        $activeGrant  = null;

        // ── Daily reminder (shown once per day, only if viewing today & no submitted/verified entry) ──
        $showReminder    = false;
        $reminderMessage = null;
        $sessionKey      = 'admission_reminder_shown_' . today()->toDateString();

        if (
            $academicYear &&
            $academicYear->isAdmissionOpen() &&
            $isToday &&
            ! session()->has($sessionKey)
        ) {
            $todayDoneCount = DailyAdmission::where('institution_id', $institution->id)
                ->whereDate('admission_date', today())
                ->whereIn('status', ['submitted', 'verified'])
                ->count();

            if ($todayDoneCount === 0) {
                $showReminder    = true;
                $cutoffTime      = \Carbon\Carbon::createFromTimeString($academicYear->daily_cutoff_time)
                    ->format('h:i A');
                $reminderMessage = "Please update today's admission data before {$cutoffTime}.";
                session([$sessionKey => true]);
            }
        }

        // ── All active institution classes ────────────────────────────
        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        // ── Section counts per class ──────────────────────────────────
        $sectionCounts = InstitutionSection::where('institution_id', $institution->id)
            ->selectRaw('class_id, COUNT(*) as cnt')
            ->groupBy('class_id')
            ->pluck('cnt', 'class_id');

        // ── Selected date's entries keyed by class_id ────────────────
        $todayEntries = DailyAdmission::where('institution_id', $institution->id)
            ->where('admission_date', $selectedDate)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->get()
            ->keyBy('class_id');

        // ── Cumulative admissions ALL other days, ALL statuses ────────
        // Drafts count against capacity — this prevents over-admission
        $cumulativeAll = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->where('admission_date', '!=', $selectedDate)
            ->selectRaw('
                class_id,
                SUM(
                    morning_boys + morning_girls + evening_boys + evening_girls +
                    morning_oosc_boys + morning_oosc_girls + morning_p2p_boys + morning_p2p_girls +
                    evening_oosc_boys + evening_oosc_girls + evening_p2p_boys + evening_p2p_girls
                ) as total_all,
                SUM(morning_boys + morning_girls + morning_oosc_boys + morning_oosc_girls + morning_p2p_boys + morning_p2p_girls) as morning_regular,
                SUM(evening_boys + evening_girls + evening_oosc_boys + evening_oosc_girls + evening_p2p_boys + evening_p2p_girls) as evening_regular
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // ── Build classesData array for Alpine.js ─────────────────────
        $hasMatricTech = (bool) $institution->has_matric_tech;

        $classesData = $classes->map(function ($ic) use (
            $todayEntries, $cumulativeAll, $sectionCounts, $institution
        ) {
            $entry      = $todayEntries[$ic->class_id]  ?? null;
            $cumul      = $cumulativeAll[$ic->class_id] ?? null;
            $cumRegular = $cumul ? (int) $cumul->total_all       : 0; // all fields — for display
            $cumMorning = $cumul ? (int) $cumul->morning_regular : 0; // seat-consuming morning prior
            $cumEvening = $cumul ? (int) $cumul->evening_regular : 0; // seat-consuming evening prior
            $cumSeats   = $cumMorning + $cumEvening;                  // total seat-consuming prior

            // Today's already-entered total (for cumulative display)
            $todayTotal = 0;
            if ($entry) {
                $todayTotal = (int)$entry->morning_boys + (int)$entry->morning_girls
                    + (int)$entry->evening_boys + (int)$entry->evening_girls
                    + (int)$entry->morning_oosc_boys + (int)$entry->morning_oosc_girls
                    + (int)$entry->morning_p2p_boys + (int)$entry->morning_p2p_girls
                    + (int)$entry->evening_oosc_boys + (int)$entry->evening_oosc_girls
                    + (int)$entry->evening_p2p_boys + (int)$entry->evening_p2p_girls;
            }

            // Available = Total Seats − Existing Enrollment − Prior seat-consuming admissions
            $sections  = (int) ($sectionCounts[$ic->class_id] ?? 1);
            $cumTotal  = $cumRegular + $todayTotal;
            $available = max(0, $ic->total_seats - $ic->existing_enrollment - $cumSeats);

            // Per-shift capacity (evening schools only; non-evening mirrors unified values)
            $hasEveningInst = (bool) $institution->has_evening_classes;

            if ($hasEveningInst) {
                $mSeats    = (int) $ic->morning_seats;
                $eSeats    = (int) $ic->evening_seats;
                $mExisting = (int) $ic->morning_existing;
                $eExisting = (int) $ic->evening_existing;
                $mAvail    = max(0, $ic->morning_seats - $ic->morning_existing - $cumMorning);
                $eAvail    = max(0, $ic->evening_seats - $ic->evening_existing - $cumEvening);
            } else {
                $mSeats    = (int) $ic->total_seats;
                $eSeats    = 0;
                $mExisting = (int) $ic->existing_enrollment;
                $eExisting = 0;
                $mAvail    = $available;
                $eAvail    = 0;
            }

            return [
                'class_id'          => $ic->class_id,
                'class_name'        => $ic->classModel?->name ?? "Class {$ic->class_id}",
                'class_order'       => (int) ($ic->classModel?->order ?? 0),
                'is_ece'            => (bool) ($ic->classModel?->is_ece ?? false),
                'sections'          => $sections,
                'existing'          => (int) $ic->existing_enrollment,
                'total_seats'       => (int) $ic->total_seats,
                'cumulative'        => $cumTotal,
                'available'         => $available,
                // Stored cumulative priors — used by frontend isOverLimit so it
                // can recompute true available even if cls.existing is changed.
                'cum_prior'         => $cumSeats,    // seat-consuming prior (non-evening)
                'cum_morning_prior' => $cumMorning,  // morning seat-consuming prior (evening schools)
                'cum_evening_prior' => $cumEvening,  // evening seat-consuming prior (evening schools)
                // Per-shift fields
                'morning_seats'     => $mSeats,
                'evening_seats'     => $eSeats,
                'morning_existing'  => $mExisting,
                'evening_existing'  => $eExisting,
                'morning_available' => $mAvail,
                'evening_available' => $eAvail,

                'morning_boys'        => $entry ? (int) $entry->morning_boys        : 0,
                'morning_girls'       => $entry ? (int) $entry->morning_girls       : 0,
                'evening_boys'        => $entry ? (int) $entry->evening_boys        : 0,
                'evening_girls'       => $entry ? (int) $entry->evening_girls       : 0,
                'morning_oosc_boys'   => $entry ? (int) $entry->morning_oosc_boys   : 0,
                'morning_oosc_girls'  => $entry ? (int) $entry->morning_oosc_girls  : 0,
                'morning_p2p_boys'    => $entry ? (int) $entry->morning_p2p_boys    : 0,
                'morning_p2p_girls'   => $entry ? (int) $entry->morning_p2p_girls   : 0,
                'evening_oosc_boys'   => $entry ? (int) $entry->evening_oosc_boys   : 0,
                'evening_oosc_girls'  => $entry ? (int) $entry->evening_oosc_girls  : 0,
                'evening_p2p_boys'    => $entry ? (int) $entry->evening_p2p_boys    : 0,
                'evening_p2p_girls'   => $entry ? (int) $entry->evening_p2p_girls   : 0,
                'matric_tech_count'   => $entry ? (int) $entry->matric_tech_count   : 0,
                'status'              => $entry?->status ?? 'draft',
            ];
        })->values()->toArray();

        $statuses        = $todayEntries->pluck('status');
        $anyVerified     = $statuses->contains('verified');  // for status-badge display only
        $anyLocked       = $statuses->contains('locked');    // true gating — only FDE-locked entries
        $anySubmitted    = $statuses->contains('submitted');
        $anyDraft        = $statuses->contains('draft');
        $admissionStatus = $institution->admission_status ?? 'open';

        // ── New construction rooms ─────────────────────────────────────
        $rooms             = NewConstructionRoom::where('institution_id', $institution->id)->get();
        $newRoomsTotal     = $rooms->sum('total_rooms');
        $newRoomsAllocated = $rooms->sum('allocated_rooms');
        $newRoomsRemaining = max(0, $newRoomsTotal - $newRoomsAllocated);

        // ── Matric tech counts ─────────────────────────────────────────
        $matricTechToday = $todayEntries->sum('matric_tech_count');
        $matricTechYear  = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->sum('matric_tech_count');

        // ── Has evening shift ──────────────────────────────────────────
        $hasEvening = (bool) $institution->has_evening_classes;

        return view('hoi.admissions.daily', compact(
            'institution', 'classes', 'classesData', 'todayEntries',
            'academicYear', 'isPastCutoff', 'activeGrant',
            'showReminder', 'reminderMessage', 'hasMatricTech', 'today',
            'selectedDate', 'isToday',
            'anyVerified', 'anyLocked', 'anySubmitted', 'anyDraft', 'admissionStatus',
            'newRoomsTotal', 'newRoomsAllocated', 'newRoomsRemaining',
            'matricTechToday', 'matricTechYear', 'hasEvening'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SAVE  (draft or submit)
    // ─────────────────────────────────────────────────────────────────
    public function save(SaveDailyAdmissionRequest $request)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        if (! $institution) {
            return redirect()->route('hoi.profile.setup');
        }

        // Re-check authorization for late saves
        if ($request->input('action') === 'submit') {
            $this->authorize('admission.create');
        }

        $today        = now()->toDateString();
        $academicYear = AcademicYear::where('is_active', true)->first();

        // ── Selected date (submitted by hidden form field) ────────────────
        $selectedDate = $request->input('date', $today);

        // Reject future dates
        if ($selectedDate > $today) {
            return redirect()->route('hoi.admissions.daily')
                ->with('error', 'Cannot submit data for a future date.');
        }
        // Clamp to academic year start
        if ($academicYear && $selectedDate < $academicYear->start_date) {
            return redirect()->route('hoi.admissions.daily')
                ->with('error', 'Selected date is before the academic year start.');
        }

        // ── Admission status guard ────────────────────────────────────────
        if ($institution->admission_status === 'closed') {
            return redirect()->route('hoi.admissions.daily', ['date' => $selectedDate])
                ->with('error', 'Admissions for your school are currently closed by the FDE Cell.');
        }

        // Load existing entries for the selected date (used for verified-lock check & audit log)
        $existingEntries = DailyAdmission::where('institution_id', $institution->id)
            ->where('admission_date', $selectedDate)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->get()
            ->keyBy('class_id');

        $isPastCutoff = false;
        $activeGrant  = null;

        $action     = $request->input('action', 'save');
        $seatErrors = [];

        DB::transaction(function () use (
            $request, $institution, $academicYear, $action,
            $today, $selectedDate, $existingEntries, &$seatErrors
        ) {
            foreach ($request->input('admissions', []) as $item) {
                $classId = (int) $item['class_id'];

                $ic = InstitutionClass::where('institution_id', $institution->id)
                    ->where('class_id', $classId)
                    ->where('is_active', true)
                    ->first();

                if (! $ic) continue;

                // ── Skip only if entry is FDE-locked for this date ────────────
                // 'verified' = HOI submitted; HOI can still correct it.
                // 'locked'   = FDE explicitly locked; truly immutable.
                $existing = $existingEntries[$classId] ?? null;
                if ($existing && $existing->status === 'locked') {
                    continue;
                }

                // ── Update baseline enrollment if HOI changed it ───────────────
                if (isset($item['existing_enrollment'])) {
                    $newExisting = max(0, (int) $item['existing_enrollment']);
                    if ($newExisting !== (int) $ic->existing_enrollment) {
                        $ic->update(['existing_enrollment' => $newExisting]);
                        $ic->existing_enrollment = $newExisting;
                    }
                }

                // ── Parse counts ──────────────────────────────────────────────
                $morningBoys       = (int) ($item['morning_boys']        ?? 0);
                $morningGirls      = (int) ($item['morning_girls']       ?? 0);
                $eveningBoys       = (int) ($item['evening_boys']        ?? 0);
                $eveningGirls      = (int) ($item['evening_girls']       ?? 0);
                $morningOoscBoys   = (int) ($item['morning_oosc_boys']   ?? 0);
                $morningOoscGirls  = (int) ($item['morning_oosc_girls']  ?? 0);
                $morningP2pBoys    = (int) ($item['morning_p2p_boys']    ?? 0);
                $morningP2pGirls   = (int) ($item['morning_p2p_girls']   ?? 0);
                $eveningOoscBoys   = (int) ($item['evening_oosc_boys']   ?? 0);
                $eveningOoscGirls  = (int) ($item['evening_oosc_girls']  ?? 0);
                $eveningP2pBoys    = (int) ($item['evening_p2p_boys']    ?? 0);
                $eveningP2pGirls   = (int) ($item['evening_p2p_girls']   ?? 0);
                $matricTechCount   = (int) ($item['matric_tech_count']   ?? 0);

                // Regular total = the only figure that consumes seats
                $grandTotal        = $morningBoys + $morningGirls + $eveningBoys + $eveningGirls;
                $morningGrandTotal = $morningBoys + $morningGirls;
                $eveningGrandTotal = $eveningBoys + $eveningGirls;

                // ── Seat capacity check (draft AND submit — never save over-limit data) ──
                $hasEveningInst = (bool) $institution->has_evening_classes;

                if ($hasEveningInst) {
                    // Per-shift check for evening schools
                    $className    = $ic->classModel?->name ?? "Class {$classId}";
                    $shiftBlocked = false;

                    // Morning shift check — all types (regular + OOSC + P2P) consume seats
                    if ($ic->morning_seats > 0) {
                        $priorMorning = DailyAdmission::where('institution_id', $institution->id)
                            ->where('class_id', $classId)
                            ->where('admission_date', '!=', $selectedDate)
                            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
                            ->selectRaw('SUM(
                                morning_boys + morning_girls +
                                morning_oosc_boys + morning_oosc_girls +
                                morning_p2p_boys + morning_p2p_girls
                            ) as t')
                            ->value('t') ?? 0;
                        $morningAllTypes = $morningBoys + $morningGirls
                            + $morningOoscBoys + $morningOoscGirls
                            + $morningP2pBoys  + $morningP2pGirls;
                        $mAvail = max(0, $ic->morning_seats - $ic->morning_existing - $priorMorning);
                        if ($morningAllTypes > $mAvail) {
                            $seatErrors[] = "{$className} (Morning): requested {$morningAllTypes}, "
                                . ($mAvail > 0 ? "only {$mAvail} seat(s) available" : "shift is FULL");
                            $shiftBlocked = true;
                        }
                    }

                    // Evening shift check — all types (regular + OOSC + P2P) consume seats
                    if ($ic->evening_seats > 0) {
                        $priorEvening = DailyAdmission::where('institution_id', $institution->id)
                            ->where('class_id', $classId)
                            ->where('admission_date', '!=', $selectedDate)
                            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
                            ->selectRaw('SUM(
                                evening_boys + evening_girls +
                                evening_oosc_boys + evening_oosc_girls +
                                evening_p2p_boys + evening_p2p_girls
                            ) as t')
                            ->value('t') ?? 0;
                        $eveningAllTypes = $eveningBoys + $eveningGirls
                            + $eveningOoscBoys + $eveningOoscGirls
                            + $eveningP2pBoys  + $eveningP2pGirls;
                        $eAvail = max(0, $ic->evening_seats - $ic->evening_existing - $priorEvening);
                        if ($eveningAllTypes > $eAvail) {
                            $seatErrors[] = "{$className} (Evening): requested {$eveningAllTypes}, "
                                . ($eAvail > 0 ? "only {$eAvail} seat(s) available" : "shift is FULL");
                            $shiftBlocked = true;
                        }
                    }

                    if ($shiftBlocked) {
                        continue; // skip save for this class if either shift is over-limit
                    }
                } elseif ($ic->total_seats > 0) {
                    // Unified check for non-evening schools (ECE and all regular classes).
                    // All admission types (regular + OOSC + P2P) consume seats.
                    // Include evening columns defensively — prevents bypass if any
                    // evening data was ever written for a non-evening school.
                    $priorDaysTotal = DailyAdmission::where('institution_id', $institution->id)
                        ->where('class_id', $classId)
                        ->where('admission_date', '!=', $selectedDate)
                        ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
                        ->selectRaw('SUM(
                            morning_boys + morning_girls +
                            evening_boys + evening_girls +
                            morning_oosc_boys + morning_oosc_girls +
                            morning_p2p_boys  + morning_p2p_girls +
                            evening_oosc_boys + evening_oosc_girls +
                            evening_p2p_boys  + evening_p2p_girls
                        ) as t')
                        ->value('t') ?? 0;

                    // All types entered today consume seats
                    $todayAllTypes = $morningBoys + $morningGirls
                                   + $morningOoscBoys + $morningOoscGirls
                                   + $morningP2pBoys  + $morningP2pGirls;

                    $available = max(0, $ic->total_seats - $ic->existing_enrollment - (int) $priorDaysTotal);

                    if ($todayAllTypes > $available) {
                        $className    = $ic->classModel?->name ?? "Class {$classId}";
                        $seatErrors[] = "{$className}: requested {$todayAllTypes}, "
                            . ($available > 0 ? "only {$available} seat(s) available" : "class is FULL");
                        continue;
                    }
                }

                // Matric Tech count must not exceed the class row total
                if ($matricTechCount > $grandTotal) {
                    $matricTechCount = $grandTotal;
                }

                // ── Status ────────────────────────────────────────────────────
                if ($action === 'submit') {
                    if ($institution->admission_status === 'by_approval') {
                        $status      = 'submitted';
                        $submittedBy = Auth::id();
                        $submittedAt = now();
                        $verifiedBy  = null;
                        $verifiedAt  = null;
                    } else {
                        $status      = 'verified';
                        $submittedBy = Auth::id();
                        $submittedAt = now();
                        $verifiedBy  = Auth::id();
                        $verifiedAt  = now();
                    }
                } else {
                    $status      = 'draft';
                    $submittedBy = null;
                    $submittedAt = null;
                    $verifiedBy  = null;
                    $verifiedAt  = null;
                }

                $dailyAdmission = DailyAdmission::updateOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'class_id'       => $classId,
                        'admission_date' => $selectedDate,
                    ],
                    [
                        'academic_year_id'    => $academicYear?->id,
                        'morning_boys'        => $morningBoys,
                        'morning_girls'       => $morningGirls,
                        'evening_boys'        => $eveningBoys,
                        'evening_girls'       => $eveningGirls,
                        'morning_oosc_boys'   => $morningOoscBoys,
                        'morning_oosc_girls'  => $morningOoscGirls,
                        'morning_p2p_boys'    => $morningP2pBoys,
                        'morning_p2p_girls'   => $morningP2pGirls,
                        'evening_oosc_boys'   => $eveningOoscBoys,
                        'evening_oosc_girls'  => $eveningOoscGirls,
                        'evening_p2p_boys'    => $eveningP2pBoys,
                        'evening_p2p_girls'   => $eveningP2pGirls,
                        'oosc_boys'           => $morningOoscBoys  + $eveningOoscBoys,
                        'oosc_girls'          => $morningOoscGirls + $eveningOoscGirls,
                        'p2p_boys'            => $morningP2pBoys   + $eveningP2pBoys,
                        'p2p_girls'           => $morningP2pGirls  + $eveningP2pGirls,
                        'matric_tech_count'   => $matricTechCount,
                        'status'              => $status,
                        'submitted_by'        => $submittedBy,
                        'submitted_at'        => $submittedAt,
                        'verified_by'         => $verifiedBy,
                        'verified_at'         => $verifiedAt,
                    ]
                );

                // ── Create / update AdmissionMonitoring record ────────────────
                // One monitoring record per daily_admission row.
                // Created as soon as the entry is verified.
                // Re-submitting never resets workflow progress already made by FDE.
                //
                // FIX: total_admitted is now ALWAYS set from the DailyAdmission
                // regularTotal (morning + evening boys/girls only — the figure
                // that consumes seats). This is the denominator for test counts.
                if (in_array($status, ['verified', 'locked'])) {

                    // Compute regular total — only seat-consuming admissions count
                    $regularTotal = $morningBoys + $morningGirls
                                  + $eveningBoys + $eveningGirls;

                    $monitoring = AdmissionMonitoring::firstOrCreate(
                        ['daily_admission_id' => $dailyAdmission->id],
                        [
                            'institution_id'   => $institution->id,
                            'class_id'         => $classId,
                            'academic_year_id' => $academicYear?->id,
                            'admission_date'   => $selectedDate,
                            'total_admitted'   => $regularTotal,   // ← FIX: was missing
                            'workflow_status'  => 'test_verification',
                            'test_status'      => 'pending',
                            'merit_status'     => 'pending',
                            'doc_status'       => 'pending',
                        ]
                    );

                    // Keep fields in sync if the daily admission was updated
                    // (e.g. HOI corrected the numbers under an edit grant)
                    if (! $monitoring->wasRecentlyCreated) {
                        $monitoring->updateQuietly([
                            'institution_id'   => $institution->id,
                            'class_id'         => $classId,
                            'academic_year_id' => $academicYear?->id,
                            'admission_date'   => $selectedDate,
                            // Re-sync total_admitted whenever the daily admission is
                            // updated — but ONLY if test counts have NOT been locked yet.
                            // Once HOI locks counts we must NOT change the denominator.
                            ...($monitoring->test_entry_locked ? [] : [
                                'total_admitted' => $regularTotal,
                            ]),
                        ]);
                    }
                }

                // ── Audit log for all past-date edits ─────────────────────────
                if ($selectedDate !== now()->toDateString()) {
                    AuditLog::record(
                        'past_date_edit',
                        'DailyAdmission',
                        $dailyAdmission->id,
                        [
                            'morning_boys'       => $existing?->morning_boys,
                            'morning_girls'      => $existing?->morning_girls,
                            'evening_boys'       => $existing?->evening_boys,
                            'evening_girls'      => $existing?->evening_girls,
                            'morning_oosc_boys'  => $existing?->morning_oosc_boys,
                            'morning_oosc_girls' => $existing?->morning_oosc_girls,
                            'morning_p2p_boys'   => $existing?->morning_p2p_boys,
                            'morning_p2p_girls'  => $existing?->morning_p2p_girls,
                            'evening_oosc_boys'  => $existing?->evening_oosc_boys,
                            'evening_oosc_girls' => $existing?->evening_oosc_girls,
                            'evening_p2p_boys'   => $existing?->evening_p2p_boys,
                            'evening_p2p_girls'  => $existing?->evening_p2p_girls,
                        ],
                        [
                            'morning_boys'       => $morningBoys,
                            'morning_girls'      => $morningGirls,
                            'evening_boys'       => $eveningBoys,
                            'evening_girls'      => $eveningGirls,
                            'morning_oosc_boys'  => $morningOoscBoys,
                            'morning_oosc_girls' => $morningOoscGirls,
                            'morning_p2p_boys'   => $morningP2pBoys,
                            'morning_p2p_girls'  => $morningP2pGirls,
                            'evening_oosc_boys'  => $eveningOoscBoys,
                            'evening_oosc_girls' => $eveningOoscGirls,
                            'evening_p2p_boys'   => $eveningP2pBoys,
                            'evening_p2p_girls'  => $eveningP2pGirls,
                        ],
                        "HOI edited past date {$selectedDate} (class {$classId})",
                        $institution->id
                    );
                }
            }
        });

        if (! empty($seatErrors)) {
            return redirect()->route('hoi.admissions.daily', ['date' => $selectedDate])
                ->with('error', 'Some classes were NOT saved — capacity exceeded: '
                    . implode(' | ', $seatErrors));
        }

        $isToday  = ($selectedDate === now()->toDateString());
        $message  = $action === 'submit'
            ? ($isToday
                ? "Today's admissions submitted and verified successfully."
                : "Admissions for {$selectedDate} submitted and verified successfully.")
            : ($isToday
                ? "Draft saved. Review and submit before the cutoff to finalise."
                : "Draft saved for {$selectedDate}.");

        return redirect()->route('hoi.admissions.daily', ['date' => $selectedDate])
            ->with('success', $message);
    }
}
