@extends('layouts.app')
@section('title', 'AEO Dashboard')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">AEO Dashboard</h2>
        <p class="text-sm text-gray-500 mt-1">
            {{ Auth::user()->name }} — {{ $sectors->pluck('name')->join(', ') }}
            &nbsp;&middot;&nbsp; {{ now()->format('l, d M Y') }}
        </p>
    </div>

    {{-- ── Grand Summary Cards ────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Schools</p>
            <p class="text-2xl font-bold text-blue-900">{{ $grand['schools'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Intake Capacity</p>
            <p class="text-2xl font-bold text-blue-900">{{ number_format($grand['seats']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Existing Enrollment</p>
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
            <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Total Enrollment</p>
            <p class="text-2xl font-bold text-white">{{ number_format($grand['enrollment']) }}</p>
        </div>
    </div>

    {{-- ── Sector Summary Table ───────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-bold text-gray-800">Sector-wise Capacity Summary</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                <tr>
                    <th class="px-4 py-3 text-left">Sector</th>
                    <th class="px-4 py-3 text-center">Schools</th>
                    <th class="px-4 py-3 text-center">Intake Capacity</th>
                    <th class="px-4 py-3 text-center text-orange-600">Existing Enrollment</th>
                    <th class="px-4 py-3 text-center text-green-600">Seats Available</th>
                    <th class="px-4 py-3 text-center text-blue-600">Newly Admitted</th>
                    <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Enrollment</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($sectorSummary as $sector)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $sector->name }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $sector->school_count }}</td>
                        <td class="px-4 py-3 text-center font-medium text-gray-700">{{ number_format($sector->total_seats) }}</td>
                        <td class="px-4 py-3 text-center text-orange-600 font-medium">{{ number_format($sector->total_existing) }}</td>
                        <td class="px-4 py-3 text-center font-bold {{ $sector->total_available > 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format($sector->total_available) }}
                        </td>
                        <td class="px-4 py-3 text-center text-blue-700 font-medium">{{ number_format($sector->total_admitted) }}</td>
                        <td class="px-4 py-3 text-center font-bold text-blue-900 bg-blue-50">{{ number_format($sector->total_enrollment) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                <tr>
                    <td class="px-4 py-3 text-gray-700">GRAND TOTAL</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $grand['schools'] }}</td>
                    <td class="px-4 py-3 text-center text-blue-900">{{ number_format($grand['seats']) }}</td>
                    <td class="px-4 py-3 text-center text-orange-600">{{ number_format($grand['existing']) }}</td>
                    <td class="px-4 py-3 text-center {{ $grand['available'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ number_format($grand['available']) }}
                    </td>
                    <td class="px-4 py-3 text-center text-blue-700">{{ number_format($grand['admitted']) }}</td>
                    <td class="px-4 py-3 text-center text-blue-900 bg-blue-100">{{ number_format($grand['enrollment']) }}</td>
                </tr>
            </tfoot>
        </table>
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
                <span class="text-xs font-bold px-3 py-1 rounded-full
                    {{ $sector->total_available > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ number_format($sector->total_available) }} seats available
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-4 py-3 text-left">School / Class</th>
                            <th class="px-4 py-3 text-center">No. of Sections</th>
                            <th class="px-4 py-3 text-center">Existing Enrollment</th>
                            <th class="px-4 py-3 text-center">Intake Capacity</th>
                            <th class="px-4 py-3 text-center text-green-600">Seats Available<br><span class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                            <th class="px-4 py-3 text-center text-blue-600">Newly Admitted<br><span class="normal-case font-normal text-gray-400">(Daily Updates)</span></th>
                            <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Enrollment<br><span class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                        </tr>
                    </thead>

                    @foreach ($sectorInsts as $inst)
                        @php
                            $instClasses = $seatData[$inst->id] ?? collect();
                            $instAdm     = $admissionData[$inst->id] ?? collect();
                            $instSecs    = $sectionCounts[$inst->id] ?? collect();

                            $instSeats    = $instClasses->sum('total_seats');
                            $instExisting = $instClasses->sum('existing_enrollment');
                            $instAdmitted = $instAdm->sum('total_admitted');
                            $instAvail    = max(0, $instSeats - $instExisting - $instAdmitted);
                            $instTotal    = $instExisting + $instAdmitted;
                            $instSecCount = $instSecs->sum('section_count');
                        @endphp

                        <tbody x-data="{ open: false }">
                            {{-- School summary row --}}
                            <tr class="bg-blue-900 text-white text-xs font-semibold cursor-pointer hover:bg-blue-800 transition select-none"
                                @click="open = !open">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span x-text="open ? '&#9660;' : '&#9654;'" class="text-blue-300 text-xs w-3"></span>
                                        <span>{{ $inst->name }}</span>
                                        <span class="text-blue-300 font-normal">
                                            {{ $inst->type }} &middot; {{ ucfirst(str_replace('_', ' ', $inst->gender)) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">{{ $instSecCount }}</td>
                                <td class="px-4 py-3 text-center text-orange-200">{{ number_format($instExisting) }}</td>
                                <td class="px-4 py-3 text-center">{{ number_format($instSeats) }}</td>
                                <td class="px-4 py-3 text-center {{ $instAvail > 0 ? 'text-green-300' : 'text-red-300' }}">
                                    {{ number_format($instAvail) }}
                                </td>
                                <td class="px-4 py-3 text-center text-blue-200">{{ number_format($instAdmitted) }}</td>
                                <td class="px-4 py-3 text-center font-bold">{{ number_format($instTotal) }}</td>
                            </tr>

                            {{-- Class detail rows --}}
                            @foreach ($instClasses->sortBy('class_id') as $ic)
                                @php
                                    $adm       = $instAdm[$ic->class_id] ?? null;
                                    $admitted  = $adm?->total_admitted ?? 0;
                                    $secCount  = $instSecs[$ic->class_id]?->section_count ?? 0;
                                    $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                                    $totalEnrl = $ic->existing_enrollment + $admitted;
                                @endphp
                                <tr x-show="open" class="border-b border-gray-50 hover:bg-blue-50 bg-white">
                                    <td class="px-4 py-2.5 pl-10 text-gray-600">
                                        {{ $ic->classModel?->name }}
                                        @if ($ic->classModel?->is_ece)
                                            <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 rounded-full">ECE</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-gray-500">{{ max(1, $secCount) }}</td>
                                    <td class="px-4 py-2.5 text-center text-orange-600">{{ number_format($ic->existing_enrollment) }}</td>
                                    <td class="px-4 py-2.5 text-center text-gray-700">{{ number_format($ic->total_seats) }}</td>
                                    <td class="px-4 py-2.5 text-center font-medium {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($available) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-blue-700">{{ number_format($admitted) }}</td>
                                    <td class="px-4 py-2.5 text-center font-bold text-blue-900 bg-blue-50">{{ number_format($totalEnrl) }}</td>
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
