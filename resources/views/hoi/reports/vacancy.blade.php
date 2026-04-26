{{-- resources/views/hoi/reports/vacancy.blade.php --}}
@extends('layouts.app')
@section('title', 'Vacancy Position — ' . $institution->name)

@section('content')

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Vacancy Position Report</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->name }}
                @if ($institution->sector)
                    <span class="mx-2 text-gray-300">·</span>
                    <span>{{ $institution->sector->name }}</span>
                @endif
                <span class="mx-2 text-gray-300">·</span>
                <span class="font-semibold text-blue-900">{{ $academicYear?->name ?? '—' }}</span>
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('hoi.admissions.report') }}"
               class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                📋 Admission Report
            </a>
            <a href="{{ route('hoi.enrollment.index') }}"
               class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                ← Enrollment
            </a>
        </div>
    </div>

    {{-- ── Summary Cards ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Intake Capacity</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalSeats) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Promoted Students</p>
            <p class="text-2xl font-bold text-orange-500">{{ number_format($totalExisting) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Admitted (YTD)</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($totalAdmitted) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-{{ $totalAvailable > 0 ? 'green' : 'red' }}-100 shadow-sm p-4 text-center
                    {{ $totalAvailable > 0 ? 'bg-green-50' : 'bg-red-50' }}">
            <p class="text-xs {{ $totalAvailable > 0 ? 'text-green-600' : 'text-red-600' }} uppercase font-semibold mb-1">Seats Available</p>
            <p class="text-2xl font-bold {{ $totalAvailable > 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($totalAvailable) }}
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Full Classes</p>
            <p class="text-2xl font-bold {{ $fullClasses > 0 ? 'text-red-500' : 'text-gray-400' }}">
                {{ $fullClasses }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">of {{ $vacancyRows->count() }} total</p>
        </div>

    </div>

    {{-- ── Vacancy Table ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-blue-900 flex justify-between items-center">
            <span class="text-white font-bold text-sm">📊 Class-wise Vacancy Position</span>
            <span class="text-blue-200 text-xs">Available = Capacity − Existing − Admitted (YTD)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-32">
                            Class</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50">
                            Sec.</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-green-600 uppercase bg-green-50">
                            Intake<br>Capacity</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-orange-600 uppercase bg-orange-50">
                            Existing<br>Enrollment</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-700 uppercase bg-blue-50">
                            Admitted<br>(YTD)</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase bg-gray-50">
                            Total<br>Occupied</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-purple-700 uppercase bg-purple-50 min-w-[120px]">
                            Seats<br>Available</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-400 uppercase bg-gray-50">
                            Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vacancyRows as $row)
                        @php $occupied = $row->existing_enrollment + $row->total_admitted; @endphp
                        <tr class="border-b border-gray-50 transition-colors
                            {{ $row->is_full ? 'bg-red-50' : 'hover:bg-gray-50' }}">

                            {{-- Class --}}
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-800">{{ $row->class_name }}</p>
                                @if ($row->is_ece)
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">ECE</span>
                                @endif
                            </td>

                            {{-- Sections --}}
                            <td class="px-3 py-3 text-center text-gray-600">
                                <span class="font-medium">{{ $row->sections }}</span>
                                @if ($row->section_names !== '—')
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $row->section_names }}</div>
                                @endif
                            </td>

                            {{-- Intake Capacity --}}
                            <td class="px-3 py-3 text-center bg-green-50">
                                <span class="font-bold text-green-700 text-base">
                                    {{ number_format($row->total_seats) }}
                                </span>
                            </td>

                            {{-- Promoted Students --}}
                            <td class="px-3 py-3 text-center bg-orange-50">
                                <span class="font-semibold text-orange-700">
                                    {{ number_format($row->existing_enrollment) }}
                                </span>
                            </td>

                            {{-- Admitted YTD --}}
                            <td class="px-3 py-3 text-center bg-blue-50">
                                <span class="font-semibold text-blue-700">
                                    {{ number_format($row->total_admitted) }}
                                </span>
                            </td>

                            {{-- Total Occupied --}}
                            <td class="px-3 py-3 text-center">
                                <span class="font-semibold text-gray-700">
                                    {{ number_format($occupied) }}
                                </span>
                                @if ($row->total_seats > 0)
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1.5">
                                        <div class="h-1.5 rounded-full {{ $row->is_full ? 'bg-red-500' : 'bg-blue-500' }}"
                                             style="width: {{ min(100, round($occupied / $row->total_seats * 100)) }}%">
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ round($occupied / $row->total_seats * 100) }}%
                                    </div>
                                @endif
                            </td>

                            {{-- Seats Available --}}
                            <td class="px-3 py-3 text-center bg-purple-50">
                                <span class="font-bold text-2xl
                                    {{ $row->available > 10 ? 'text-green-600'
                                        : ($row->available > 0 ? 'text-yellow-500'
                                        : 'text-red-500') }}">
                                    {{ number_format($row->available) }}
                                </span>
                            </td>

                            {{-- Status badge --}}
                            <td class="px-3 py-3 text-center">
                                @if ($row->is_full)
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-semibold">
                                        🚫 Full
                                    </span>
                                @elseif ($row->available <= 10)
                                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full font-semibold">
                                        ⚠️ Nearly Full
                                    </span>
                                @else
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-semibold">
                                        ✅ Open
                                    </span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                No active classes configured.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- Totals footer --}}
                <tfoot>
                    <tr class="bg-blue-900 text-white font-bold text-sm">
                        <td class="px-4 py-3" colspan="2">TOTAL</td>
                        <td class="px-3 py-3 text-center bg-green-800">{{ number_format($totalSeats) }}</td>
                        <td class="px-3 py-3 text-center bg-orange-800">{{ number_format($totalExisting) }}</td>
                        <td class="px-3 py-3 text-center">{{ number_format($totalAdmitted) }}</td>
                        <td class="px-3 py-3 text-center">{{ number_format($totalExisting + $totalAdmitted) }}</td>
                        <td class="px-3 py-3 text-center bg-purple-800">{{ number_format($totalAvailable) }}</td>
                        <td class="px-3 py-3 text-center">
                            @if ($fullClasses > 0)
                                <span class="text-red-300">{{ $fullClasses }} full</span>
                            @else
                                <span class="text-green-300">All open</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── Legend ────────────────────────────────────────────────────── --}}
    <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500">
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Open (> 10 seats)
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> Nearly Full (1–10 seats)
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Full (0 seats)
        </span>
        <span class="ml-auto text-gray-400 italic">
            Admitted (YTD) includes all verified daily admissions for the current academic year.
        </span>
    </div>

@endsection
