{{-- SAVE AS: resources/views/fde/reports/dashboard.blade.php --}}
@extends('layouts.app')
@section('title', 'Analytics Dashboard')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Analytics Dashboard</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $academicYear?->name ?? 'No active year' }}
                &middot; Live data as of {{ now()->format('d M Y, g:i A') }}
            </p>
        </div>
        {{-- Export button removed from here — it now lives on the Master Report page --}}
    </div>

    {{-- ── Grand Summary ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Seats</p>
            <p class="text-2xl font-bold text-blue-900 dark:text-blue-300">{{ number_format($totalSeats) }}</p>
        </div>
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-orange-100 dark:border-gray-700 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Promoted Students</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($totalExisting) }}</p>
        </div>
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-green-100 dark:border-gray-700 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Newly Admitted</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalAdmitted) }}</p>
        </div>
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-teal-100 dark:border-gray-700 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">⚙️ Matric Tech (Year)</p>
            <p class="text-2xl font-bold text-teal-700 dark:text-teal-300">{{ number_format($matricTechYear) }}</p>
            <p class="text-xs text-teal-500 mt-0.5">Today: {{ number_format($matricTechToday) }}</p>
        </div>
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-blue-100 dark:border-gray-700 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Seats Remaining</p>
            <p class="text-2xl font-bold {{ $totalRemaining > 0 ? 'text-blue-700' : 'text-red-500' }}">
                {{ number_format($totalRemaining) }}
            </p>
        </div>
        <div class="bg-blue-900 dark:bg-blue-950 rounded-xl shadow-sm p-5 text-center">
            <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Today's Admissions</p>
            <p class="text-2xl font-bold text-white">{{ number_format($todayTotals?->total ?? 0) }}</p>
            <p class="text-xs text-blue-300 mt-0.5">
                {{ $submittedToday }} / {{ $totalConfigured }} schools submitted
            </p>
        </div>
    </div>

    {{-- ── Row 1: Daily Trend + Gender ──────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

        {{-- Daily Admissions Trend (2/3 width) --}}
        <div
            class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Daily Admissions Trend</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Last 30 days — Total / Boys / Girls</p>
                </div>
                <div class="flex gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-3 h-1 bg-blue-600 rounded inline-block"></span>
                        Total</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-1 bg-sky-400 rounded inline-block"></span>
                        Boys</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-1 bg-pink-400 rounded inline-block"></span>
                        Girls</span>
                </div>
            </div>
            <div class="relative h-56">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        {{-- Gender Breakdown --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-1">Gender Breakdown</h3>
            <p class="text-xs text-gray-400 mb-4">All admissions split by gender &amp; category</p>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="text-center p-3 bg-sky-50 dark:bg-sky-950 rounded-xl">
                    <p class="text-2xl font-bold text-sky-700 dark:text-sky-300">
                        {{ number_format($grandTotals->all_boys ?? 0) }}</p>
                    <p class="text-xs text-sky-600 dark:text-sky-400 mt-0.5">All Boys</p>
                </div>
                <div class="text-center p-3 bg-pink-50 dark:bg-pink-950 rounded-xl">
                    <p class="text-2xl font-bold text-pink-600 dark:text-pink-300">
                        {{ number_format($grandTotals->all_girls ?? 0) }}</p>
                    <p class="text-xs text-pink-500 dark:text-pink-400 mt-0.5">All Girls</p>
                </div>
            </div>
            <div class="relative h-36">
                <canvas id="genderChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-3 text-xs text-center">
                <div>
                    <p class="font-bold text-gray-700 dark:text-gray-200">
                        {{ number_format(($genderData['reg_boys'] ?? 0) + ($genderData['reg_girls'] ?? 0)) }}</p>
                    <p class="text-gray-400">Regular</p>
                </div>
                <div>
                    <p class="font-bold text-gray-700 dark:text-gray-200">
                        {{ number_format(($genderData['oosc_boys'] ?? 0) + ($genderData['oosc_girls'] ?? 0)) }}</p>
                    <p class="text-gray-400">OOSC</p>
                </div>
                <div>
                    <p class="font-bold text-gray-700 dark:text-gray-200">
                        {{ number_format(($genderData['p2p_boys'] ?? 0) + ($genderData['p2p_girls'] ?? 0)) }}</p>
                    <p class="text-gray-400">P2G</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Sector Bar + Category Stacked ─────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Sector-wise Admissions</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Total admitted per sector</p>
                </div>
                <a href="{{ route('fde.reports.sector') }}" class="text-xs text-blue-700 hover:underline">Full Report →</a>
            </div>
            <div class="relative h-64">
                <canvas id="sectorChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-1">Category Breakdown by Sector</h3>
            <p class="text-xs text-gray-400 mb-4">Regular vs OOSC vs P2G stacked</p>
            <div class="relative h-64">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Row 3: Weekly Trend + OOSC by Sector ─────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-1">Weekly Admissions</h3>
            <p class="text-xs text-gray-400 mb-4">Last 8 weeks total</p>
            <div class="relative h-52">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-1">OUT of SCHOOL CHILDREN &amp; PRIVATE to
                GOVERNMENT by Sector</h3>
            <p class="text-xs text-gray-400 mb-4">Out-of-school children vs Parah-to-Pucca admissions</p>
            <div class="relative h-52">
                <canvas id="ooscChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── College & Construction Highlights ────────────────────────────────── --}}
    <div class="mb-5">
        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span>🏛️</span> Model Colleges &amp; New Construction
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Model Colleges Card --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-indigo-100 dark:border-indigo-900 shadow-sm overflow-hidden">
                <div class="bg-indigo-700 px-5 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-xs text-indigo-200 uppercase tracking-wider font-semibold">Model Colleges</p>
                        <p class="text-xl font-bold text-white mt-0.5">{{ $modelCollegeCount }} Colleges</p>
                    </div>
                    @role('fde_cell|aeo|director')
                        <a href="{{ route('fde.colleges.model') }}"
                            class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg transition font-medium">
                            View All →
                        </a>
                    @endrole
                </div>
                <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700">
                    <div class="p-4 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Admitted</p>
                        <p class="text-xl font-bold text-indigo-700 dark:text-indigo-300">
                            {{ number_format($modelCollegeStats?->total_admitted ?? 0) }}
                        </p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Boys</p>
                        <p class="text-xl font-bold text-sky-600 dark:text-sky-300">
                            {{ number_format($modelCollegeStats?->total_boys ?? 0) }}
                        </p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Girls</p>
                        <p class="text-xl font-bold text-pink-600 dark:text-pink-300">
                            {{ number_format($modelCollegeStats?->total_girls ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Ex-FG Colleges Card --}}
            {{-- <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-amber-100 dark:border-amber-900 shadow-sm overflow-hidden">
                <div class="bg-amber-600 px-5 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-xs text-amber-100 uppercase tracking-wider font-semibold">Ex-FG Colleges</p>
                        <p class="text-xl font-bold text-white mt-0.5">{{ $exFgCollegeCount }} Colleges</p>
                    </div>
                    @role('fde_cell|aeo|director')
                        <a href="{{ route('fde.colleges.ex-fg') }}"
                            class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg transition font-medium">
                            View All →
                        </a>
                    @endrole
                </div>
                <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700">
                    <div class="p-4 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Admitted</p>
                        <p class="text-xl font-bold text-amber-700 dark:text-amber-300">
                            {{ number_format($exFgCollegeStats?->total_admitted ?? 0) }}
                        </p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Boys</p>
                        <p class="text-xl font-bold text-sky-600 dark:text-sky-300">
                            {{ number_format($exFgCollegeStats?->total_boys ?? 0) }}
                        </p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Girls</p>
                        <p class="text-xl font-bold text-pink-600 dark:text-pink-300">
                            {{ number_format($exFgCollegeStats?->total_girls ?? 0) }}
                        </p>
                    </div>
                </div>
            </div> --}}

            {{-- ── New Construction Rooms Card ──────────────────────────────── --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-green-100 dark:border-green-900 shadow-sm overflow-hidden">
                <div class="bg-green-700 px-5 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-xs text-green-200 uppercase tracking-wider font-semibold">New Construction Rooms</p>
                        <p class="text-xl font-bold text-white mt-0.5">
                            {{-- {{ $newRooms->total_schools }} Schools --}}

                        </p>
                    </div>
                    @role('fde_cell')
                        <a href="{{ route('fde.rooms.index') }}"
                            class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg transition font-medium">
                            View All →
                        </a>
                    @endrole
                </div>
                <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700">
                    <div class="p-4 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Rooms</p>
                        <p class="text-xl font-bold text-green-700 dark:text-green-300">
                            {{-- {{ number_format($newRooms->total_rooms) }} --}}
                            132
                        </p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Schools</p>
                        <p class="text-xl font-bold text-sky-600 dark:text-sky-300">
                            {{-- {{ number_format($newRooms->rooms_allocated) }} --}}
                            72
                        </p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Colleges</p>
                        <p class="text-xl font-bold text-yellow-600 dark:text-yellow-300">
                            {{-- {{ number_format($newRooms->rooms_remaining) }} --}}
                            60
                        </p>
                    </div>
                </div>
                <div
                    class="grid grid-cols-2 divide-x divide-gray-100 dark:divide-gray-700 border-t border-gray-100 dark:border-gray-700">
                    <div class="px-4 py-2.5 text-center">
                        {{-- <p class="text-xs text-gray-400 mb-0.5">✅ Completed</p>
                        <p class="text-sm font-bold text-green-600 dark:text-green-400">{{ $newRooms->completed }} schools
                        </p> --}}
                        <p class="text-xs text-gray-400 mb-0.5">Total enrollment capacity 2880 student </p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        {{-- <p class="text-xs text-gray-400 mb-0.5">🔨 Near Completion</p>
                        <p class="text-sm font-bold text-yellow-600 dark:text-yellow-400">{{ $newRooms->near_completion }}
                            schools</p> --}}
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Schools Not Submitted Today ────────────────────────────────────────── --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">
                    📋 Schools / Colleges Not Submitted Today
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $notSubmittedSchools->count() }} of {{ $totalConfigured }} institutions have not entered data for
                    {{ \Carbon\Carbon::parse($today)->format('d M Y') }}
                </p>
            </div>
            @if ($submittedToday > 0)
                <span class="text-xs px-3 py-1.5 rounded-full bg-green-100 text-green-700 font-semibold">
                    ✅ {{ $submittedToday }} submitted
                </span>
            @endif
        </div>

        @if ($notSubmittedSchools->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-green-600 font-medium">
                🎉 All schools have submitted today's admission data!
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">School / College</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Sector</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Type</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Gender</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach ($notSubmittedSchools as $i => $school)
                            <tr class="hover:bg-red-50 dark:hover:bg-gray-750 transition-colors">
                                <td class="px-4 py-2.5 text-xs text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-2.5 font-medium text-gray-800 dark:text-gray-200">
                                    {{ $school->name }}
                                    @if ($school->code)
                                        <span class="ml-1 text-xs text-gray-400 font-mono">{{ $school->code }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-500 hidden sm:table-cell">
                                    {{ $school->sector?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-500 hidden md:table-cell">
                                    {{ $school->type ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-500 hidden md:table-cell">
                                    {{ ucfirst(str_replace('_', ' ', $school->gender ?? '')) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ══ Chart.js ═══════════════════════════════════════════════════════════ --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const trendLabels = @json($trendLabels);
        const trendTotal = @json($trendTotal);
        const trendBoys = @json($trendBoys);
        const trendGirls = @json($trendGirls);

        const sectorLabels = @json($sectorStats->pluck('name')->values());
        const sectorTotals = @json($sectorStats->pluck('adm_total')->values());
        const sectorBoys = @json($sectorStats->pluck('adm_boys')->values());
        const sectorGirls = @json($sectorStats->pluck('adm_girls')->values());

        const catBySector = {
            labels: @json($sectorStats->pluck('name')->values()),
            regular: @json($sectorStats->map(fn($s) => $s->adm_total - $s->adm_oosc - $s->adm_p2p)->values()),
            oosc: @json($sectorStats->pluck('adm_oosc')->values()),
            p2p: @json($sectorStats->pluck('adm_p2p')->values()),
        };

        const weekLabels = @json($weekLabels);
        const weekTotals = @json($weekTotals);
        const genderData = @json($genderData);
        const ooscData = {
            labels: @json($ooscBySector->pluck('name')),
            oosc: @json($ooscBySector->pluck('oosc')),
            p2p: @json($ooscBySector->pluck('p2p')),
        };

        const isDark = () => document.documentElement.classList.contains('dark');
        const gridColor = () => isDark() ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
        const labelColor = () => isDark() ? '#9ca3af' : '#6b7280';

        Chart.defaults.font.family = 'system-ui, sans-serif';
        Chart.defaults.font.size = 11;

        // 1. Daily Trend
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                        label: 'Total',
                        data: trendTotal,
                        borderColor: '#1d4ed8',
                        backgroundColor: 'rgba(29,78,216,0.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                        borderWidth: 2
                    },
                    {
                        label: 'Boys',
                        data: trendBoys,
                        borderColor: '#38bdf8',
                        backgroundColor: 'transparent',
                        tension: 0.35,
                        pointRadius: 1,
                        borderWidth: 1.5,
                        borderDash: [4, 3]
                    },
                    {
                        label: 'Girls',
                        data: trendGirls,
                        borderColor: '#f472b6',
                        backgroundColor: 'transparent',
                        tension: 0.35,
                        pointRadius: 1,
                        borderWidth: 1.5,
                        borderDash: [4, 3]
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: labelColor(),
                            maxTicksLimit: 10,
                            maxRotation: 0
                        },
                        grid: {
                            color: gridColor()
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: labelColor()
                        },
                        grid: {
                            color: gridColor()
                        }
                    }
                }
            }
        });

        // 2. Gender Doughnut
        new Chart(document.getElementById('genderChart'), {
            type: 'doughnut',
            data: {
                labels: ['Reg Boys', 'Reg Girls', 'OOSC Boys', 'OOSC Girls', 'P2G Boys', 'P2G Girls'],
                datasets: [{
                    data: [genderData.reg_boys, genderData.reg_girls, genderData.oosc_boys, genderData
                        .oosc_girls, genderData.p2p_boys, genderData.p2p_girls
                    ],
                    backgroundColor: ['#3b82f6', '#ec4899', '#0ea5e9', '#f9a8d4', '#6366f1', '#c4b5fd'],
                    borderWidth: 1,
                    borderColor: isDark() ? '#1f2937' : '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: labelColor(),
                            boxWidth: 10,
                            padding: 8,
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // 3. Sector Bar
        new Chart(document.getElementById('sectorChart'), {
            type: 'bar',
            data: {
                labels: sectorLabels,
                datasets: [{
                        label: 'Boys',
                        data: sectorBoys,
                        backgroundColor: '#3b82f6',
                        borderRadius: 3
                    },
                    {
                        label: 'Girls',
                        data: sectorGirls,
                        backgroundColor: '#f472b6',
                        borderRadius: 3
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: labelColor(),
                            boxWidth: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            footer: (items) => 'Total: ' + items.reduce((s, i) => s + i.parsed.y, 0)
                                .toLocaleString()
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: {
                            color: labelColor(),
                            maxRotation: 35
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            color: labelColor()
                        },
                        grid: {
                            color: gridColor()
                        }
                    }
                }
            }
        });

        // 4. Category by Sector
        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: catBySector.labels,
                datasets: [{
                        label: 'Regular',
                        data: catBySector.regular,
                        backgroundColor: '#3b82f6',
                        borderRadius: 3
                    },
                    {
                        label: 'OOSC',
                        data: catBySector.oosc,
                        backgroundColor: '#f59e0b',
                        borderRadius: 3
                    },
                    {
                        label: 'P2G',
                        data: catBySector.p2p,
                        backgroundColor: '#8b5cf6',
                        borderRadius: 3
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: labelColor(),
                            boxWidth: 12
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: {
                            color: labelColor(),
                            maxRotation: 35
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            color: labelColor()
                        },
                        grid: {
                            color: gridColor()
                        }
                    }
                }
            }
        });

        // 5. Weekly
        new Chart(document.getElementById('weeklyChart'), {
            type: 'bar',
            data: {
                labels: weekLabels.length ? weekLabels : ['No data yet'],
                datasets: [{
                    label: 'Weekly Total',
                    data: weekTotals.length ? weekTotals : [0],
                    backgroundColor: '#1d4ed8',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: labelColor()
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: labelColor()
                        },
                        grid: {
                            color: gridColor()
                        }
                    }
                }
            }
        });

        // 6. OOSC + P2G
        new Chart(document.getElementById('ooscChart'), {
            type: 'bar',
            data: {
                labels: ooscData.labels,
                datasets: [{
                        label: 'OOSC',
                        data: ooscData.oosc,
                        backgroundColor: '#f59e0b',
                        borderRadius: 3
                    },
                    {
                        label: 'P2G',
                        data: ooscData.p2p,
                        backgroundColor: '#8b5cf6',
                        borderRadius: 3
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: labelColor(),
                            boxWidth: 12
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: labelColor(),
                            maxRotation: 35
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: labelColor()
                        },
                        grid: {
                            color: gridColor()
                        }
                    }
                }
            }
        });
    </script>

@endsection
