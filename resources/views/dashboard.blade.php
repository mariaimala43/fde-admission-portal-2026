@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- HOI DASHBOARD                                       --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @role('hoi')

        @php
            $user = Auth::user();
            $institution = $user->institution?->load(['sector']);
        @endphp

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $institution?->name ?? 'My Dashboard' }}</h2>
                <p class="text-gray-500 text-sm mt-1">
                    {{ $institution?->sector?->name }} Sector
                    &nbsp;·&nbsp; {{ $institution?->type }}
                    &nbsp;·&nbsp; {{ ucfirst($institution?->shift) }}
                </p>
            </div>
            @if ($institution)
                @php
                    $dashAcademicYear = \App\Models\AcademicYear::where('is_active', true)->first();
                    $effectiveStatus = $institution->admission_status;
                    // Auto-open if institution is not_started but academic year window is active
                    if (!in_array($effectiveStatus, ['open', 'closed', 'by_approval']) && $dashAcademicYear) {
                        $today = now()->toDateString();
                        $start = $dashAcademicYear->admission_start
                            ? (is_string($dashAcademicYear->admission_start)
                                ? $dashAcademicYear->admission_start
                                : $dashAcademicYear->admission_start->toDateString())
                            : null;
                        $end = $dashAcademicYear->admission_end
                            ? (is_string($dashAcademicYear->admission_end)
                                ? $dashAcademicYear->admission_end
                                : $dashAcademicYear->admission_end->toDateString())
                            : null;
                        if ($start && $end && $today >= $start && $today <= $end) {
                            $effectiveStatus = 'open';
                        }
                    }
                @endphp
                <span
                    class="px-3 py-1 rounded-full text-xs font-semibold uppercase
                    {{ $effectiveStatus === 'open'
                        ? 'bg-green-100 text-green-700'
                        : ($effectiveStatus === 'closed'
                            ? 'bg-red-100 text-red-700'
                            : 'bg-yellow-100 text-yellow-700') }}">
                    Admissions: {{ ucfirst(str_replace('_', ' ', $effectiveStatus)) }}
                </span>
            @endif
        </div>

        @if ($institution)

       @php
            $classes = \App\Models\InstitutionClass::where('institution_id', $institution->id)
                ->where('is_active', true)
                ->with(['classModel'])
                ->orderBy('class_id')
                ->get();

            $sectionsMap = \App\Models\InstitutionSection::where('institution_id', $institution->id)
                ->orderBy('order')
                ->get()
                ->groupBy('class_id');

            $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();
            $hasEvening   = (bool) ($institution?->has_evening_classes ?? false);

            // ── All admissions per class — all types consume seats
            $morningAdmitted = \App\Models\DailyAdmission::where('institution_id', $institution->id)
                ->where('academic_year_id', $academicYear?->id)
                ->selectRaw('class_id, SUM(
                    morning_boys + morning_girls +
                    morning_oosc_boys + morning_oosc_girls +
                    morning_p2p_boys + morning_p2p_girls
                ) as total')
                ->groupBy('class_id')
                ->pluck('total', 'class_id');

            $eveningAdmitted = \App\Models\DailyAdmission::where('institution_id', $institution->id)
                ->where('academic_year_id', $academicYear?->id)
                ->selectRaw('class_id, SUM(
                    evening_boys + evening_girls +
                    evening_oosc_boys + evening_oosc_girls +
                    evening_p2p_boys + evening_p2p_girls
                ) as total')
                ->groupBy('class_id')
                ->pluck('total', 'class_id');

            // ── Totals
            $totalSeats           = $classes->sum('total_seats');
            $totalEnrolled        = $classes->sum('existing_enrollment');
            $totalMorningAdmit    = $morningAdmitted->sum();
            $totalEveningAdmit    = $eveningAdmitted->sum();
            $totalNewAdmit        = $totalMorningAdmit + $totalEveningAdmit;

            // ── Available seats
            // Evening school: use per-shift columns when set; otherwise fall back to
            // combined totals (school enabled evening but never re-did Class Setup).
            // Morning-only school: always use combined totals.
            if ($hasEvening) {
                $rawMorningSeats    = $classes->sum('morning_seats');
                $rawEveningSeats    = $classes->sum('evening_seats');
                $rawMorningExisting = $classes->sum('morning_existing');
                $rawEveningExisting = $classes->sum('evening_existing');

                $perShiftSeatsSet   = ($rawMorningSeats > 0 || $rawEveningSeats > 0);
                $perShiftExistSet   = ($rawMorningExisting > 0 || $rawEveningExisting > 0);

                $totalMorningSeats    = $perShiftSeatsSet ? $rawMorningSeats   : $totalSeats;
                $totalEveningSeats    = $perShiftSeatsSet ? $rawEveningSeats   : 0;
                $totalMorningExisting = $perShiftExistSet ? $rawMorningExisting : $totalEnrolled;
                $totalEveningExisting = $perShiftExistSet ? $rawEveningExisting : 0;

                // All types consume seats
                $totalMorningAvailable = max(0, $totalMorningSeats - $totalMorningExisting - $totalMorningAdmit);
                $totalEveningAvailable = max(0, $totalEveningSeats - $totalEveningExisting - $totalEveningAdmit);
                $totalAvailable        = $totalMorningAvailable + $totalEveningAvailable;

                $totalMorningEnrollment = $totalMorningExisting + $totalMorningAdmit;
                $totalEveningEnrollment = $totalEveningExisting + $totalEveningAdmit;
            } else {
                // Morning-only — all types consume seats
                $totalAvailable = max(0, $totalSeats - $totalEnrolled - $totalMorningAdmit);

                $totalMorningSeats      = $totalSeats;
                $totalEveningSeats      = 0;
                $totalMorningExisting   = $totalEnrolled;
                $totalEveningExisting   = 0;
                $totalMorningAvailable  = $totalAvailable;
                $totalEveningAvailable  = 0;
                $totalMorningEnrollment = $totalEnrolled + $totalMorningAdmit;
                $totalEveningEnrollment = 0;
            }

            // ── Today's admissions
            $todayMorning = \App\Models\DailyAdmission::where('institution_id', $institution->id)
                ->where('admission_date', now()->toDateString())
                ->selectRaw('SUM(
                    morning_boys + morning_girls +
                    morning_oosc_boys + morning_oosc_girls +
                    morning_p2p_boys + morning_p2p_girls
                ) as total')
                ->value('total') ?? 0;

            $todayEvening = \App\Models\DailyAdmission::where('institution_id', $institution->id)
                ->where('admission_date', now()->toDateString())
                ->selectRaw('SUM(
                    evening_boys + evening_girls +
                    evening_oosc_boys + evening_oosc_girls +
                    evening_p2p_boys + evening_p2p_girls
                ) as total')
                ->value('total') ?? 0;

            $todayTotal = $todayMorning + $todayEvening;

            // ── Matric Tech (only for schools that have it enabled)
            $matricTechExisting = 0;
            $matricTechYear     = 0;
            $matricTechToday    = 0;
            if ($institution->has_matric_tech) {
                // Baseline: existing Matric Tech students (Class 9 & 10) from institution_classes
                $matricTechExisting = (int) \App\Models\InstitutionClass::where('institution_id', $institution->id)
                    ->whereHas('classModel', fn($q) => $q->whereIn('order', [9, 10]))
                    ->sum('matric_tech_existing');

                // New admissions this academic year
                $matricTechYear  = (int) \App\Models\DailyAdmission::where('institution_id', $institution->id)
                    ->where('academic_year_id', $academicYear?->id)
                    ->sum('matric_tech_count');

                // New admissions today
                $matricTechToday = (int) \App\Models\DailyAdmission::where('institution_id', $institution->id)
                    ->where('admission_date', now()->toDateString())
                    ->sum('matric_tech_count');
            }
        @endphp

            {{-- ── Quick Stats Row 1: Capacity overview ───────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Intake Capacity</p>
                    <p class="text-2xl font-bold text-blue-900">{{ number_format($totalSeats) }}</p>
                    @if ($hasEvening)
                        <p class="text-xs text-gray-400 mt-1">
                            <span class="text-blue-600 font-semibold">Morning {{ number_format($totalMorningSeats) }}</span>
                            <span class="text-gray-300">&middot;</span>
                            <span class="text-indigo-600 font-semibold">Evening {{ number_format($totalEveningSeats) }}</span>
                        </p>
                    @endif
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-orange-100 p-5 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Existing Enrollment</p>
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($totalEnrolled) }}</p>
                    @if ($hasEvening)
                        <p class="text-xs text-gray-400 mt-1">
                            <span class="text-blue-600 font-semibold">Morning {{ number_format($totalMorningExisting) }}</span>
                            <span class="text-gray-300">&middot;</span>
                            <span class="text-indigo-600 font-semibold">Evening {{ number_format($totalEveningExisting) }}</span>
                        </p>
                    @endif
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-green-100 p-5 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Seats Available</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($totalAvailable) }}</p>
                    @if ($hasEvening)
                        <p class="text-xs text-gray-400 mt-1">
                            <span class="text-blue-600 font-semibold">Morning {{ number_format($totalMorningAvailable) }}</span>
                            <span class="text-gray-300">&middot;</span>
                            <span class="text-indigo-600 font-semibold">Evening {{ number_format($totalEveningAvailable) }}</span>
                        </p>
                    @endif
                </div>
                <div class="bg-blue-900 rounded-xl shadow-sm p-5 text-center">
                    <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Total Enrollment</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($totalEnrolled + $totalNewAdmit) }}</p>
                    @if ($hasEvening)
                        <p class="text-xs text-blue-200 mt-1">
                            <span class="font-semibold">Morning {{ number_format($totalMorningEnrollment) }}</span>
                            <span class="text-blue-300">&middot;</span>
                            <span class="font-semibold">Evening {{ number_format($totalEveningEnrollment) }}</span>
                        </p>
                    @endif
                </div>
            </div>

            {{-- ── Quick Stats Row 2: Admission breakdown ──────────── --}}
            @if ($hasEvening)
                {{-- Evening school: show per-shift breakdown --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">🌅 Morning Admitted</p>
                        <p class="text-2xl font-bold text-blue-700">{{ number_format($totalMorningAdmit) }}</p>
                        <p class="text-xs text-gray-400 mt-1">Cumulative</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-indigo-100 p-5 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">🌆 Evening Admitted</p>
                        <p class="text-2xl font-bold text-indigo-700">{{ number_format($totalEveningAdmit) }}</p>
                        <p class="text-xs text-gray-400 mt-1">Cumulative</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">🌅 Today Morning</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($todayMorning) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ now()->format('d M Y') }}</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-indigo-100 p-5 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">🌆 Today Evening</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ number_format($todayEvening) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ now()->format('d M Y') }}</p>
                    </div>
                </div>
            @else
                {{-- Morning-only school: show cumulative + today totals --}}
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">📋 Total Admitted</p>
                        <p class="text-2xl font-bold text-blue-700">{{ number_format($totalMorningAdmit) }}</p>
                        <p class="text-xs text-gray-400 mt-1">Cumulative this year</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">📝 Today's Admissions</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($todayMorning) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ now()->format('d M Y') }}</p>
                    </div>
                </div>
            @endif

            {{-- ── Matric Tech Row (only for schools with matric tech) ─ --}}
            @if ($institution->has_matric_tech)
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-teal-200 p-5 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">⚙️ Matric Tech Existing</p>
                        <p class="text-2xl font-bold text-teal-700">{{ number_format($matricTechExisting) }}</p>
                        <p class="text-xs text-gray-400 mt-1">Previous year baseline</p>
                    </div>
                    <div class="bg-teal-700 rounded-xl shadow-sm p-5 text-center text-white">
                        <p class="text-xs text-teal-100 uppercase tracking-wider mb-1">⚙️ Admitted This Year</p>
                        <p class="text-2xl font-bold">{{ number_format($matricTechYear) }}</p>
                        <p class="text-xs text-teal-200 mt-1">
                            Today: {{ number_format($matricTechToday) }}
                        </p>
                        <p class="text-xs text-teal-300 mt-1 italic">Subset of New Students above</p>
                    </div>
                    <div class="bg-teal-900 rounded-xl shadow-sm p-5 text-center text-white">
                        <p class="text-xs text-teal-200 uppercase tracking-wider mb-1">⚙️ Total Matric Tech</p>
                        <p class="text-2xl font-bold">{{ number_format($matricTechExisting + $matricTechYear) }}</p>
                        <p class="text-xs text-teal-300 mt-1">Existing + This Year</p>
                    </div>
                </div>
            @endif

            {{-- ── Class-wise Table ─────────────────────────────────── --}}
            @if ($classes->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Class-wise Enrollment Summary</h3>
                        <a href="{{ route('hoi.classes.setup') }}" class="text-xs text-blue-600 hover:underline">Edit
                            Classes</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left">Class</th>
                                    <th class="px-4 py-3 text-center">Sections</th>
                                    <th class="px-4 py-3 text-center">Existing<br>Enrollment</th>
                                    <th class="px-4 py-3 text-center">Intake<br>Capacity</th>
                                    <th class="px-4 py-3 text-center text-green-600">Seats<br>Available</th>
                                    @if ($hasEvening)
                                        <th class="px-4 py-3 text-center text-blue-600 bg-blue-50">🌅 Morning<br>Admitted</th>
                                        <th class="px-4 py-3 text-center text-indigo-600 bg-indigo-50">🌆 Evening<br>Admitted</th>
                                    @else
                                        <th class="px-4 py-3 text-center text-blue-600 bg-blue-50">Newly<br>Admitted</th>
                                    @endif
                                    <th class="px-4 py-3 text-center text-blue-900 bg-blue-100">Total<br>Enrollment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($classes as $ic)
                                    @php
                                        $secs       = $sectionsMap[$ic->class_id] ?? collect();
                                        $secCount   = $secs->count();
                                        $morningAmt = $morningAdmitted[$ic->class_id] ?? 0;
                                        $eveningAmt = $eveningAdmitted[$ic->class_id] ?? 0;

                                        if ($hasEvening) {
                                            // Fall back to combined cols when per-shift was never entered
                                            $rowPerShiftSeats = ($ic->morning_seats > 0 || $ic->evening_seats > 0);
                                            $rowMSeats = $rowPerShiftSeats ? ($ic->morning_seats ?? 0) : $ic->total_seats;
                                            $rowESeats = $rowPerShiftSeats ? ($ic->evening_seats ?? 0) : 0;

                                            $rowPerShiftExist = ($ic->morning_existing > 0 || $ic->evening_existing > 0);
                                            $rowMExist = $rowPerShiftExist ? ($ic->morning_existing ?? 0) : $ic->existing_enrollment;
                                            $rowEExist = $rowPerShiftExist ? ($ic->evening_existing ?? 0) : 0;

                                            $morningAvailable = max(0, $rowMSeats - $rowMExist - $morningAmt);
                                            $eveningAvailable = max(0, $rowESeats - $rowEExist - $eveningAmt);
                                            $available        = $morningAvailable + $eveningAvailable;
                                        } else {
                                            $rowMExist        = $ic->existing_enrollment;
                                            $rowEExist        = 0;
                                            $available        = max(0, $ic->total_seats - $ic->existing_enrollment - $morningAmt);
                                            $morningAvailable = $available;
                                            $eveningAvailable = 0;
                                        }

                                        $totalEnrl        = $ic->existing_enrollment + $morningAmt + $eveningAmt;
                                        $morningTotalEnrl = $rowMExist + $morningAmt;
                                        $eveningTotalEnrl = $rowEExist + $eveningAmt;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-gray-800">
                                            {{ $ic->classModel?->name }}
                                            @if ($ic->classModel?->is_ece)
                                                <span
                                                    class="ml-1 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">ECE</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600">
                                            {{ max(1, $secCount) }}
                                            @if ($secs->isNotEmpty())
                                                <div class="text-xs text-gray-400">({{ $secs->pluck('name')->join(', ') }})
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-orange-600 font-medium">
                                            {{ number_format($ic->existing_enrollment) }}
                                            @if ($hasEvening)
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    <span class="text-blue-600 font-semibold">M: {{ number_format($rowMExist) }}</span>
                                                    <span class="text-gray-300">&middot;</span>
                                                    <span class="text-indigo-600 font-semibold">E: {{ number_format($rowEExist) }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center font-medium text-gray-700">
                                            {{ number_format($ic->total_seats) }}
                                            @if ($hasEvening)
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    <span class="text-blue-600 font-semibold">M: {{ number_format($rowMSeats) }}</span>
                                                    <span class="text-gray-300">&middot;</span>
                                                    <span class="text-indigo-600 font-semibold">E: {{ number_format($rowESeats) }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td
                                            class="px-4 py-3 text-center font-bold {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                            {{ number_format($available) }}
                                            @if ($hasEvening)
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    <span class="text-blue-600 font-semibold">M: {{ number_format($morningAvailable) }}</span>
                                                    <span class="text-gray-300">&middot;</span>
                                                    <span class="text-indigo-600 font-semibold">E: {{ number_format($eveningAvailable) }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        @if ($hasEvening)
                                            <td class="px-4 py-3 text-center font-medium text-blue-700 bg-blue-50">
                                                {{ number_format($morningAmt) }}
                                            </td>
                                            <td class="px-4 py-3 text-center font-medium text-indigo-700 bg-indigo-50">
                                                {{ number_format($eveningAmt) }}
                                            </td>
                                        @else
                                            <td class="px-4 py-3 text-center font-medium text-blue-700 bg-blue-50">
                                                {{ number_format($morningAmt) }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-3 text-center font-bold text-blue-900 bg-blue-100">
                                            {{ number_format($totalEnrl) }}
                                            @if ($hasEvening)
                                                <div class="text-xs text-blue-300 mt-0.5">
                                                    <span class="font-semibold">M: {{ number_format($morningTotalEnrl) }}</span>
                                                    <span class="text-blue-200">&middot;</span>
                                                    <span class="font-semibold">E: {{ number_format($eveningTotalEnrl) }}</span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">TOTAL</td>
                                    <td class="px-4 py-3 text-center text-gray-600">
                                        {{ $classes->sum(fn($ic) => max(1, ($sectionsMap[$ic->class_id] ?? collect())->count())) }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-orange-600">{{ number_format($totalEnrolled) }}</td>
                                    <td class="px-4 py-3 text-center text-blue-900">{{ number_format($totalSeats) }}</td>
                                    <td
                                        class="px-4 py-3 text-center {{ $totalAvailable > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($totalAvailable) }}
                                        @if ($hasEvening)
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                <span class="text-blue-600 font-semibold">M: {{ number_format($totalMorningAvailable) }}</span>
                                                <span class="text-gray-300">&middot;</span>
                                                <span class="text-indigo-600 font-semibold">E: {{ number_format($totalEveningAvailable) }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    @if ($hasEvening)
                                        <td class="px-4 py-3 text-center text-blue-700 bg-blue-50">
                                            {{ number_format($totalMorningAdmit) }}</td>
                                        <td class="px-4 py-3 text-center text-indigo-700 bg-indigo-50">
                                            {{ number_format($totalEveningAdmit) }}</td>
                                    @else
                                        <td class="px-4 py-3 text-center text-blue-700 bg-blue-50">
                                            {{ number_format($totalMorningAdmit) }}</td>
                                    @endif
                                    <td class="px-4 py-3 text-center text-blue-900 bg-blue-100">
                                        {{ number_format($totalEnrolled + $totalNewAdmit) }}
                                        @if ($hasEvening)
                                            <div class="text-xs text-blue-300 mt-0.5">
                                                <span class="font-semibold">M: {{ number_format($totalMorningEnrollment) }}</span>
                                                <span class="text-blue-200">&middot;</span>
                                                <span class="font-semibold">E: {{ number_format($totalEveningEnrollment) }}</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- ── Action Cards ─────────────────────────────────── --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <a href="{{ route('hoi.enrollment.index') }}"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-3">
                            <p class="text-sm text-gray-500">Baseline Enrollment</p>
                            @if ($totalEnrolled > 0)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Entered</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full">Pending</span>
                            @endif
                        </div>
                        <p class="text-3xl font-bold text-blue-900 mb-1">{{ number_format($totalEnrolled) }}</p>
                        <p class="text-xs text-blue-600 font-medium">Enter / Update</p>
                    </a>

                    <a href="{{ route('hoi.admissions.daily') }}"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <p class="text-sm text-gray-500 mb-1">Today's Admissions</p>
                        <p class="text-3xl font-bold text-blue-900 mb-2">{{ number_format($todayTotal) }}</p>
                        @if ($hasEvening)
                            <div class="flex items-center gap-3 text-xs">
                                <span class="text-blue-600 font-semibold">🌅 {{ number_format($todayMorning) }} Morning</span>
                                <span class="text-gray-300">·</span>
                                <span class="text-indigo-600 font-semibold">🌆 {{ number_format($todayEvening) }} Evening</span>
                            </div>
                        @endif
                        <p class="text-xs text-blue-600 font-medium mt-2">Enter Admissions</p>
                    </a>

                    <a href="{{ route('hoi.admissions.report') }}"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <p class="text-sm text-gray-500 mb-1">Cumulative Admissions</p>
                        <p class="text-3xl font-bold text-blue-900 mb-2">{{ number_format($totalNewAdmit) }}</p>
                        @if ($hasEvening)
                            <div class="flex items-center gap-3 text-xs">
                                <span class="text-blue-600 font-semibold">🌅 {{ number_format($totalMorningAdmit) }} Morning</span>
                                <span class="text-gray-300">·</span>
                                <span class="text-indigo-600 font-semibold">🌆 {{ number_format($totalEveningAdmit) }} Evening</span>
                            </div>
                        @endif
                        <p class="text-xs text-blue-600 font-medium mt-2">View Report</p>
                    </a>

                </div>

                {{--
                <div class="mt-4">
                    <a href="{{ route('hoi.quota.index') }}"
                        class="inline-flex items-center gap-2 text-sm text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-4 py-2.5 transition font-medium">
                        🎯 Set Admission Quota per Class
                        <span class="text-xs text-blue-400 font-normal">(max students you plan to admit this year)</span>
                    </a>
                </div>
                --}}
            @else
                {{-- No classes configured yet --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                    <p class="text-yellow-800 font-medium mb-3">Classes not configured yet.</p>
                    <a href="{{ route('hoi.classes.setup') }}"
                        class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Setup Classes Now
                    </a>
                </div>
            @endif
        @else
            {{-- No institution --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                <p class="text-yellow-800 font-medium mb-3">School profile not set up yet.</p>
                <a href="{{ route('hoi.profile.setup') }}"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Setup Profile Now
                </a>
            </div>
        @endif

    @endrole

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- DIRECTOR DASHBOARD (view-only)                      --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @role('director')
        <h2 class="text-2xl font-bold text-gray-800 mb-1">Director Dashboard</h2>
        <p class="text-gray-500 text-sm mb-8">Academic Year 2026-27</p>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-gray-500 text-sm">Director features coming soon.</p>
        </div>
    @endrole

@endsection
