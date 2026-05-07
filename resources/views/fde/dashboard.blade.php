@extends('layouts.app')
@section('title', 'FDE Admission Cell Dashboard')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">FDE Admission Cell Dashboard</h2>
        <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, d M Y') }}</p>
    </div>

    {{-- ── Today's Totals ──────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Today's Admissions</p>
            <p class="text-3xl font-bold text-blue-900">{{ number_format($todayTotals->total ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Regular Today</p>
            <p class="text-3xl font-bold text-blue-700">{{ number_format($todayTotals->regular ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">OOSC Today</p>
            <p class="text-3xl font-bold text-purple-700">{{ number_format($todayTotals->oosc ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-orange-100 p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">P2G Today</p>
            <p class="text-3xl font-bold text-orange-600">{{ number_format($todayTotals->p2p ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-teal-100 p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">⚙️ Matric Tech Today</p>
            <p class="text-3xl font-bold text-teal-700">{{ number_format($todayTotals->matric_tech ?? 0) }}</p>
        </div>
    </div>

    {{-- ── Ex FG Colleges Totals ──────────────────────────────── --}}

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Admitted (Ex FG Colleges)</p>
            <p class="text-3xl font-bold text-blue-900">18,281</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Intake Capacity (Ex FG Colleges)</p>
            <p class="text-3xl font-bold text-blue-700">22,133</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Available Seats (Ex FG Colleges)</p>
            <p class="text-3xl font-bold text-purple-700">3,852</p>
        </div>
    </div>

    {{-- ── Cumulative Totals ───────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-blue-900 rounded-xl p-5 text-center text-white">
            <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Total Admitted (Year)</p>
            <p class="text-3xl font-bold">{{ number_format($cumulativeTotals->total ?? 0) }}</p>
        </div>
        <div class="bg-blue-700 rounded-xl p-5 text-center text-white">
            <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Regular</p>
            <p class="text-3xl font-bold">{{ number_format($cumulativeTotals->regular ?? 0) }}</p>
        </div>
        <div class="bg-purple-700 rounded-xl p-5 text-center text-white">
            <p class="text-xs text-purple-200 uppercase tracking-wider mb-1">OOSC Campaign</p>
            <p class="text-3xl font-bold">{{ number_format($cumulativeTotals->oosc ?? 0) }}</p>
        </div>
        <div class="bg-orange-600 rounded-xl p-5 text-center text-white">
            <p class="text-xs text-orange-100 uppercase tracking-wider mb-1">Private to Government</p>
            <p class="text-3xl font-bold">{{ number_format($cumulativeTotals->p2p ?? 0) }}</p>
        </div>
        <div class="bg-emerald-700 rounded-xl p-5 text-center text-white">
            <p class="text-xs text-emerald-100 uppercase tracking-wider mb-1">Available Capacity</p>
            <p class="text-3xl font-bold">{{ number_format($availableCapacity ?? 0) }}</p>
        </div>
    </div>

    {{-- ── Matric Tech Breakdown ──────────────────────── --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-teal-200 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">⚙️ Matric Tech Existing</p>
            <p class="text-2xl font-bold text-teal-700">{{ number_format($matricTechExisting) }}</p>
            <p class="text-xs text-gray-400 mt-1">Previous year baseline</p>
        </div>
        <div class="bg-teal-700 rounded-xl shadow-sm p-5 text-center text-white">
            <p class="text-xs text-teal-100 uppercase tracking-wider mb-1">⚙️ Admitted This Year</p>
            <p class="text-2xl font-bold">{{ number_format($cumulativeTotals->matric_tech ?? 0) }}</p>
            <p class="text-xs text-teal-200 mt-1">Today: {{ number_format($todayTotals->matric_tech ?? 0) }}</p>
        </div>
        <div class="bg-teal-900 rounded-xl shadow-sm p-5 text-center text-white">
            <p class="text-xs text-teal-200 uppercase tracking-wider mb-1">⚙️ Total Matric Tech</p>
            <p class="text-2xl font-bold">{{ number_format($matricTechExisting + ($cumulativeTotals->matric_tech ?? 0)) }}</p>
            <p class="text-xs text-teal-300 mt-1">Existing + This Year</p>
        </div>
    </div>

    {{-- ── New Construction Rooms ─────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">🏗️ New Construction Classrooms</h3>
                <p class="text-xs text-gray-400 mt-0.5">Newly constructed rooms across all ICT schools</p>
            </div>
            <a href="{{ route('fde.rooms.index') }}" class="text-xs text-blue-700 hover:underline font-medium">
                View Details →
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 divide-x divide-gray-50">
            <div class="px-5 py-4 text-center">
                <p class="text-2xl font-bold text-blue-900">132</p>
                <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Total New Classroom</p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-2xl font-bold text-gray-800"> 60</p>
                <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Colleges</p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-2xl font-bold text-green-600">72</p>
                <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Schools</p>
            </div>

            <div class="px-5 py-4 text-center bg-blue-50">
                <p class="text-2xl font-bold text-blue-900"> 2880</p>
                <p class="text-xs text-blue-400 uppercase tracking-wide mt-0.5">Total enrollment Capacity</p>
            </div>
        </div>
    </div>

    {{-- ── Referral Outcomes ──────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">📋 Referral Outcomes</h3>
                <p class="text-xs text-gray-400 mt-0.5">Cumulative tracking across all referrals</p>
            </div>
            <a href="{{ route('fde.referrals.index') }}" class="text-xs text-blue-700 hover:underline font-medium">
                View All Referrals →
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 divide-x divide-gray-50">
            <div class="px-5 py-4 text-center">
                <p class="text-2xl font-bold text-gray-800">{{ number_format($referralStats->total ?? 0) }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Total</p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($referralStats->pending ?? 0) }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Pending</p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ number_format($referralStats->accepted ?? 0) }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Accepted</p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-2xl font-bold text-red-500">{{ number_format($referralStats->rejected ?? 0) }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Rejected</p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-2xl font-bold text-emerald-700">{{ number_format($referralStats->admitted ?? 0) }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Admitted</p>
            </div>
            <div class="px-5 py-4 text-center">
                <p class="text-2xl font-bold text-orange-600">{{ number_format($referralStats->not_admitted ?? 0) }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">Not Admitted</p>
            </div>
        </div>
    </div>



    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        {{-- ── Sector Breakdown ─────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Sector-wise Breakdown</h3>
            </div>
            <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1 px-4 pt-2">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                Swipe right to see all columns
            </p>
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-4 py-2 text-left">Sector</th>
                            <th class="px-4 py-2 text-center">Schools</th>
                            <th class="px-4 py-2 text-center">Today</th>
                            <th class="px-4 py-2 text-center">Year Total</th>
                            <th class="px-4 py-2 text-center text-purple-600">OOSC</th>
                            <th class="px-4 py-2 text-center text-orange-600">P2G</th>
                            <th class="px-4 py-2 text-center text-teal-600">⚙️ M.Tech</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($sectorBreakdown as $sector)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-medium text-gray-800">{{ $sector->name }}</td>
                                <td class="px-4 py-2.5 text-center text-gray-600">{{ $sector->institutions_count }}</td>
                                <td class="px-4 py-2.5 text-center font-semibold text-blue-700">
                                    {{ number_format($sector->today_total) }}
                                </td>
                                <td class="px-4 py-2.5 text-center font-bold text-gray-900">
                                    {{ number_format($sector->cumul_total) }}
                                </td>
                                <td class="px-4 py-2.5 text-center text-purple-700">
                                    {{ number_format($sector->cumul_oosc) }}
                                </td>
                                <td class="px-4 py-2.5 text-center text-orange-700">
                                    {{ number_format($sector->cumul_p2p) }}
                                </td>
                                <td class="px-4 py-2.5 text-center text-teal-700 font-semibold">
                                    {{ number_format($sector->cumul_matric_tech ?? 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Schools Not Submitted Today ──────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-gray-800">
                    Not Submitted Today
                </h3>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">
                    {{ $notSubmittedCount }} / {{ $totalSchools }}
                </span>
            </div>
            @if ($notSubmitted->isEmpty())
                <div class="px-6 py-8 text-center text-green-600 text-sm font-medium">
                    ✓ All schools submitted today!
                </div>
            @else
                <div class="overflow-y-auto overflow-x-auto max-h-80">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-400 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left">School</th>
                                <th class="px-4 py-2 text-left">Sector</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($notSubmitted as $school)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 max-w-[160px]">
                                        <a href="{{ route('fde.schools.show', $school) }}"
                                            class="text-blue-700 hover:underline font-medium truncate block max-w-[160px]"
                                            title="{{ $school->name }}">
                                            {{ $school->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 text-gray-500 text-xs">
                                        {{ $school->sector?->name }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- Quick Links --}}
    <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ route('fde.schools.index') }}"
            class="bg-blue-900 text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-blue-800 transition">
            View All Schools →
        </a>
        <a href="{{ route('fde.reports.master') }}"
            class="bg-gray-700 text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-gray-600 transition">
            View Report →
        </a>
    </div>

@endsection
