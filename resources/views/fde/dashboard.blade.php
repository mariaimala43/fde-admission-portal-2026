@extends('layouts.app')
@section('title', 'FDE Cell Dashboard')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">FDE Cell Dashboard</h2>
        <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, d M Y') }}</p>
    </div>

    {{-- ── Today's Totals ──────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
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
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">P2P Today</p>
            <p class="text-3xl font-bold text-orange-600">{{ number_format($todayTotals->p2p ?? 0) }}</p>
        </div>
    </div>

    {{-- ── Cumulative Totals ───────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
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
            <p class="text-xs text-orange-100 uppercase tracking-wider mb-1">Private → Public</p>
            <p class="text-3xl font-bold">{{ number_format($cumulativeTotals->p2p ?? 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        {{-- ── Sector Breakdown ─────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Sector-wise Breakdown</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                    <tr>
                        <th class="px-4 py-2 text-left">Sector</th>
                        <th class="px-4 py-2 text-center">Schools</th>
                        <th class="px-4 py-2 text-center">Today</th>
                        <th class="px-4 py-2 text-center">Year Total</th>
                        <th class="px-4 py-2 text-center text-purple-600">OOSC</th>
                        <th class="px-4 py-2 text-center text-orange-600">P2P</th>
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
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
                <div class="overflow-y-auto max-h-80">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-400 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left">School</th>
                                <th class="px-4 py-2 text-left">Sector</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($notSubmitted as $school)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('fde.schools.show', $school) }}"
                                            class="text-blue-700 hover:underline font-medium">
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
    <div class="flex gap-4">
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
