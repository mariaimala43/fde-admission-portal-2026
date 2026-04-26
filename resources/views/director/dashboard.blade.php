{{-- SAVE AS: resources/views/director/dashboard.blade.php --}}
@extends('layouts.app')
@section('title', 'Executive Dashboard')

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap');

        .exec-dash * {
            font-family: 'Outfit', sans-serif;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* KPI cards */
        .kpi-card {
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent);
        }

        /* Bar chart */
        .bar-track {
            height: 8px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 1s cubic-bezier(.16, 1, .3, 1);
        }

        /* Trend sparkline bars */
        .trend-col > div > div {
            border-radius: 3px 3px 0 0;
        }
        .trend-col > div > div:hover {
            filter: brightness(1.15);
        }

        /* Sector table row */
        .sector-row {
            transition: background 0.15s;
        }

        .sector-row:hover {
            background: #f8faff;
        }

        /* Animate in */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-up {
            animation: fadeUp 0.4s ease both;
        }

        .delay-1 {
            animation-delay: 0.05s;
        }

        .delay-2 {
            animation-delay: 0.10s;
        }

        .delay-3 {
            animation-delay: 0.15s;
        }

        .delay-4 {
            animation-delay: 0.20s;
        }

        /* Fill rate colour */
        .fill-low {
            color: #16a34a;
        }

        .fill-medium {
            color: #d97706;
        }

        .fill-high {
            color: #dc2626;
        }

        .fill-bg-low {
            background: #dcfce7;
            color: #15803d;
        }

        .fill-bg-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .fill-bg-high {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
@endpush

@section('content')
    <div class="exec-dash">

        {{-- ═══════════════════════════════════════════════════════
         HEADER
        ═══════════════════════════════════════════════════════ --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-8 fade-up">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="text-2xl">🏛️</span>
                    <h1 class="text-2xl font-bold text-gray-900">Executive Dashboard</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">
                    Federal Directorate of Education &nbsp;·&nbsp;
                    {{ $academicYear?->name ?? 'Academic Year' }} &nbsp;·&nbsp;
                    {{ now()->format('l, d M Y') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('director.reports.dashboard') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    📊 Analytics
                </a>
                <a href="{{ route('director.reports.vacancy') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    🪑 Vacancy
                </a>
                <a href="{{ route('director.reports.master') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl bg-blue-900 text-white hover:bg-blue-800 transition">
                    📋 Master Report
                </a>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
         ROW 1 — PRIMARY KPI CARDS (6)
        ═══════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

            {{-- Total Schools --}}
            <div class="kpi-card bg-white rounded-2xl border border-gray-100 p-5 fade-up delay-1" style="--accent:#3b82f6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Schools</p>
                <p class="text-3xl font-bold text-gray-900 mono">{{ number_format($totalSchools) }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $configuredSchools }} configured</p>
            </div>

            {{-- Intake Capacity --}}
            <div class="kpi-card bg-white rounded-2xl border border-gray-100 p-5 fade-up delay-1" style="--accent:#6366f1">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Intake Capacity</p>
                <p class="text-3xl font-bold text-gray-900 mono">{{ number_format($totalSeats) }}</p>
                <p class="text-xs text-gray-400 mt-2">total seats</p>
            </div>

            {{-- Promoted Students --}}
            <div class="kpi-card bg-white rounded-2xl border border-gray-100 p-5 fade-up delay-2" style="--accent:#f59e0b">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Prior Enrollment</p>
                <p class="text-3xl font-bold text-amber-600 mono">{{ number_format($totalExisting) }}</p>
                <p class="text-xs text-gray-400 mt-2">before admission drive</p>
            </div>

            {{-- Newly Admitted --}}
            <div class="kpi-card bg-white rounded-2xl border border-gray-100 p-5 fade-up delay-2" style="--accent:#10b981">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Newly Admitted</p>
                <p class="text-3xl font-bold text-emerald-600 mono">{{ number_format($totalAdmitted) }}</p>
                <p class="text-xs text-gray-400 mt-2">
                    {{ number_format((int) ($todayAdm->total ?? 0)) }} today
                </p>
            </div>

            {{-- Seats Available --}}
            <div class="kpi-card bg-white rounded-2xl border border-gray-100 p-5 fade-up delay-3"
                style="--accent:{{ $totalAvailable > 0 ? '#10b981' : '#ef4444' }}">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Seats Available</p>
                <p class="text-3xl font-bold mono {{ $totalAvailable > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ number_format($totalAvailable) }}
                </p>
                <p class="text-xs text-gray-400 mt-2">remaining capacity</p>
            </div>

            {{-- Fill Rate --}}
            <div class="kpi-card rounded-2xl p-5 fade-up delay-3 {{ $fillRate >= 90 ? 'bg-red-600' : ($fillRate >= 70 ? 'bg-amber-500' : 'bg-blue-900') }}"
                style="--accent:transparent">
                <p
                    class="text-xs font-semibold uppercase tracking-widest mb-3 {{ $fillRate >= 90 ? 'text-red-200' : ($fillRate >= 70 ? 'text-amber-100' : 'text-blue-300') }}">
                    Fill Rate</p>
                <p class="text-3xl font-bold text-white mono">{{ $fillRate }}%</p>
                <div class="mt-3 bar-track bg-white/20">
                    <div class="bar-fill bg-white" style="width: {{ min(100, $fillRate) }}%"></div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
         ROW 1B — MATRIC TECH + NEW CONSTRUCTION ROOMS
        ═══════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

            {{-- New Construction Rooms --}}
            <div class="kpi-card bg-white rounded-2xl border border-gray-100 p-5 fade-up delay-2" style="--accent:#10b981">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-xl shrink-0">🏗️
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">New Construction Rooms
                        </p>
                        <div class="flex items-baseline gap-4 flex-wrap">
                            <div>
                                <p class="text-3xl font-bold text-emerald-700 mono">132</p>
                                <p class="text-xs text-gray-400 mt-0.5">build</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-emerald-500 mono"> 60
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">Colleges</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-green-600 mono"> 72
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">School(s)</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Total enrollment capacity
                            <bclass="text-xl font-bold text-green-600 mono">2880</b> student</p>
                    </div>
                </div>
            </div>


            {{-- ── Ex FG Colleges Totals ──────────────────────────────── --}}
            <div class="kpi-card bg-white rounded-2xl border border-gray-100 p-5 fade-up delay-2" style="--accent:#61477a">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <!-- Graduation cap -->
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c0 1.657 2.686 3 6 3s6-1.343 6-3v-5"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Ex FG Colleges
                        </p>
                        <div class="flex items-baseline gap-4 flex-wrap">

                            <div>
                                <p class="text-xl font-bold text-purple-500 mono"> 22,133
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">Intake Capacity</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-violet-500 mono">18,281</p>
                                <p class="text-xs text-gray-400 mt-0.5">Total Admitted</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-indigo-600 mono">3,852
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">Available Seats</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Matric Tech --}}
            <div class="kpi-card bg-white rounded-2xl border border-gray-100 p-5 fade-up delay-2" style="--accent:#6366f1">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl shrink-0">⚙️
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Matric Tech Program
                        </p>
                        <div class="flex items-baseline gap-4 flex-wrap">
                            <div>
                                <p class="text-3xl font-bold text-indigo-700 mono">{{ number_format($matricTechToday) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">admitted today</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-indigo-500 mono">{{ number_format($matricTechYear) }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">year total</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════
         ROW 2 — TODAY STATUS + GENDER + OOSC
        ═══════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

            {{-- Today's Submission Status --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 fade-up delay-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-700">Today's Submissions</h3>
                    <span
                        class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-50 text-blue-700">{{ now()->format('d M') }}</span>
                </div>
                <div class="flex gap-4 mb-4">
                    <div class="flex-1 text-center bg-green-50 rounded-xl py-3">
                        <p class="text-2xl font-bold text-green-700 mono">{{ $submittedToday }}</p>
                        <p class="text-xs text-green-600 mt-1">Submitted</p>
                    </div>
                    <div class="flex-1 text-center bg-red-50 rounded-xl py-3">
                        <p class="text-2xl font-bold text-red-600 mono">{{ $notSubmittedToday }}</p>
                        <p class="text-xs text-red-500 mt-1">Pending</p>
                    </div>
                </div>
                <div class="bar-track">
                    <div class="bar-fill bg-green-500"
                        style="width: {{ $configuredSchools > 0 ? round(($submittedToday / $configuredSchools) * 100) : 0 }}%">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2 text-right">
                    {{ $configuredSchools > 0 ? round(($submittedToday / $configuredSchools) * 100) : 0 }}% compliance ·
                    {{ $configuredSchools }} configured schools
                </p>
                @if (isset($todayAdm->total) && $todayAdm->total > 0)
                    <div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-center">
                        <div>
                            <p class="text-sm font-bold text-blue-800 mono">{{ number_format((int) $todayAdm->boys) }}</p>
                            <p class="text-xs text-gray-400">Boys today</p>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-pink-600 mono">{{ number_format((int) $todayAdm->girls) }}
                            </p>
                            <p class="text-xs text-gray-400">Girls today</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Gender Parity --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 fade-up delay-2">
                <h3 class="text-sm font-bold text-gray-700 mb-4">Gender Distribution</h3>
                <div class="flex items-end gap-3 mb-4">
                    <div class="flex-1">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-semibold text-blue-700">Boys</span>
                            <span class="mono font-bold text-gray-700">{{ $genderSplit['boys_pct'] }}%</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill bg-blue-600" style="width: {{ $genderSplit['boys_pct'] }}%"></div>
                        </div>
                        <p class="text-sm font-bold text-gray-800 mono mt-1.5">{{ number_format($genderSplit['boys']) }}
                        </p>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-semibold text-pink-600">Girls</span>
                            <span class="mono font-bold text-gray-700">{{ $genderSplit['girls_pct'] }}%</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill bg-pink-500" style="width: {{ $genderSplit['girls_pct'] }}%"></div>
                        </div>
                        <p class="text-sm font-bold text-gray-800 mono mt-1.5">{{ number_format($genderSplit['girls']) }}
                        </p>
                    </div>
                </div>
                @php $parity = abs($genderSplit['boys_pct'] - $genderSplit['girls_pct']); @endphp
                <div class="pt-3 border-t border-gray-100 text-center">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                        {{ $parity <= 10 ? 'bg-green-100 text-green-700' : ($parity <= 20 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                        {{ $parity <= 10 ? '✓ Balanced' : ($parity <= 20 ? '⚠ Moderate Gap' : '⚠ High Imbalance') }}
                        &nbsp;· {{ $parity }}% gap
                    </span>
                </div>
            </div>

            {{-- OOSC + P2G --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 fade-up delay-3">
                <h3 class="text-sm font-bold text-gray-700 mb-4">OOSC & P2G Inclusion</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-semibold text-purple-700">Out-of-School Children</span>
                            <span class="mono font-bold text-gray-700">{{ $ooscPct }}%</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill bg-purple-500" style="width: {{ min(100, $ooscPct * 5) }}%"></div>
                        </div>
                        <p class="text-xl font-bold text-purple-700 mono mt-1.5">{{ number_format($ooscTotal) }}</p>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-semibold text-teal-600">Private to Government (P2G)</span>
                            <span class="mono font-bold text-gray-700">
                                {{ $totalAdmitted > 0 ? round(($p2pTotal / $totalAdmitted) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill bg-teal-500"
                                style="width: {{ $totalAdmitted > 0 ? min(100, round(($p2pTotal / $totalAdmitted) * 100, 1) * 5) : 0 }}%">
                            </div>
                        </div>
                        <p class="text-xl font-bold text-teal-600 mono mt-1.5">{{ number_format($p2pTotal) }}</p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-400">
                        {{ number_format($ooscTotal + $p2pTotal) }} total inclusion admissions
                    </p>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
         ROW 3 — 7-DAY TREND + TOP SCHOOLS
        ═══════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">

            {{-- 7-Day Trend Chart --}}
            <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 p-6 fade-up delay-2">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-sm font-bold text-gray-700">7-Day Admission Trend</h3>
                    <div class="flex gap-3 text-xs">
                        <span class="flex items-center gap-1.5"><span
                                class="w-2.5 h-2.5 rounded-sm bg-blue-600 inline-block"></span>Boys</span>
                        <span class="flex items-center gap-1.5"><span
                                class="w-2.5 h-2.5 rounded-sm bg-pink-500 inline-block"></span>Girls</span>
                    </div>
                </div>
                @php $maxTrend = $trendDays->max('total') ?: 1; $barMaxPx = 80; @endphp

                {{-- Number labels row --}}
                <div style="display:flex; gap:8px; margin-bottom:4px;">
                    @foreach ($trendDays as $day)
                        @php $isToday = $day['date'] === now()->format('D d'); @endphp
                        <div style="flex:1; text-align:center; font-size:9px; font-weight:700;
                                    color:{{ $isToday ? '#2563eb' : '#9ca3af' }}; font-family:monospace;">
                            {{ $day['total'] > 0 ? number_format($day['total']) : '' }}
                        </div>
                    @endforeach
                </div>

                {{-- Bar row — anchored to bottom --}}
                <div style="display:flex; gap:8px; align-items:flex-end; height:{{ $barMaxPx }}px;">
                    @foreach ($trendDays as $day)
                        @php
                            $boyPx  = $day['boys']  > 0 ? max(3, (int)round($day['boys']  / $maxTrend * $barMaxPx)) : 0;
                            $girlPx = $day['girls'] > 0 ? max(3, (int)round($day['girls'] / $maxTrend * $barMaxPx)) : 0;
                            $isToday = $day['date'] === now()->format('D d');
                        @endphp
                        <div style="flex:1; display:flex; gap:2px; align-items:flex-end; height:100%;">
                            <div style="flex:1; height:{{ $boyPx }}px;
                                        background:{{ $isToday ? '#2563eb' : '#93c5fd' }};
                                        border-radius:3px 3px 0 0;
                                        transition:height .5s cubic-bezier(.16,1,.3,1);"></div>
                            <div style="flex:1; height:{{ $girlPx }}px;
                                        background:{{ $isToday ? '#ec4899' : '#f9a8d4' }};
                                        border-radius:3px 3px 0 0;
                                        transition:height .5s cubic-bezier(.16,1,.3,1);"></div>
                        </div>
                    @endforeach
                </div>

                {{-- Date labels row --}}
                <div style="display:flex; gap:8px; margin-top:6px;">
                    @foreach ($trendDays as $day)
                        @php $isToday = $day['date'] === now()->format('D d'); @endphp
                        <div style="flex:1; text-align:center; font-size:10px; font-weight:600;
                                    color:{{ $isToday ? '#2563eb' : '#9ca3af' }};">
                            {{ $day['date'] }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top Schools --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 fade-up delay-3">
                <h3 class="text-sm font-bold text-gray-700 mb-4">Top 10 Schools</h3>
                @php $maxTop = $topSchools->max('total') ?: 1; @endphp
                <div class="space-y-2.5">
                    @foreach ($topSchools->take(10) as $i => $school)
                        <div>
                            <div class="flex justify-between items-baseline mb-1">
                                <p class="text-xs font-medium text-gray-700 truncate flex-1 pr-2">
                                    <span class="text-gray-400 mr-1">{{ $i + 1 }}.</span>{{ $school['name'] }}
                                </p>
                                <span
                                    class="text-xs font-bold mono text-blue-800 shrink-0">{{ number_format($school['total']) }}</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill bg-blue-500"
                                    style="width: {{ round(($school['total'] / $maxTop) * 100) }}%; opacity: {{ 1 - $i * 0.06 }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
         ROW 4 — SECTOR TABLE (full)
        ═══════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6 fade-up delay-3">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Sector-wise Performance</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Capacity · enrollment · admissions · fill rate</p>
                </div>
                <a href="{{ route('director.reports.sector') }}"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                    Full Report →
                </a>
            </div>

            <p class="block sm:hidden text-xs text-gray-400 px-4 pt-2 mb-2 flex items-center gap-1">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                Swipe right to see all columns
            </p>
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs uppercase text-gray-400">
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">
                                Sector</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wide hidden sm:table-cell">
                                Schools</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wide hidden sm:table-cell">
                                Capacity</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium text-amber-600 uppercase tracking-wide hidden md:table-cell">
                                Prior Enroll.</th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-emerald-600 uppercase tracking-wide">
                                Admitted</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium text-blue-600 uppercase tracking-wide hidden sm:table-cell">
                                Boys</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium text-pink-500 uppercase tracking-wide hidden sm:table-cell">
                                Girls</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium text-purple-600 uppercase tracking-wide hidden md:table-cell">
                                OOSC</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium text-indigo-600 uppercase tracking-wide hidden md:table-cell">
                                ⚙️ M.Tech</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium text-emerald-600 uppercase tracking-wide hidden lg:table-cell">
                                🏗️ Rooms</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wide hidden sm:table-cell">
                                Available</th>
                            <th
                                class="px-3 py-3 text-center text-xs font-medium bg-blue-50 text-blue-900 font-bold uppercase tracking-wide">
                                Fill Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($sectorSummary as $sec)
                            @php
                                $fillClass =
                                    $sec['fill_rate'] >= 90
                                        ? 'fill-bg-high'
                                        : ($sec['fill_rate'] >= 70
                                            ? 'fill-bg-medium'
                                            : 'fill-bg-low');
                            @endphp
                            <tr class="sector-row">
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    <p class="font-semibold text-gray-800">{{ $sec['name'] }}</p>
                                </td>
                                <td
                                    class="px-3 py-3 text-sm text-center text-gray-500 mono whitespace-nowrap hidden sm:table-cell">
                                    {{ $sec['schools'] }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-center font-medium text-gray-700 mono whitespace-nowrap hidden sm:table-cell">
                                    {{ number_format($sec['seats']) }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-center text-amber-600 mono whitespace-nowrap hidden md:table-cell">
                                    {{ number_format($sec['existing']) }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-center text-emerald-600 font-semibold mono whitespace-nowrap">
                                    {{ number_format($sec['admitted']) }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-center text-blue-600 mono whitespace-nowrap hidden sm:table-cell">
                                    {{ number_format($sec['boys']) }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-center text-pink-500 mono whitespace-nowrap hidden sm:table-cell">
                                    {{ number_format($sec['girls']) }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-center text-purple-600 mono whitespace-nowrap hidden md:table-cell">
                                    {{ number_format($sec['oosc']) }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-center text-indigo-700 font-semibold mono whitespace-nowrap hidden md:table-cell">
                                    {{ number_format($sec['matric_tech'] ?? 0) }}</td>
                                <td class="px-3 py-3 text-sm text-center mono whitespace-nowrap hidden lg:table-cell">
                                    @if (($sec['new_rooms_total'] ?? 0) > 0)
                                        <span class="text-emerald-700 font-semibold">{{ $sec['new_rooms_total'] }}</span>
                                        <span class="text-gray-400 text-xs block">{{ $sec['new_rooms_remaining'] }}
                                            avail.</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td
                                    class="px-3 py-3 text-sm text-center font-bold mono whitespace-nowrap hidden sm:table-cell {{ $sec['available'] > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ number_format($sec['available']) }}</td>
                                <td class="px-3 py-3 text-sm text-center bg-blue-50 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold mono {{ $fillClass }}">
                                        {{ $sec['fill_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-blue-100 bg-blue-50 font-bold text-sm">
                        <tr>
                            <td class="px-3 py-3.5 text-gray-700 whitespace-nowrap">SYSTEM TOTAL</td>
                            <td class="px-3 py-3.5 text-center text-gray-600 mono whitespace-nowrap hidden sm:table-cell">
                                {{ $totalSchools }}</td>
                            <td class="px-3 py-3.5 text-center text-blue-900 mono whitespace-nowrap hidden sm:table-cell">
                                {{ number_format($totalSeats) }}</td>
                            <td class="px-3 py-3.5 text-center text-amber-700 mono whitespace-nowrap hidden md:table-cell">
                                {{ number_format($totalExisting) }}
                            </td>
                            <td class="px-3 py-3.5 text-center text-emerald-700 mono whitespace-nowrap">
                                {{ number_format($totalAdmitted) }}
                            </td>
                            <td class="px-3 py-3.5 text-center text-blue-700 mono whitespace-nowrap hidden sm:table-cell">
                                {{ number_format($genderSplit['boys']) }}</td>
                            <td class="px-3 py-3.5 text-center text-pink-600 mono whitespace-nowrap hidden sm:table-cell">
                                {{ number_format($genderSplit['girls']) }}</td>
                            <td
                                class="px-3 py-3.5 text-center text-purple-700 mono whitespace-nowrap hidden md:table-cell">
                                {{ number_format($ooscTotal) }}</td>
                            <td
                                class="px-3 py-3.5 text-center text-indigo-700 mono whitespace-nowrap hidden md:table-cell">
                                {{ number_format($matricTechYear) }}
                            </td>
                            <td
                                class="px-3 py-3.5 text-center text-emerald-700 mono whitespace-nowrap hidden lg:table-cell">
                                {{ $newRoomsTotal > 0 ? number_format($newRoomsTotal) . ' / ' . number_format($newRoomsRemaining) : '—' }}
                            </td>
                            <td
                                class="px-3 py-3.5 text-center mono whitespace-nowrap hidden sm:table-cell {{ $totalAvailable > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ number_format($totalAvailable) }}</td>
                            <td class="px-3 py-3.5 text-center bg-blue-100 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold text-blue-900 bg-blue-200 mono">
                                    {{ $fillRate }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
         ROW 5 — SCHOOL TYPE + WEEKLY COMPLIANCE
        ═══════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 fade-up delay-4">

            {{-- School Type Breakdown --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-700 mb-4">Schools by Type</h3>
                <div class="space-y-3">
                    @php
                        $typeColors = [
                            'Primary' => '#3b82f6',
                            'Middle' => '#10b981',
                            'High' => '#8b5cf6',
                            'Higher Secondary' => '#f59e0b',
                            'Cambridge' => '#ef4444',
                        ];
                        $typeTotal = $byType->sum();
                    @endphp
                    @foreach ($byType as $type => $count)
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full shrink-0"
                                style="background: {{ $typeColors[$type] ?? '#6b7280' }}"></div>
                            <p class="text-sm text-gray-600 flex-1">{{ $type }}</p>
                            <p class="text-sm font-bold text-gray-800 mono w-8 text-right">{{ $count }}</p>
                            <div class="w-24 bar-track">
                                <div class="bar-fill"
                                    style="width:{{ $typeTotal > 0 ? round(($count / $typeTotal) * 100) : 0 }}%; background:{{ $typeColors[$type] ?? '#6b7280' }}">
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mono w-8 text-right">
                                {{ $typeTotal > 0 ? round(($count / $typeTotal) * 100) : 0 }}%</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Weekly Submission Compliance --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-700 mb-4">Weekly Submission Compliance</h3>
                @if ($weeklyCompliance->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">No submissions this week yet.</p>
                @else
                    <div class="space-y-2.5">
                        @foreach ($weeklyCompliance as $wc)
                            <div class="flex items-center gap-3">
                                <p class="text-xs font-bold text-gray-500 w-8">{{ $wc['day'] }}</p>
                                <div class="flex-1 bar-track">
                                    <div class="bar-fill {{ $wc['pct'] >= 80 ? 'bg-green-500' : ($wc['pct'] >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                                        style="width: {{ $wc['pct'] }}%"></div>
                                </div>
                                <p class="text-xs mono font-bold text-gray-700 w-16 text-right">
                                    {{ $wc['schools'] }} / {{ $configuredSchools }}
                                </p>
                                <span
                                    class="text-xs font-bold mono w-10 text-right
                                    {{ $wc['pct'] >= 80 ? 'text-green-600' : ($wc['pct'] >= 50 ? 'text-amber-600' : 'text-red-500') }}">
                                    {{ $wc['pct'] }}%
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
         QUICK LINKS
        ═══════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 fade-up delay-4">
            @foreach ([['route' => 'director.reports.sector', 'icon' => '🗺️', 'label' => 'Sector Report', 'color' => 'blue'], ['route' => 'director.reports.vacancy', 'icon' => '🪑', 'label' => 'Vacancy Report', 'color' => 'emerald'], ['route' => 'director.reports.master', 'icon' => '📋', 'label' => 'Master Report', 'color' => 'indigo'], ['route' => 'director.reports.gender', 'icon' => '⚖️', 'label' => 'Gender Analytics', 'color' => 'pink'], ['route' => 'director.reports.oosc', 'icon' => '🎒', 'label' => 'OOSC / P2G', 'color' => 'purple']] as $link)
                <a href="{{ route($link['route']) }}"
                    class="flex items-center gap-3 bg-white border border-gray-100 rounded-2xl px-5 py-4 hover:border-{{ $link['color'] }}-200 hover:bg-{{ $link['color'] }}-50 transition group">
                    <span class="text-xl">{{ $link['icon'] }}</span>
                    <span
                        class="text-sm font-semibold text-gray-700 group-hover:text-{{ $link['color'] }}-800">{{ $link['label'] }}</span>
                    <span class="ml-auto text-gray-300 group-hover:text-{{ $link['color'] }}-400 transition">→</span>
                </a>
            @endforeach
        </div>

    </div>
@endsection
