@extends('layouts.app')
@section('title', Auth::user()->hasRole('director') ? 'Director Dashboard' : 'AEO Dashboard')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            @if (Auth::user()->hasRole('director'))
                Director Dashboard
            @else
                AEO Dashboard
            @endif
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            {{ Auth::user()->name }}
            @if (Auth::user()->hasRole('aeo'))
                &mdash; {{ $sectors->pluck('name')->join(', ') }}
            @endif
            &nbsp;&middot;&nbsp; {{ now()->format('l, d M Y') }}
        </p>
    </div>

    {{-- ── Grand Summary Cards ────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Schools</p>
            <p class="text-2xl font-bold text-blue-900">{{ $grand['schools'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Intake Capacity</p>
            <p class="text-2xl font-bold text-blue-900">{{ number_format($grand['seats']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Promoted Students</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($grand['existing']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Seats Available</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($grand['available']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Newly Admitted</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($grand['admitted']) }}</p>
        </div>
        <div class="bg-blue-900 rounded-xl shadow-sm p-5 text-center">
            <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Total Capacity</p>
            <p class="text-2xl font-bold text-white">{{ number_format($grand['enrollment']) }}</p>
        </div>
    </div>

    {{-- ── Matric Tech + New Rooms Cards ──────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

        {{-- Matric Tech --}}
        <div class="bg-white rounded-xl border border-indigo-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl shrink-0">⚙️</div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Matric Tech Today</p>
                    <p class="text-2xl font-bold text-indigo-700">{{ number_format($matricTechToday) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Year Total: <span
                            class="font-semibold text-indigo-600">{{ number_format($matricTechYear) }}</span></p>
                </div>
            </div>
        </div>

        {{-- New Construction Rooms --}}
        <div class="bg-white rounded-xl border border-emerald-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-xl shrink-0">🏗️</div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">New Rooms Constructed</p>
                    <p class="text-2xl font-bold text-emerald-700">{{ number_format($newRoomsTotal) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Allocated: <span
                            class="font-semibold text-emerald-600">{{ number_format($newRoomsAllocated) }}</span>
                        &nbsp;|&nbsp;
                        Available: <span
                            class="font-semibold text-green-600">{{ number_format($newRoomsRemaining) }}</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Across {{ $schoolsWithNewRooms }} school(s)</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Sector Summary Table ───────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-bold text-gray-800">Sector-wise Capacity Summary</h3>
        </div>
        <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1 px-4 pt-3">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            Swipe right to see all columns
        </p>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Sector</th>
                        <th class="px-4 py-3 text-center">Schools</th>
                        <th class="px-4 py-3 text-center">Intake Capacity</th>
                        <th class="px-4 py-3 text-center text-orange-600">Promoted Students</th>
                        <th class="px-4 py-3 text-center text-green-600">Seats Available</th>
                        <th class="px-4 py-3 text-center text-blue-600">Newly Admitted</th>
                        <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Capacity</th>
                        <th class="px-4 py-3 text-center text-indigo-600">⚙️ Matric Tech</th>
                        <th class="px-4 py-3 text-center text-emerald-600">🏗️ New Rooms</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($sectorSummary as $sector)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $sector->name }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $sector->school_count }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray-700">
                                {{ number_format($sector->total_seats) }}</td>
                            <td class="px-4 py-3 text-center text-orange-600 font-medium">
                                {{ number_format($sector->total_existing) }}</td>
                            <td
                                class="px-4 py-3 text-center font-bold {{ $sector->total_available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ number_format($sector->total_available) }}
                            </td>
                            <td class="px-4 py-3 text-center text-blue-700 font-medium">
                                {{ number_format($sector->total_admitted) }}</td>
                            <td class="px-4 py-3 text-center font-bold text-blue-900 bg-blue-50">
                                {{ number_format($sector->total_enrollment) }}</td>
                            <td class="px-4 py-3 text-center text-indigo-700 font-semibold">
                                {{ number_format($sector->matric_tech ?? 0) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if (($sector->new_rooms_total ?? 0) > 0)
                                    <span class="text-emerald-700 font-semibold">{{ $sector->new_rooms_total }}</span>
                                    <span class="text-gray-400 text-xs"> / {{ $sector->new_rooms_remaining }} avail.</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                    <tr>
                        <td class="px-4 py-3 text-gray-700">GRAND TOTAL</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $grand['schools'] }}</td>
                        <td class="px-4 py-3 text-center text-blue-900">{{ number_format($grand['seats']) }}</td>
                        <td class="px-4 py-3 text-center text-orange-600">{{ number_format($grand['existing']) }}</td>
                        <td
                            class="px-4 py-3 text-center {{ $grand['available'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format($grand['available']) }}
                        </td>
                        <td class="px-4 py-3 text-center text-blue-700">{{ number_format($grand['admitted']) }}</td>
                        <td class="px-4 py-3 text-center text-blue-900 bg-blue-100">
                            {{ number_format($grand['enrollment']) }}
                        </td>
                        <td class="px-4 py-3 text-center text-indigo-700">{{ number_format($matricTechYear) }}</td>
                        <td class="px-4 py-3 text-center text-emerald-700">
                            {{ $newRoomsTotal > 0 ? number_format($newRoomsTotal) . ' / ' . number_format($newRoomsRemaining) . ' avail.' : '—' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── School-wise Class Breakdown (per sector) ───────── --}}
    @foreach ($sectorSummary as $sector)
        @php
            $sectorInsts = $institutions->where('sector_id', $sector->id);
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">{{ $sector->name }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $sectorInsts->count() }} schools</p>
                </div>
                <span
                    class="text-xs font-bold px-3 py-1 rounded-full
                    {{ $sector->total_available > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ number_format($sector->total_available) }} seats available
                </span>
            </div>

            <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                Swipe right to see all columns
            </p>
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-4 py-3 text-left">School / Class</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">No. of Sections</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Intake Capacity</th>
                            <th class="px-4 py-3 text-center hidden md:table-cell">Promoted Students</th>
                            <th class="px-4 py-3 text-center text-green-600">Seats Available<br><span
                                    class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                            <th class="px-4 py-3 text-center text-blue-600">Newly Admitted<br><span
                                    class="normal-case font-normal text-gray-400">(Daily Updates)</span></th>
                            <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Capacity<br><span
                                    class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                        </tr>
                    </thead>

                    @foreach ($sectorInsts as $inst)
                        @php
                            $instClasses = $seatData[$inst->id] ?? collect();
                            $instAdm = $admissionData[$inst->id] ?? collect();
                            $instSecs = $sectionCounts[$inst->id] ?? collect();

                            $instSeats    = $instClasses->sum('total_seats');
                            $instExisting = $instClasses->sum('existing_enrollment');
                            $instAdmitted = $instAdm->sum('total_admitted');
                            $instAvail    = max(0, $instSeats - $instExisting - $instAdmitted);
                            $instTotal    = $instExisting + $instAdmitted;
                            $instSecCount = $instSecs->sum('section_count');
                            $hasEvening   = (bool) $inst->has_evening_classes;
                        @endphp

                        <tbody x-data="{ open: false }">
                            {{-- School summary row --}}
                            <tr class="bg-blue-900 text-white text-xs font-semibold cursor-pointer hover:bg-blue-800 transition select-none"
                                @click="open = !open">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span x-text="open ? '&#9660;' : '&#9654;'"
                                            class="text-blue-300 text-xs w-3"></span>
                                        <span>{{ $inst->name }}</span>
                                        <span class="text-blue-300 font-normal">
                                            {{ $inst->type }} &middot;
                                            {{ ucfirst(str_replace('_', ' ', $inst->gender)) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center hidden sm:table-cell">{{ $instSecCount }}</td>
                                <td class="px-4 py-3 text-center hidden sm:table-cell">{{ number_format($instSeats) }}
                                </td>
                                <td class="px-4 py-3 text-center text-orange-200 hidden md:table-cell">
                                    {{ number_format($instExisting) }}</td>
                                <td
                                    class="px-4 py-3 text-center {{ $instAvail > 0 ? 'text-green-300' : 'text-red-300' }}">
                                    {{ number_format($instAvail) }}
                                </td>
                                <td class="px-4 py-3 text-center text-blue-200">{{ number_format($instAdmitted) }}</td>
                                <td class="px-4 py-3 text-center font-bold">{{ number_format($instTotal) }}</td>
                            </tr>

                            {{-- Class detail rows --}}
                            @foreach ($instClasses->sortBy('class_id') as $ic)
                                @php
                                    $adm = $instAdm[$ic->class_id] ?? null;
                                    $admitted = $adm?->total_admitted ?? 0;
                                    $secCount = $instSecs[$ic->class_id]?->section_count ?? 0;
                                    $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                                    $totalEnrl = $ic->existing_enrollment + $admitted;
                                @endphp
                                <tr x-show="open" class="border-b border-gray-50 hover:bg-blue-50 bg-white">
                                    <td class="px-4 py-2.5 pl-10 text-gray-600">
                                        {{ $ic->classModel?->name }}
                                        @if ($ic->classModel?->is_ece)
                                            <span
                                                class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 rounded-full">ECE</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-gray-500 hidden sm:table-cell">
                                        {{ max(1, $secCount) }}</td>
                                    <td class="px-4 py-2.5 text-center text-gray-700 hidden sm:table-cell">
                                        {{ number_format($ic->total_seats) }}</td>
                                    <td class="px-4 py-2.5 text-center text-orange-600 hidden md:table-cell">
                                        @if ($hasEvening)
                                            {{-- Morning sub-row --}}
                                            <div class="text-xs text-gray-500 font-semibold uppercase mb-0.5">Morning</div>
                                            {{ number_format($ic->morning_existing ?? 0) }}
                                            @if (($ic->morning_promoted ?? 0) + ($ic->morning_failed ?? 0) > 0)
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    Promoted: <span class="text-green-600 font-semibold">{{ number_format($ic->morning_promoted ?? 0) }}</span>
                                                    @if (($ic->morning_failed ?? 0) > 0)
                                                        &middot; Repeaters: <span class="text-red-500 font-semibold">{{ number_format($ic->morning_failed ?? 0) }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                            {{-- Evening sub-row --}}
                                            <div class="text-xs text-indigo-500 font-semibold uppercase mt-1 mb-0.5">Evening</div>
                                            {{ number_format($ic->evening_existing ?? 0) }}
                                            @if (($ic->evening_promoted ?? 0) + ($ic->evening_failed ?? 0) > 0)
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    Promoted: <span class="text-green-600 font-semibold">{{ number_format($ic->evening_promoted ?? 0) }}</span>
                                                    @if (($ic->evening_failed ?? 0) > 0)
                                                        &middot; Repeaters: <span class="text-red-500 font-semibold">{{ number_format($ic->evening_failed ?? 0) }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @else
                                            {{ number_format($ic->existing_enrollment) }}
                                            @if ($ic->promoted_count + $ic->failed_count > 0)
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    Promoted: <span class="text-green-600 font-semibold">{{ number_format($ic->promoted_count) }}</span>
                                                    @if ($ic->failed_count > 0)
                                                        &middot; Repeaters: <span class="text-red-500 font-semibold">{{ number_format($ic->failed_count) }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td
                                        class="px-4 py-2.5 text-center font-medium {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($available) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-blue-700">{{ number_format($admitted) }}</td>
                                    <td class="px-4 py-2.5 text-center font-bold text-blue-900 bg-blue-50">
                                        {{ number_format($totalEnrl) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach

                </table>
            </div>
        </div>
    @endforeach

    @if ($sectors->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
            <p class="text-yellow-800 font-medium">No sectors assigned to your account. Please contact FDE Cell.</p>
        </div>
    @endif

@endsection
