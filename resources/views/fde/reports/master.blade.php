@extends('layouts.app')
@section('title', 'Master Admission Report')
@section('content')

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Master Admission Report</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institutions->count() }} Schools
                &nbsp;·&nbsp; {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @php
                $dashRoute = ($exportPrefix ?? 'fde') === 'director' ? 'director.dashboard' : 'fde.dashboard';
            @endphp
            <a href="{{ route($dashRoute) }}" class="text-sm text-blue-600 hover:underline">← Dashboard</a>
            <a href="{{ route(($exportPrefix ?? 'fde').'.export.master') }}?{{ http_build_query(array_merge(request()->only(['sector_id', 'type', 'gender', 'class_level', 'from', 'to']), ['format' => 'excel'])) }}"
                class="px-4 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-lg hover:bg-green-600 transition">
                📊 Export Excel
            </a>
            <a href="{{ route(($exportPrefix ?? 'fde').'.export.master') }}?{{ http_build_query(array_merge(request()->only(['sector_id', 'type', 'gender', 'class_level', 'from', 'to']), ['format' => 'pdf'])) }}"
                class="px-4 py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                📥 Export PDF
            </a>
        </div>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route(($exportPrefix ?? 'fde').'.reports.master') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-7 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sector</label>
                <select name="sector_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Sectors</option>
                    @foreach ($sectors as $s)
                        <option value="{{ $s->id }}" {{ $sectorId == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Type</label>
                <select name="type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach (['I-V', 'I-VIII', 'I-X', 'I-XII', 'VI-VIII', 'VI-X', 'VI-XII', 'XI-XII', 'XI-XIV', 'Model College'] as $t)
                        <option value="{{ $t }}" {{ $type == $t ? 'selected' : '' }}>{{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Gender</label>
                <select name="gender"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="boys" {{ $gender == 'boys' ? 'selected' : '' }}>Boys</option>
                    <option value="girls" {{ $gender == 'girls' ? 'selected' : '' }}>Girls</option>
                    <option value="co_education" {{ $gender == 'co_education' ? 'selected' : '' }}>Co-Education</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Classes</label>
                <select name="class_level"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Classes</option>
                    <option value="non_ece" {{ ($classLevel ?? '') === 'non_ece' ? 'selected' : '' }}>Exclude ECE</option>
                    <option value="ece" {{ ($classLevel ?? '') === 'ece' ? 'selected' : '' }}>ECE Only</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Filter
                </button>
                <a href="{{ route(($exportPrefix ?? 'fde').'.reports.master') }}"
                    class="px-3 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- ── Grand Summary Cards ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-4 md:grid-cols-8 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center col-span-2 md:col-span-1">
            <p class="text-xs text-gray-400 uppercase mb-1">Total Seats</p>
            <p class="text-xl font-bold text-blue-900">{{ number_format($grand['seats']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-4 text-center col-span-2 md:col-span-1">
            <p class="text-xs text-gray-400 uppercase mb-1">Existing</p>
            <p class="text-xl font-bold text-orange-600">{{ number_format($grand['existing']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-4 text-center col-span-2 md:col-span-1">
            <p class="text-xs text-gray-400 uppercase mb-1">Regular</p>
            <p class="text-xl font-bold text-blue-700">{{ number_format($grand['regular']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-purple-100 shadow-sm p-4 text-center col-span-2 md:col-span-1">
            <p class="text-xs text-gray-400 uppercase mb-1">OOSC</p>
            <p class="text-xl font-bold text-purple-700">{{ number_format($grand['oosc']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-4 text-center col-span-2 md:col-span-1">
            <p class="text-xs text-gray-400 uppercase mb-1">P2G</p>
            <p class="text-xl font-bold text-orange-600">{{ number_format($grand['p2p']) }}</p>
        </div>
        <div class="bg-blue-900 rounded-xl shadow-sm p-4 text-center col-span-2 md:col-span-1">
            <p class="text-xs text-blue-200 uppercase mb-1">Total Admitted</p>
            <p class="text-xl font-bold text-white">{{ number_format($grand['admitted']) }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl shadow-sm p-4 text-center col-span-2 md:col-span-1">
            <p class="text-xs text-gray-300 uppercase mb-1">Total Filled</p>
            <p class="text-xl font-bold text-white">{{ number_format($grand['filled']) }}</p>
        </div>
        <div
            class="{{ $grand['remaining'] > 0 ? 'bg-green-600' : 'bg-red-600' }} rounded-xl shadow-sm p-4 text-center col-span-2 md:col-span-1">
            <p class="text-xs text-white/70 uppercase mb-1">Available Seats</p>
            <p class="text-xl font-bold text-white">{{ number_format($grand['remaining']) }}</p>
        </div>
    </div>

    {{-- ── Highlight Legend ─────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3 mb-3 text-xs">
        <span class="text-gray-400 dark:text-gray-500 font-semibold uppercase tracking-wider">Highlights:</span>
        @foreach (['Class 1', 'Class 6', 'ECE I', 'ECE II'] as $hl)
            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 border border-amber-300 dark:border-amber-600 text-amber-800 dark:text-amber-300 font-semibold">
                <span class="w-2 h-2 rounded-full bg-amber-400 dark:bg-amber-500 inline-block"></span> {{ $hl }}
            </span>
        @endforeach
    </div>

    {{-- ── Section 1: Overall Class Summary ────────────────────────────────────── --}}
    <p class="block md:hidden text-xs text-gray-500 mb-2">Scroll right to see all columns, or view on a larger screen for
        full detail.</p>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-blue-50">
            <h3 class="text-sm font-bold text-blue-900">
                Section 1 — Overall Class Summary (All {{ $institutions->count() }} Schools Combined)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Schools</th>
                        <th class="px-4 py-3 text-center">Intake Capacity</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Promoted Students</th>
                        <th class="px-4 py-3 text-center text-green-600">Seats Available</th>
                        <th class="px-4 py-3 text-center text-blue-600">Newly Admitted</th>
                        <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Capacity</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Fill Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($overallByClass as $row)
                        @php
                            $fillRate =
                                $row['total_seats'] > 0 ? round(($row['total_filled'] / $row['total_seats']) * 100) : 0;

                            $cn = strtolower(trim($row['class']->name ?? ''));

                            // Class 1 — amber
                            $isClass1 = in_array($cn, ['class 1', 'class i', '1', 'one']);
                            // Class 6 — emerald
                            $isClass6 = in_array($cn, ['class 6', 'class vi', '6', 'six']);
                            // ECE I — purple
                            $isEce1 = in_array($cn, ['ece i', 'ece 1', 'ece-i', 'ece-1']);
                            // ECE II — pink
                            $isEce2 = in_array($cn, ['ECE-II', 'ece 2', 'ece-ii', 'ece-2']);

                            $isHighlight = $isClass1 || $isClass6 || $isEce1 || $isEce2;

                            // Row styling — unified amber for all highlighted classes (dark + light)
                            $rowBg = $isHighlight
                                ? 'bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-400 dark:border-amber-500 hover:bg-amber-100 dark:hover:bg-amber-900/30'
                                : 'hover:bg-gray-50 dark:hover:bg-white/5';

                            $nameCls = $isHighlight
                                ? 'text-amber-800 dark:text-amber-300'
                                : 'text-gray-800 dark:text-gray-200';

                            $badgeCls = $isHighlight
                                ? 'bg-amber-200 dark:bg-amber-800/60 text-amber-800 dark:text-amber-300'
                                : '';
                        @endphp
                        <tr class="{{ $rowBg }}">
                            <td class="px-4 py-3 font-semibold {{ $nameCls }}">
                                {{ $row['class']->name }}
                                @if ($row['class']->is_ece)
                                    <span
                                        class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full">ECE</span>
                                @endif
                                @if ($isHighlight)
                                    <span
                                        class="ml-1 text-xs px-1.5 py-0.5 rounded-full font-bold {{ $badgeCls }}">★</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 hidden md:table-cell">
                                {{ $row['school_count'] }}</td>
                            <td class="px-4 py-3 text-center font-medium {{ $isHighlight ? $nameCls : 'text-gray-700' }}">
                                {{ number_format($row['total_seats']) }}</td>
                            <td class="px-4 py-3 text-center text-orange-600 font-medium hidden md:table-cell">
                                {{ number_format($row['total_existing']) }}</td>
                            <td
                                class="px-4 py-3 text-center font-bold {{ $row['total_remaining'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ number_format($row['total_remaining']) }}
                            </td>
                            <td class="px-4 py-3 text-center text-blue-700 font-bold">
                                {{ number_format($row['total_admitted']) }}
                                @if ($row['total_admitted'] > 0)
                                    <div class="text-xs text-gray-400 font-normal">
                                        R:{{ number_format($row['total_regular']) }}
                                        O:{{ number_format($row['total_oosc']) }}
                                        P:{{ number_format($row['total_p2p']) }}
                                    </div>
                                    <div class="text-xs font-normal mt-0.5">
                                        <span class="text-amber-600">☀ {{ number_format($row['total_morning']) }}</span>
                                        @if ($row['total_evening'] > 0)
                                            <span class="text-indigo-500 ml-1">🌙
                                                {{ number_format($row['total_evening']) }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-blue-900 bg-blue-50">
                                {{ number_format($row['total_filled']) }}</td>
                            <td class="px-4 py-3 text-center hidden md:table-cell">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $fillRate >= 90 ? 'bg-red-500' : ($fillRate >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                            style="width: {{ min(100, $fillRate) }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600">{{ $fillRate }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                    <tr>
                        <td class="px-4 py-3 text-gray-700">GRAND TOTAL</td>
                        <td class="px-4 py-3 text-center text-gray-500 hidden md:table-cell">{{ $institutions->count() }}
                        </td>
                        <td class="px-4 py-3 text-center text-blue-900">{{ number_format($grand['seats']) }}</td>
                        <td class="px-4 py-3 text-center text-orange-600 hidden md:table-cell">
                            {{ number_format($grand['existing']) }}</td>
                        <td
                            class="px-4 py-3 text-center {{ $grand['remaining'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format($grand['remaining']) }}</td>
                        <td class="px-4 py-3 text-center text-blue-700">{{ number_format($grand['admitted']) }}</td>
                        <td class="px-4 py-3 text-center text-blue-900 bg-blue-100">{{ number_format($grand['filled']) }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 hidden md:table-cell">
                            @php $gRate = $grand['seats'] > 0 ? round(($grand['filled'] / $grand['seats']) * 100) : 0 @endphp
                            {{ $gRate }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── Section 2: School-wise Class Breakdown (Toggle) ─────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-bold text-gray-800">Section 2 — School-wise Class Breakdown</h3>
            <p class="text-xs text-gray-500 mt-0.5">Click any school row to expand / collapse class details</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left max-w-[200px] min-w-[160px]">School / Class</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Sector</th>
                        <th class="px-4 py-3 text-center">Intake Capacity</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Promoted Students</th>
                        <th class="px-4 py-3 text-center text-green-600">Seats Available</th>
                        <th class="px-4 py-3 text-center text-blue-600">Newly Admitted</th>
                        <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Capacity</th>
                    </tr>
                </thead>

                @foreach ($institutions as $inst)
                    @php
                        $instSeatData = $seatData[$inst->id] ?? collect();
                        $instAdmData = $admissionData[$inst->id] ?? collect();
                        $instSeats = $instSeatData->sum('total_seats');
                        $instExisting = $instSeatData->sum('existing_enrollment');
                        $instAdmitted = $instAdmData->sum('total_admitted');
                        $instFilled = $instExisting + $instAdmitted;
                        $instRemaining = max(0, $instSeats - $instFilled);
                    @endphp

                    <tbody x-data="{ open: false }">

                        {{-- School summary row --}}
                        <tr class="bg-blue-900 text-white text-xs font-semibold cursor-pointer hover:bg-blue-800 transition select-none"
                            @click="open = !open">
                            <td class="px-4 py-3 max-w-[200px]" colspan="2">
                                <div class="flex items-center gap-2">
                                    <span x-text="open ? '&#9660;' : '&#9654;'"
                                        class="text-blue-300 text-xs w-3 shrink-0"></span>
                                    <span class="truncate max-w-[160px]"
                                        title="{{ $inst->name }}">{{ $inst->name }}</span>
                                    <span class="text-blue-300 font-normal">
                                        {{ $inst->type }} &middot; {{ ucfirst(str_replace('_', ' ', $inst->gender)) }}
                                        @if ($inst->shift)
                                            &middot;
                                            @if ($inst->shift === 'morning')
                                                <span class="text-amber-300">☀ Morning</span>
                                            @elseif ($inst->shift === 'evening')
                                                <span class="text-indigo-300">🌙 Evening</span>
                                            @else
                                                <span class="text-blue-200">{{ ucfirst($inst->shift) }}</span>
                                            @endif
                                        @endif
                                        @if ($inst->has_evening_classes)
                                            <span class="text-indigo-300 ml-1">+Eve</span>
                                        @endif
                                    </span>
                                    <span class="text-blue-400 font-normal text-xs">({{ $instSeatData->count() }}
                                        classes)</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">{{ number_format($instSeats) }}</td>
                            <td class="px-4 py-3 text-center text-orange-200 hidden md:table-cell">
                                {{ number_format($instExisting) }}</td>
                            <td
                                class="px-4 py-3 text-center {{ $instRemaining > 0 ? 'text-green-300' : 'text-red-300' }}">
                                {{ number_format($instRemaining) }}</td>
                            <td class="px-4 py-3 text-center text-blue-200">{{ number_format($instAdmitted) }}</td>
                            <td class="px-4 py-3 text-center font-bold">{{ number_format($instFilled) }}</td>
                        </tr>

                        {{-- Class detail rows --}}
                        @foreach ($instSeatData->sortBy('class_id') as $ic)
                            @php
                                $adm = $instAdmData[$ic->class_id] ?? null;
                                $admitted = $adm?->total_admitted ?? 0;
                                $filled = $ic->existing_enrollment + $admitted;
                                $remaining = max(0, $ic->total_seats - $filled);

                                $dcn = strtolower(trim($ic->classModel?->name ?? ''));

                                $dIsClass1 = in_array($dcn, ['class 1', 'class i', '1', 'one']);
                                $dIsClass6 = in_array($dcn, ['class 6', 'class vi', '6', 'six']);
                                $dIsEce1 = in_array($dcn, ['ece i', 'ece 1', 'ece-i', 'ece-1']);
                                $dIsEce2 = in_array($dcn, ['ECE-II/Prep', 'ece 2', 'ece-ii', 'ece-2']);
                                $dIsHighlight = $dIsClass1 || $dIsClass6 || $dIsEce1 || $dIsEce2;

                                // Row styling — unified amber for all highlighted classes (dark + light)
                                $dRowBg = $dIsHighlight
                                    ? 'bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-400 dark:border-amber-500 hover:bg-amber-100 dark:hover:bg-amber-900/30'
                                    : 'border-b border-gray-50 dark:border-white/5 hover:bg-blue-50 dark:hover:bg-white/5 bg-white dark:bg-transparent';

                                $dNameCls = $dIsHighlight
                                    ? 'text-amber-800 dark:text-amber-300 font-semibold'
                                    : 'text-gray-600 dark:text-gray-400';

                                $dBadgeCls = $dIsHighlight
                                    ? 'bg-amber-200 dark:bg-amber-800/60 text-amber-800 dark:text-amber-300'
                                    : '';
                            @endphp
                            <tr x-show="open" class="{{ $dRowBg }}">
                                <td class="px-4 py-2.5 pl-10 {{ $dNameCls }}">
                                    {{ $ic->classModel?->name }}
                                    @if ($ic->classModel?->is_ece)
                                        <span
                                            class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 rounded-full">ECE</span>
                                    @endif
                                    @if ($dIsHighlight)
                                        <span
                                            class="ml-1 text-xs px-1.5 py-0.5 rounded-full font-bold {{ $dBadgeCls }}">★</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-400 hidden md:table-cell">
                                    {{ $inst->sector?->name }}</td>
                                <td class="px-4 py-2.5 text-center text-gray-700">{{ number_format($ic->total_seats) }}
                                </td>
                                <td class="px-4 py-2.5 text-center text-orange-600 hidden md:table-cell">
                                    {{ number_format($ic->existing_enrollment) }}
                                    @if ($ic->promoted_count + $ic->failed_count > 0)
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            Promoted: <span
                                                class="text-green-600 font-semibold">{{ number_format($ic->promoted_count) }}</span>
                                            @if ($ic->failed_count > 0)
                                                &middot; Repeaters: <span
                                                    class="text-red-500 font-semibold">{{ number_format($ic->failed_count) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td
                                    class="px-4 py-2.5 text-center font-medium {{ $remaining > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ number_format($remaining) }}</td>
                                <td class="px-4 py-2.5 text-center text-blue-700">
                                    {{ number_format($admitted) }}
                                    @if ($admitted > 0)
                                        @php
                                            $morningAdm = $adm?->morning_total ?? 0;
                                            $eveningAdm = $adm?->evening_total ?? 0;
                                        @endphp
                                        <div class="text-xs font-normal mt-0.5">
                                            <span class="text-amber-600">☀ {{ number_format($morningAdm) }}</span>
                                            @if ($eveningAdm > 0)
                                                <span class="text-indigo-500 ml-1">🌙
                                                    {{ number_format($eveningAdm) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-center font-bold text-blue-900 bg-blue-50">
                                    {{ number_format($filled) }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                @endforeach

            </table>
        </div>
    </div>

@endsection
