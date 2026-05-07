@extends('layouts.app')
@section('title', $institution->name . ' - Report')
@section('content')

    @php
        $isDirector = auth()->user()->hasRole('director');
        $indexRoute = $isDirector ? route('director.schools.index') : route('fde.schools.index');
        $showRoute = $isDirector
            ? route('director.schools.show', $institution)
            : route('fde.schools.show', $institution);
    @endphp

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-start gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $institution->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->sector?->name }} Sector
                &nbsp;&middot;&nbsp; {{ $institution->type }}
                &nbsp;&middot;&nbsp; {{ ucfirst(str_replace('_', ' ', $institution->gender)) }}
                &nbsp;&middot;&nbsp; {{ ucfirst($institution->shift) }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @role('director')
                <a href="{{ $indexRoute }}" class="text-sm text-blue-600 hover:underline">← All Schools</a>
            @else
                <a href="{{ $indexRoute }}" class="text-sm text-blue-600 hover:underline">← All Schools</a>
                <a href="{{ route('fde.enrollment.show', $institution) }}"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">
                    🔓 Enrollment Override
                </a>
                <button type="button" onclick="document.getElementById('resetModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                    🗑️ Reset Admission Data
                </button>
            @endrole
        </div>
    </div>

    {{-- ── Reset Confirmation Modal (FDE only) ────────────────────────── --}}
    @role('fde_cell')
        <div id="resetModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-start gap-3 mb-4">
                    <span class="text-3xl">⚠️</span>
                    <div>
                        <h3 class="text-lg font-bold text-red-700">Reset Admission Data</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            This will permanently delete <strong>all daily admission records</strong> for
                            <strong>{{ $institution->name }}</strong> in the current academic year
                            ({{ $academicYear?->name ?? 'active year' }}).
                            The school will be able to re-enter their data from scratch.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('fde.schools.reset-admissions', $institution) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                            Reason <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input type="text" name="reason" maxlength="255"
                            placeholder="e.g. School submitted incorrect figures"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>

                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                            Type <span class="font-mono font-bold text-red-600">RESET</span> to confirm
                        </label>
                        <input type="text" name="confirmation" autocomplete="off" placeholder="RESET"
                            class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-red-400">
                        @error('confirmation')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-red-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-red-700 transition">
                            Yes, Delete All Records
                        </button>
                        <button type="button" onclick="document.getElementById('resetModal').classList.add('hidden')"
                            class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endrole

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- ── Date Range Filter ───────────────────────────────────────────── --}}
    <form method="GET" action="{{ $showRoute }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <button type="submit"
                class="bg-blue-900 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                Apply
            </button>
        </div>
    </form>

    {{-- ── Grand Totals (date-range newly admitted) ────────────────────── --}}
    @php
        $dateRangeMtNew = $hasMatricTech ? (int) $classSummary->sum('matric_tech_count') : 0;
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-2 {{ $hasMatricTech ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Newly Admitted</p>
            <p class="text-3xl font-bold text-blue-900">{{ number_format($grandTotal) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $from->format('d M') }} – {{ $to->format('d M Y') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Regular</p>
            <p class="text-3xl font-bold text-blue-700">{{ number_format($grandRegular) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-purple-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">OOSC</p>
            <p class="text-3xl font-bold text-purple-700">{{ number_format($grandOosc) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Private to Government (P2G)</p>
            <p class="text-3xl font-bold text-orange-600">{{ number_format($grandP2p) }}</p>
        </div>
        @if ($hasMatricTech)
            <div class="bg-teal-50 rounded-xl border border-teal-200 shadow-sm p-5 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">⚙️ Matric Tech</p>
                <p class="text-3xl font-bold text-teal-700">{{ number_format($dateRangeMtNew) }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    Base: {{ number_format($classes->filter(fn($ic) => in_array($ic->classModel?->order, [9,10]))->sum('matric_tech_existing')) }}
                    &middot; Year Total: {{ number_format($grandMatricTech) }}
                </p>
            </div>
        @endif
    </div>

    {{-- ── Class-wise Summary ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6"
        @if ($hasEvening) x-data="{ shift: 'both' }" @endif>

        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">Class-wise Summary</h3>
            @if ($hasEvening)
                <div class="flex items-center gap-1 text-xs">
                    <button @click="shift = 'both'"
                        :class="shift === 'both' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1.5 rounded-l-lg font-medium transition">Both</button>
                    <button @click="shift = 'morning'"
                        :class="shift === 'morning' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1.5 font-medium transition">Morning</button>
                    <button @click="shift = 'evening'"
                        :class="shift === 'evening' ? 'bg-indigo-700 text-white' :
                            'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1.5 rounded-r-lg font-medium transition">Evening</button>
                </div>
            @endif
        </div>

        <p class="block sm:hidden text-xs text-gray-400 mb-2 px-4 pt-2 flex items-center gap-1">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            Swipe right to see all columns
        </p>

        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-3 text-left text-gray-500">Class</th>
                        <th class="px-3 py-3 text-center text-gray-500 hidden sm:table-cell">Sections</th>
                        <th class="px-3 py-3 text-center text-gray-500 hidden md:table-cell">Intake Capacity</th>
                        <th class="px-3 py-3 text-center text-gray-500 hidden sm:table-cell">Promoted Students</th>
                        <th class="px-3 py-3 text-center text-gray-500">Newly Admitted</th>
                        @if ($hasMatricTech)
                            <th class="px-3 py-3 text-center text-purple-600 hidden sm:table-cell">Matric Tech</th>
                        @endif
                        <th class="px-3 py-3 text-center text-gray-500 hidden md:table-cell">Seats Available</th>
                        <th class="px-3 py-3 text-center text-gray-500 bg-blue-50">Current Enrollment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($classes as $ic)
                        @php
                            // All per-class computed values come from the controller's $classStats.
// This keeps the blade free of complex seat-split math.
$s = $classSummary[$ic->class_id] ?? null;
$sMorning = $classSummaryMorning[$ic->class_id] ?? null;
$sEvening = $classSummaryEvening[$ic->class_id] ?? null;
$stat = $classStats[$ic->class_id] ?? null;

// Date-range admitted totals (for "Newly Admitted" column)
$admitted = $s?->total ?? 0;
$admMorning = $sMorning?->total ?? 0;
$admEvening = $sEvening?->total ?? 0;

// Seat / enrollment values (full academic year)
$available = $stat['available'] ?? 0;
$availableMorning = $stat['availableMorning'] ?? 0;
$availableEvening = $stat['availableEvening'] ?? 0;
$totalEnrl = $stat['totalEnrl'] ?? 0;
$totalMorning = $stat['totalMorning'] ?? 0;
$totalEvening = $stat['totalEvening'] ?? 0;
$mSeats = $stat['mSeats'] ?? 0;
$eSeats = $stat['eSeats'] ?? 0;

                            // ECE: flag only — same seat formula applies
                            $isEce = (bool) $ic->classModel?->is_ece;
                        @endphp
                        <tr class="hover:bg-gray-50">

                            {{-- Class name --}}
                            <td class="px-3 py-3 whitespace-nowrap font-semibold text-gray-900">
                                {{ $ic->classModel?->name }}
                                @if ($isEce)
                                    <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 rounded-full">ECE</span>
                                @endif
                            </td>

                            {{-- Sections --}}
                            <td class="px-3 py-3 text-center text-gray-700 font-medium hidden sm:table-cell">
                                {{ $sectionCounts[$ic->class_id]->count ?? 0 }}
                            </td>

                            {{-- Intake Capacity --}}
                            <td class="px-3 py-3 text-center text-gray-700 font-medium hidden md:table-cell">
                                @if ($hasEvening)
                                    <span x-show="shift === 'both'">{{ number_format($ic->total_seats) }}</span>
                                    <span x-show="shift === 'morning'" x-cloak>{{ number_format($mSeats) }}</span>
                                    <span x-show="shift === 'evening'" x-cloak>{{ number_format($eSeats) }}</span>
                                @else
                                    {{ number_format($ic->total_seats) }}
                                @endif
                            </td>

                            {{-- Promoted Students (existing enrollment at year start) --}}
                            <td class="px-3 py-3 text-center hidden sm:table-cell">
                                @if ($hasEvening)
                                    {{-- Both --}}
                                    <span x-show="shift === 'both'">
                                        <div class="font-bold text-orange-600 text-base">
                                            {{ number_format($ic->existing_enrollment) }}</div>
                                        @if ($ic->promoted_count + $ic->failed_count > 0)
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                Promoted: <span
                                                    class="text-green-600 font-semibold">{{ number_format($ic->promoted_count) }}</span>
                                                @if ($ic->failed_count > 0)
                                                    &nbsp;&middot;&nbsp;
                                                    Repeaters: <span
                                                        class="text-red-500 font-semibold">{{ number_format($ic->failed_count) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </span>
                                    {{-- Morning --}}
                                    <span x-show="shift === 'morning'" x-cloak>
                                        <div class="font-bold text-orange-600 text-base">
                                            {{ number_format($ic->morning_existing ?? 0) }}</div>
                                        @if (($ic->morning_promoted ?? 0) + ($ic->morning_failed ?? 0) > 0)
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                Promoted: <span
                                                    class="text-green-600 font-semibold">{{ number_format($ic->morning_promoted ?? 0) }}</span>
                                                @if (($ic->morning_failed ?? 0) > 0)
                                                    &nbsp;&middot;&nbsp;
                                                    Repeaters: <span
                                                        class="text-red-500 font-semibold">{{ number_format($ic->morning_failed ?? 0) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </span>
                                    {{-- Evening --}}
                                    <span x-show="shift === 'evening'" x-cloak>
                                        <div class="font-bold text-orange-600 text-base">
                                            {{ number_format($ic->evening_existing ?? 0) }}</div>
                                        @if (($ic->evening_promoted ?? 0) + ($ic->evening_failed ?? 0) > 0)
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                Promoted: <span
                                                    class="text-green-600 font-semibold">{{ number_format($ic->evening_promoted ?? 0) }}</span>
                                                @if (($ic->evening_failed ?? 0) > 0)
                                                    &nbsp;&middot;&nbsp;
                                                    Repeaters: <span
                                                        class="text-red-500 font-semibold">{{ number_format($ic->evening_failed ?? 0) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </span>
                                @else
                                    <div class="font-bold text-orange-600 text-base">
                                        {{ number_format($ic->existing_enrollment) }}</div>
                                    @if ($ic->promoted_count + $ic->failed_count > 0)
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            Promoted: <span
                                                class="text-green-600 font-semibold">{{ number_format($ic->promoted_count) }}</span>
                                            @if ($ic->failed_count > 0)
                                                &nbsp;&middot;&nbsp;
                                                Repeaters: <span
                                                    class="text-red-500 font-semibold">{{ number_format($ic->failed_count) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </td>

                            {{-- Newly Admitted (date-range filtered) --}}
                            <td class="px-3 py-3 text-center text-blue-700 font-bold">
                                @if ($hasEvening)
                                    <span x-show="shift === 'both'">{{ number_format($admitted) }}</span>
                                    <span x-show="shift === 'morning'" x-cloak>{{ number_format($admMorning) }}</span>
                                    <span x-show="shift === 'evening'" x-cloak>{{ number_format($admEvening) }}</span>
                                    @if ($admitted > 0)
                                        <div class="text-xs text-gray-400 font-normal" x-show="shift === 'both'">
                                            Reg: {{ number_format(($s?->reg_boys ?? 0) + ($s?->reg_girls ?? 0)) }}
                                            &middot; OOSC:
                                            {{ number_format(($s?->oosc_boys ?? 0) + ($s?->oosc_girls ?? 0)) }}
                                            &middot; P2G: {{ number_format(($s?->p2p_boys ?? 0) + ($s?->p2p_girls ?? 0)) }}
                                        </div>
                                        <div class="text-xs text-gray-400 font-normal" x-show="shift === 'morning'"
                                            x-cloak>
                                            Reg:
                                            {{ number_format(($sMorning?->reg_boys ?? 0) + ($sMorning?->reg_girls ?? 0)) }}
                                            &middot; OOSC:
                                            {{ number_format(($sMorning?->oosc_boys ?? 0) + ($sMorning?->oosc_girls ?? 0)) }}
                                            &middot; P2G:
                                            {{ number_format(($sMorning?->p2p_boys ?? 0) + ($sMorning?->p2p_girls ?? 0)) }}
                                        </div>
                                        <div class="text-xs text-gray-400 font-normal" x-show="shift === 'evening'"
                                            x-cloak>
                                            Reg:
                                            {{ number_format(($sEvening?->reg_boys ?? 0) + ($sEvening?->reg_girls ?? 0)) }}
                                            &middot; OOSC:
                                            {{ number_format(($sEvening?->oosc_boys ?? 0) + ($sEvening?->oosc_girls ?? 0)) }}
                                            &middot; P2G:
                                            {{ number_format(($sEvening?->p2p_boys ?? 0) + ($sEvening?->p2p_girls ?? 0)) }}
                                        </div>
                                    @endif
                                @else
                                    {{ number_format($admitted) }}
                                    @if ($admitted > 0)
                                        <div class="text-xs text-gray-400 font-normal">
                                            Reg: {{ number_format(($s?->reg_boys ?? 0) + ($s?->reg_girls ?? 0)) }}
                                            &middot; OOSC:
                                            {{ number_format(($s?->oosc_boys ?? 0) + ($s?->oosc_girls ?? 0)) }}
                                            &middot; P2G: {{ number_format(($s?->p2p_boys ?? 0) + ($s?->p2p_girls ?? 0)) }}
                                        </div>
                                    @endif
                                @endif
                            </td>

                            {{-- Matric Tech column --}}
                            @if ($hasMatricTech)
                                @php
                                    $isMatricClass = in_array($ic->classModel?->order, [9, 10]);
                                    $mtNew = $isMatricClass
                                        ? (int) ($classSummary[$ic->class_id]?->matric_tech_count ?? 0)
                                        : null;
                                    $mtBase = $isMatricClass ? (int) ($ic->matric_tech_existing ?? 0) : null;
                                    $mtTotal = $isMatricClass ? $mtNew + $mtBase : null;
                                @endphp
                                <td class="px-3 py-3 text-center hidden sm:table-cell">
                                    @if ($isMatricClass)
                                        <span class="font-bold text-purple-700">{{ number_format($mtTotal) }}</span>
                                        <div class="text-xs text-gray-400 font-normal mt-0.5">
                                            Base: {{ number_format($mtBase) }}
                                            &middot; New: {{ number_format($mtNew) }}
                                        </div>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            @endif

                            {{-- Seats Available --}}
                            {{--
                                FIX: values come from $classStats (pre-computed in controller).
                                Morning/Evening split uses explicit per-shift seat columns when
                                available, otherwise proportional split from yearly admitted ratio.
                            --}}
                            <td
                                class="px-3 py-3 text-center font-bold hidden md:table-cell
                                {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                @if ($hasEvening)
                                    <span x-show="shift === 'both'">{{ number_format($available) }}</span>
                                    <span x-show="shift === 'morning'" x-cloak
                                        class="{{ $availableMorning > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($availableMorning) }}
                                    </span>
                                    <span x-show="shift === 'evening'" x-cloak
                                        class="{{ $availableEvening > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($availableEvening) }}
                                    </span>
                                @else
                                    {{ number_format($available) }}
                                @endif
                            </td>

                            {{-- Current Enrollment (existing + full-year admitted) --}}
                            {{--
                                FIX: renamed from "Total" to "Current Enrollment" to distinguish
                                it clearly from the date-range "Newly Admitted" card at the top.
                            --}}
                            <td class="px-3 py-3 text-center font-bold text-blue-900 bg-blue-50">
                                @if ($hasEvening)
                                    <span x-show="shift === 'both'">{{ number_format($totalEnrl) }}</span>
                                    <span x-show="shift === 'morning'" x-cloak>{{ number_format($totalMorning) }}</span>
                                    <span x-show="shift === 'evening'" x-cloak>{{ number_format($totalEvening) }}</span>
                                @else
                                    {{ number_format($totalEnrl) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                {{-- ── Footer ──────────────────────────────────────────── --}}
                {{--
                    FIX: all footer values now come from $footerStats which is the
                    controller-computed sum of per-class clamped values.
                    This guarantees the footer matches the visible row values exactly.
                --}}
                <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                    <tr>
                        <td class="px-3 py-3 text-gray-700">TOTAL</td>

                        {{-- Sections total --}}
                        <td class="px-3 py-3 text-center text-gray-500 hidden sm:table-cell">
                            {{ $sectionCounts->sum('count') }}
                        </td>

                        {{-- Intake Capacity total --}}
                        <td class="px-3 py-3 text-center text-blue-900 hidden md:table-cell">
                            @if ($hasEvening)
                                <span x-show="shift === 'both'">{{ number_format($footerStats['totalSeats']) }}</span>
                                <span x-show="shift === 'morning'"
                                    x-cloak>{{ number_format($footerStats['totalSeatsM']) }}</span>
                                <span x-show="shift === 'evening'"
                                    x-cloak>{{ number_format($footerStats['totalSeatsE']) }}</span>
                            @else
                                {{ number_format($footerStats['totalSeats']) }}
                            @endif
                        </td>

                        {{-- Promoted Students total --}}
                        <td class="px-3 py-3 text-center text-orange-600 hidden sm:table-cell">
                            @if ($hasEvening)
                                <span x-show="shift === 'both'">{{ number_format($footerStats['totalExisting']) }}</span>
                                <span x-show="shift === 'morning'"
                                    x-cloak>{{ number_format($footerStats['totalExistingM']) }}</span>
                                <span x-show="shift === 'evening'"
                                    x-cloak>{{ number_format($footerStats['totalExistingE']) }}</span>
                            @else
                                {{ number_format($footerStats['totalExisting']) }}
                            @endif
                        </td>

                        {{-- Newly Admitted total (date-range) --}}
                        <td class="px-3 py-3 text-center text-blue-700">
                            @if ($hasEvening)
                                <span x-show="shift === 'both'">{{ number_format($grandTotal) }}</span>
                                <span x-show="shift === 'morning'"
                                    x-cloak>{{ number_format($classSummaryMorning->sum('total')) }}</span>
                                <span x-show="shift === 'evening'"
                                    x-cloak>{{ number_format($classSummaryEvening->sum('total')) }}</span>
                            @else
                                {{ number_format($grandTotal) }}
                            @endif
                        </td>

                        {{-- Matric Tech total --}}
                        @if ($hasMatricTech)
                            @php
                                $totalMtExisting = $classes
                                    ->filter(fn($ic) => in_array($ic->classModel?->order, [9, 10]))
                                    ->sum('matric_tech_existing');
                                // Date-range "New" matches the per-row values; full-year shown as note
                                $totalMtOverall = $dateRangeMtNew + $totalMtExisting;
                            @endphp
                            <td class="px-3 py-3 text-center text-purple-700 hidden sm:table-cell">
                                {{ number_format($totalMtOverall) }}
                                <div class="text-xs text-gray-400 font-normal mt-0.5">
                                    Base: {{ number_format($totalMtExisting) }}
                                    &middot; New: {{ number_format($dateRangeMtNew) }}
                                    @if ($grandMatricTech !== $dateRangeMtNew)
                                        <br><span class="text-teal-600">Year: {{ number_format($grandMatricTech) }}</span>
                                    @endif
                                </div>
                            </td>
                        @endif

                        {{-- Seats Available total --}}
                        {{--
                            FIX: uses $footerStats values which are sums of per-class
                            clamped max(0,...) values — consistent with what each row shows.
                        --}}
                        <td
                            class="px-3 py-3 text-center hidden md:table-cell
                            {{ $footerStats['totalAvailable'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                            @if ($hasEvening)
                                <span x-show="shift === 'both'">{{ number_format($footerStats['totalAvailable']) }}</span>
                                <span x-show="shift === 'morning'" x-cloak
                                    class="{{ $footerStats['totalAvailableMorn'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ number_format($footerStats['totalAvailableMorn']) }}
                                </span>
                                <span x-show="shift === 'evening'" x-cloak
                                    class="{{ $footerStats['totalAvailableEven'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ number_format($footerStats['totalAvailableEven']) }}
                                </span>
                            @else
                                {{ number_format($footerStats['totalAvailable']) }}
                            @endif
                        </td>

                        {{-- Current Enrollment total --}}
                        <td class="px-3 py-3 text-center text-blue-900 bg-blue-100">
                            @if ($hasEvening)
                                <span
                                    x-show="shift === 'both'">{{ number_format($footerStats['totalEnrollment']) }}</span>
                                <span x-show="shift === 'morning'"
                                    x-cloak>{{ number_format($footerStats['totalEnrollmentMorn']) }}</span>
                                <span x-show="shift === 'evening'"
                                    x-cloak>{{ number_format($footerStats['totalEnrollmentEven']) }}</span>
                            @else
                                {{ number_format($footerStats['totalEnrollment']) }}
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{--
    ── Admission Quota (hidden — keep for future re-enable) ──────────
    @php
        $totalQuota = $classes->sum('admission_quota');
        $totalAdmitted = $classSummary->sum('total');
        $totalRemain = $classes->sum(function ($ic) use ($classSummary) {
            if (!$ic->admission_quota) return 0;
            return max(0, $ic->admission_quota - ($classSummary[$ic->class_id]?->total ?? 0));
        });
    @endphp
    ... (quota table omitted for brevity — logic unchanged)
    --}}

    {{-- ── Day-by-Day Breakdown ────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Day-by-Day Breakdown</h3>
        </div>

        @if ($dailyRows->isEmpty())
            <div class="px-6 py-10 text-center text-gray-400 text-sm">
                No admissions in this date range.
            </div>
        @else
            <p class="block sm:hidden text-xs text-gray-400 mb-2 px-4 pt-2 flex items-center gap-1">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                Swipe right to see all columns
            </p>

            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wide">Date</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wide">Class</th>
                            <th
                                class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wide hidden sm:table-cell">
                                Reg Boys</th>
                            <th
                                class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wide hidden sm:table-cell">
                                Reg Girls</th>
                            <th
                                class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">
                                OOSC B</th>
                            <th
                                class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">
                                OOSC G</th>
                            <th
                                class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">
                                P2G B</th>
                            <th
                                class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">
                                P2G G</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wide">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($dailyRows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3 whitespace-nowrap text-gray-600">
                                    {{ $row->admission_date->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-800">
                                    {{ $row->classModel?->name }}
                                </td>

                                {{-- Reg Boys = morning + evening regular boys --}}
                                <td class="px-3 py-3 whitespace-nowrap text-center text-blue-700 hidden sm:table-cell">
                                    {{ $row->morning_boys + $row->evening_boys }}
                                </td>

                                {{-- Reg Girls = morning + evening regular girls --}}
                                <td class="px-3 py-3 whitespace-nowrap text-center text-pink-700 hidden sm:table-cell">
                                    {{ $row->morning_girls + $row->evening_girls }}
                                </td>

                                {{--
                                    FIX: the raw DailyAdmission model has no flat oosc_boys column.
                                    Real columns are morning_oosc_boys / evening_oosc_boys etc.
                                    Sum both shifts for the combined display.
                                --}}
                                <td class="px-3 py-3 whitespace-nowrap text-center text-purple-700 hidden md:table-cell">
                                    {{ $row->morning_oosc_boys + $row->evening_oosc_boys }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-center text-pink-700 hidden md:table-cell">
                                    {{ $row->morning_oosc_girls + $row->evening_oosc_girls }}
                                </td>

                                {{--
                                    FIX: same issue — p2p_boys / p2p_girls don't exist on the model.
                                    Correct columns are morning_p2p_boys / evening_p2p_boys etc.
                                    (displayed as P2G on front-end per business naming convention)
                                --}}
                                <td class="px-3 py-3 whitespace-nowrap text-center text-orange-700 hidden md:table-cell">
                                    {{ $row->morning_p2p_boys + $row->evening_p2p_boys }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-center text-pink-700 hidden md:table-cell">
                                    {{ $row->morning_p2p_girls + $row->evening_p2p_girls }}
                                </td>

                                <td class="px-3 py-3 whitespace-nowrap text-center font-bold text-gray-900">
                                    {{ $row->displayTotal() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    @role('fde_cell')
        @if ($errors->has('confirmation'))
            <script>
                document.getElementById('resetModal').classList.remove('hidden');
            </script>
        @endif
    @endrole
@endpush
