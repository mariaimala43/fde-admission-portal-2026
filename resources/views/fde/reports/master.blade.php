@extends('layouts.app')
@section('title', 'Master Admission Report')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Master Admission Report</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institutions->count() }} schools
                &nbsp;·&nbsp; {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
            </p>
        </div>
        <a href="{{ route('fde.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Dashboard</a>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('fde.reports.master') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 items-end">

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sector</label>
                <select name="sector_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Sectors</option>
                    @foreach ($sectors as $s)
                        <option value="{{ $s->id }}" {{ $sectorId == $s->id ? 'selected' : '' }}>{{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Type</label>
                <select name="type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach (['I-V', 'I-VIII', 'I-X', 'I-XII', 'VI-VIII', 'VI-X', 'VI-XII', 'Model_College'] as $t)
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
                <a href="{{ route('fde.reports.master') }}"
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
            <p class="text-xs text-gray-400 uppercase mb-1">P2P</p>
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
            <p class="text-xs text-white/70 uppercase mb-1">Remaining</p>
            <p class="text-xl font-bold text-white">{{ number_format($grand['remaining']) }}</p>
        </div>
    </div>

    {{-- ── Section 1: Overall Class Summary (Document 7-Column + extras) ─────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-blue-50">
            <h3 class="text-sm font-bold text-blue-900">
                Section 1 — Overall Class Summary (All {{ $institutions->count() }} Schools Combined)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-center">Schools</th>
                        <th class="px-4 py-3 text-center">Existing Enrollment</th>
                        <th class="px-4 py-3 text-center">Intake Capacity</th>
                        <th class="px-4 py-3 text-center text-green-600">Seats Available</th>
                        <th class="px-4 py-3 text-center text-blue-600">Newly Admitted</th>
                        <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Enrollment</th>
                        <th class="px-4 py-3 text-center">Fill Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($overallByClass as $row)
                        @php
                            $fillRate = $row['total_seats'] > 0 ? round(($row['total_filled'] / $row['total_seats']) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $row['class']->name }}
                                @if ($row['class']->is_ece)
                                    <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full">ECE</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $row['school_count'] }}</td>
                            <td class="px-4 py-3 text-center text-orange-600 font-medium">{{ number_format($row['total_existing']) }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray-700">{{ number_format($row['total_seats']) }}</td>
                            <td class="px-4 py-3 text-center font-bold {{ $row['total_remaining'] > 0 ? 'text-green-600' : 'text-red-500' }}">
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
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-blue-900 bg-blue-50">{{ number_format($row['total_filled']) }}</td>
                            <td class="px-4 py-3 text-center">
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
                        <td class="px-4 py-3 text-center text-gray-500">{{ $institutions->count() }}</td>
                        <td class="px-4 py-3 text-center text-orange-600">{{ number_format($grand['existing']) }}</td>
                        <td class="px-4 py-3 text-center text-blue-900">{{ number_format($grand['seats']) }}</td>
                        <td class="px-4 py-3 text-center {{ $grand['remaining'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format($grand['remaining']) }}
                        </td>
                        <td class="px-4 py-3 text-center text-blue-700">{{ number_format($grand['admitted']) }}</td>
                        <td class="px-4 py-3 text-center text-blue-900 bg-blue-100">{{ number_format($grand['filled']) }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">
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
            <table class="w-full text-sm">

                {{-- Table header --}}
                <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">School / Class</th>
                        <th class="px-4 py-3 text-left">Sector</th>
                        <th class="px-4 py-3 text-center">Existing Enrollment</th>
                        <th class="px-4 py-3 text-center">Intake Capacity</th>
                        <th class="px-4 py-3 text-center text-green-600">Seats Available</th>
                        <th class="px-4 py-3 text-center text-blue-600">Newly Admitted</th>
                        <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Enrollment</th>
                    </tr>
                </thead>

                {{-- One tbody per school for Alpine toggle --}}
                @foreach ($institutions as $inst)
                    @php
                        $instSeatData = $seatData[$inst->id] ?? collect();
                        $instAdmData = $admissionData[$inst->id] ?? collect();
                        $instSeats = $instSeatData->sum('total_seats');
                        $instExisting = $instSeatData->sum('existing_enrollment');
                        $instAdmitted = $instAdmData->sum('total_admitted');
                        $instFilled = $instExisting + $instAdmitted;
                        $instRemaining = max(0, $instSeats - $instFilled);
                        $instRegular = $instAdmData->sum(fn($r) => ($r->reg_boys ?? 0) + ($r->reg_girls ?? 0));
                        $instOosc = $instAdmData->sum(fn($r) => ($r->oosc_boys ?? 0) + ($r->oosc_girls ?? 0));
                        $instP2p = $instAdmData->sum(fn($r) => ($r->p2p_boys ?? 0) + ($r->p2p_girls ?? 0));
                    @endphp

                    <tbody x-data="{ open: false }">

                        {{-- School summary row — click to toggle --}}
                        <tr class="bg-blue-900 text-white text-xs font-semibold cursor-pointer hover:bg-blue-800 transition select-none"
                            @click="open = !open">
                            <td class="px-4 py-3" colspan="2">
                                <div class="flex items-center gap-2">
                                    <span x-text="open ? '&#9660;' : '&#9654;'" class="text-blue-300 text-xs w-3"></span>
                                    <span>{{ $inst->name }}</span>
                                    <span class="text-blue-300 font-normal">
                                        {{ $inst->type }} &middot; {{ ucfirst(str_replace('_', ' ', $inst->gender)) }}
                                    </span>
                                    <span class="text-blue-400 font-normal text-xs">
                                        ({{ $instSeatData->count() }} classes)
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-orange-200">{{ number_format($instExisting) }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format($instSeats) }}</td>
                            <td class="px-4 py-3 text-center {{ $instRemaining > 0 ? 'text-green-300' : 'text-red-300' }}">
                                {{ number_format($instRemaining) }}
                            </td>
                            <td class="px-4 py-3 text-center text-blue-200">{{ number_format($instAdmitted) }}</td>
                            <td class="px-4 py-3 text-center font-bold">{{ number_format($instFilled) }}</td>
                        </tr>

                        {{-- Class detail rows — hidden until school row clicked --}}
                        @foreach ($instSeatData->sortBy('class_id') as $ic)
                            @php
                                $adm = $instAdmData[$ic->class_id] ?? null;
                                $admitted = $adm?->total_admitted ?? 0;
                                $filled = $ic->existing_enrollment + $admitted;
                                $remaining = max(0, $ic->total_seats - $filled);
                            @endphp
                            <tr x-show="open" class="border-b border-gray-50 hover:bg-blue-50 bg-white">
                                <td class="px-4 py-2.5 pl-10 text-gray-600">
                                    {{ $ic->classModel?->name }}
                                    @if ($ic->classModel?->is_ece)
                                        <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 rounded-full">ECE</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-400">{{ $inst->sector?->name }}</td>
                                <td class="px-4 py-2.5 text-center text-orange-600">{{ number_format($ic->existing_enrollment) }}</td>
                                <td class="px-4 py-2.5 text-center text-gray-700">{{ number_format($ic->total_seats) }}</td>
                                <td class="px-4 py-2.5 text-center font-medium {{ $remaining > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ number_format($remaining) }}
                                </td>
                                <td class="px-4 py-2.5 text-center text-blue-700">{{ number_format($admitted) }}</td>
                                <td class="px-4 py-2.5 text-center font-bold text-blue-900 bg-blue-50">{{ number_format($filled) }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                @endforeach

            </table>
        </div>
    </div>

@endsection
