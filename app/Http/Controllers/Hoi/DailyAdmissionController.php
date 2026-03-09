<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\InstitutionSection;
use App\Models\AcademicYear;
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

        // ── Cutoff ────────────────────────────────────────────────────
        $isPastCutoff = $academicYear
            ? $academicYear->isCutoffPassed()
            : now('Asia/Karachi')->gte(now('Asia/Karachi')->copy()->setTimeFromTimeString('23:59:00'));

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

        // ── Today's entries keyed by class_id ─────────────────────────
        $todayEntries = DailyAdmission::where('institution_id', $institution->id)
            ->where('admission_date', $today)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->get()
            ->keyBy('class_id');

        // ── Cumulative admissions ALL prior days, ALL statuses ────────
        // Drafts count against capacity — this prevents over-admission
        $cumulativeAll = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->where('admission_date', '!=', $today)
            ->selectRaw('
                class_id,
                SUM(
                    morning_boys + morning_girls + evening_boys + evening_girls +
                    morning_oosc_boys + morning_oosc_girls + morning_p2p_boys + morning_p2p_girls +
                    evening_oosc_boys + evening_oosc_girls + evening_p2p_boys + evening_p2p_girls
                ) as regular_total
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // ── Build classesData array for Alpine.js ─────────────────────
        $classesData = $classes->map(function ($ic) use (
            $todayEntries, $cumulativeAll, $sectionCounts
        ) {
            $entry      = $todayEntries[$ic->class_id]  ?? null;
            $cumul      = $cumulativeAll[$ic->class_id] ?? null;
            $cumRegular = $cumul ? (int) $cumul->regular_total : 0;

            // Today's already-entered total
            $todayTotal = 0;
            if ($entry) {
                $todayTotal = (int)$entry->morning_boys + (int)$entry->morning_girls
                    + (int)$entry->evening_boys + (int)$entry->evening_girls
                    + (int)$entry->morning_oosc_boys + (int)$entry->morning_oosc_girls
                    + (int)$entry->morning_p2p_boys + (int)$entry->morning_p2p_girls
                    + (int)$entry->evening_oosc_boys + (int)$entry->evening_oosc_girls
                    + (int)$entry->evening_p2p_boys + (int)$entry->evening_p2p_girls;
            }

            // Available = seats remaining for today's entry
            // (prior days already consumed; today's entry REPLACES, not adds)
            $available = max(0, $ic->total_seats - $ic->existing_enrollment - $cumRegular);

            return [
                'class_id'     => $ic->class_id,
                'class_name'   => $ic->classModel?->name ?? "Class {$ic->class_id}",
                'is_ece'       => (bool) ($ic->classModel?->is_ece ?? false),
                'sections'     => (int) ($sectionCounts[$ic->class_id] ?? 1),
                'existing'     => (int) $ic->existing_enrollment,
                'total_seats'  => (int) $ic->total_seats,
                'cumulative'   => $cumRegular + $todayTotal,  // all-time for display
                'available'    => $available,                  // headroom for today's entry

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

                'status'       => $entry?->status              ?? null,
                'status_label' => $entry?->statusLabel()       ?? null,
                'badge_class'  => $entry?->statusBadgeClass()  ?? 'bg-gray-100 text-gray-500',
                'is_locked'    => $entry ? $entry->isVerified() : false,
            ];
        })->values()->toArray();

        $hasEvening = in_array($institution->shift, ['evening', 'both']);

        $anyVerified  = $todayEntries->contains(fn($e) => in_array($e->status, ['verified', 'locked']));
        $anyDraft     = $todayEntries->contains(fn($e) => in_array($e->status, ['draft', 'returned']));
        $anySubmitted = $todayEntries->contains(fn($e) => $e->status === 'submitted');

        return view('hoi.admissions.daily', compact(
            'institution', 'academicYear', 'classesData',
            'today', 'hasEvening', 'isPastCutoff',
            'anyVerified', 'anyDraft', 'anySubmitted'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SAVE  — action: 'draft' | 'submit'
    // ─────────────────────────────────────────────────────────────────
    public function save(Request $request)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        abort_if(! $institution, 403, 'No institution assigned.');

        $action = $request->input('action', 'draft');

        if ($action === 'submit') {
            $this->authorize('admission.submit');
        } else {
            $this->authorize('admission.create');
        }

        $request->validate([
            'admissions'                        => 'required|array',
            'admissions.*.class_id'             => 'required|exists:classes,id',
            'admissions.*.morning_boys'         => 'required|integer|min:0|max:9999',
            'admissions.*.morning_girls'        => 'required|integer|min:0|max:9999',
            'admissions.*.evening_boys'         => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_girls'        => 'nullable|integer|min:0|max:9999',
            'admissions.*.morning_oosc_boys'    => 'nullable|integer|min:0|max:9999',
            'admissions.*.morning_oosc_girls'   => 'nullable|integer|min:0|max:9999',
            'admissions.*.morning_p2p_boys'     => 'nullable|integer|min:0|max:9999',
            'admissions.*.morning_p2p_girls'    => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_oosc_boys'    => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_oosc_girls'   => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_p2p_boys'     => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_p2p_girls'    => 'nullable|integer|min:0|max:9999',
        ]);

        $today        = now()->toDateString();
        $academicYear = AcademicYear::where('is_active', true)->first();

        $isPastCutoff = $academicYear
            ? $academicYear->isCutoffPassed()
            : now('Asia/Karachi')->gte(now('Asia/Karachi')->copy()->setTimeFromTimeString('23:59:00'));

        if ($isPastCutoff) {
            return redirect()->route('hoi.admissions.daily')
                ->with('warning', 'The daily entry window has closed. Contact the FDE Cell for corrections.');
        }

        $validClassIds = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->pluck('class_id')
            ->flip();

        $seatErrors = [];

        DB::transaction(function () use (
            $request, $institution, $today, $academicYear,
            $action, $validClassIds, &$seatErrors
        ) {
            foreach ($request->input('admissions', []) as $item) {
                $classId = (int) $item['class_id'];

                if (! isset($validClassIds[$classId])) {
                    continue;
                }

                $morningBoys       = (int) ($item['morning_boys']       ?? 0);
                $morningGirls      = (int) ($item['morning_girls']      ?? 0);
                $eveningBoys       = (int) ($item['evening_boys']       ?? 0);
                $eveningGirls      = (int) ($item['evening_girls']      ?? 0);
                $morningOoscBoys   = (int) ($item['morning_oosc_boys']  ?? 0);
                $morningOoscGirls  = (int) ($item['morning_oosc_girls'] ?? 0);
                $morningP2pBoys    = (int) ($item['morning_p2p_boys']   ?? 0);
                $morningP2pGirls   = (int) ($item['morning_p2p_girls']  ?? 0);
                $eveningOoscBoys   = (int) ($item['evening_oosc_boys']  ?? 0);
                $eveningOoscGirls  = (int) ($item['evening_oosc_girls'] ?? 0);
                $eveningP2pBoys    = (int) ($item['evening_p2p_boys']   ?? 0);
                $eveningP2pGirls   = (int) ($item['evening_p2p_girls']  ?? 0);

                $grandTotal = $morningBoys + $morningGirls + $eveningBoys + $eveningGirls
                    + $morningOoscBoys + $morningOoscGirls + $morningP2pBoys + $morningP2pGirls
                    + $eveningOoscBoys + $eveningOoscGirls + $eveningP2pBoys + $eveningP2pGirls;

                $existing = DailyAdmission::where('institution_id', $institution->id)
                    ->where('class_id', $classId)
                    ->where('admission_date', $today)
                    ->first();

                if ($grandTotal === 0 && ! $existing) {
                    continue;
                }

                if ($existing && $existing->isVerified()) {
                    continue;
                }

                // ── Capacity check — ALL statuses on ALL prior days count ─────
                $ic = InstitutionClass::where('institution_id', $institution->id)
                    ->where('class_id', $classId)
                    ->first();

                if ($ic && $grandTotal > 0) {
                    // All prior days, regardless of status
                    $cumulAllDays = DailyAdmission::where('institution_id', $institution->id)
                        ->where('class_id', $classId)
                        ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
                        ->where('admission_date', '!=', $today)  // today's entry replaces, not adds
                        ->selectRaw('SUM(
                            morning_boys + morning_girls + evening_boys + evening_girls +
                            morning_oosc_boys + morning_oosc_girls + morning_p2p_boys + morning_p2p_girls +
                            evening_oosc_boys + evening_oosc_girls + evening_p2p_boys + evening_p2p_girls
                        ) as total')
                        ->value('total') ?? 0;

                    $available = $ic->total_seats - $ic->existing_enrollment - (int) $cumulAllDays;

                    if ($grandTotal > $available) {
                        $allowed   = max(0, $available);
                        $className = $ic->classModel?->name ?? "Class {$classId}";
                        $seatErrors[] = "{$className}: requested {$grandTotal}, "
                            . ($allowed > 0 ? "only {$allowed} seat(s) available" : "class is FULL");
                        continue; // hard block — do not save this class
                    }
                }

                // ── Status ────────────────────────────────────────────────────
                if ($action === 'submit') {
                    $status      = 'verified';
                    $submittedBy = Auth::id();
                    $submittedAt = now();
                    $verifiedBy  = Auth::id();
                    $verifiedAt  = now();
                } else {
                    $status      = 'draft';
                    $submittedBy = null;
                    $submittedAt = null;
                    $verifiedBy  = null;
                    $verifiedAt  = null;
                }

                DailyAdmission::updateOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'class_id'       => $classId,
                        'admission_date' => $today,
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
                        'status'              => $status,
                        'submitted_by'        => $submittedBy,
                        'submitted_at'        => $submittedAt,
                        'verified_by'         => $verifiedBy,
                        'verified_at'         => $verifiedAt,
                    ]
                );
            }
        });

        if (! empty($seatErrors)) {
            return redirect()->route('hoi.admissions.daily')
                ->with('error', 'Admission blocked — intake capacity exceeded: '
                    . implode(' | ', $seatErrors));
        }

        $message = $action === 'submit'
            ? "Today's admissions submitted and verified successfully."
            : "Draft saved. Review and submit before 11:59 PM to finalise.";

        return redirect()->route('hoi.admissions.daily')->with('success', $message);
    }
}
