{{-- SAVE AS: resources/views/fde/reports/gender.blade.php --}}
@extends('layouts.app')
@section('title', 'Gender Analytics')

@section('content')

    @php
        $totalBoys = $overall->total_boys ?? 0;
        $totalGirls = $overall->total_girls ?? 0;
        $grandTotal = $totalBoys + $totalGirls;
        $boysPct = $grandTotal > 0 ? round(($totalBoys / $grandTotal) * 100, 1) : 0;
        $girlsPct = $grandTotal > 0 ? round(($totalGirls / $grandTotal) * 100, 1) : 0;
        $ratio = $totalGirls > 0 ? number_format($totalBoys / $totalGirls, 2) : '—';
        $parity = abs($boysPct - $girlsPct);

        $regBoys = $overall->reg_boys ?? 0;
        $regGirls = $overall->reg_girls ?? 0;
        $ooscBoys = $overall->oosc_boys ?? 0;
        $ooscGirls = $overall->oosc_girls ?? 0;
        $p2pBoys = $overall->p2p_boys ?? 0;
        $p2pGirls = $overall->p2p_girls ?? 0;

        // Sector chart data
        $sectorLabels = collect($bySector)->pluck('name')->toJson();
        $sectorBoysData = collect($bySector)->pluck('boys')->toJson();
        $sectorGirlsData = collect($bySector)->pluck('girls')->toJson();

        // Class chart data
        $classLabels = collect($byClass)->pluck('name')->toJson();
        $classBoysData = collect($byClass)->pluck('boys')->toJson();
        $classGirlsData = collect($byClass)->pluck('girls')->toJson();

        // Category breakdown for stacked bar
        $catLabels = json_encode(['Regular', 'OOSC Campaign', 'P2G Transfer']);
        $catBoys = json_encode([$regBoys, $ooscBoys, $p2pBoys]);
        $catGirls = json_encode([$regGirls, $ooscGirls, $p2pGirls]);
    @endphp

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Gender Analytics</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Academic Year: <strong>{{ $academicYear?->name ?? '—' }}</strong>
                &nbsp;·&nbsp; {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
            </p>
        </div>
        <a href="{{ url()->previous() }}"
            class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
            Back
        </a>
    </div>

    {{-- ── Date Filter ─────────────────────────────────────────────── --}}
    <form method="GET" action="{{ request()->url() }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
        </div>
        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
            Apply
        </button>
        <a href="{{ request()->url() }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">Reset</a>
    </form>

    {{-- ── KPI Cards ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

        <div class="bg-blue-700 rounded-xl p-5 text-white text-center shadow-sm">
            <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Total Boys</p>
            <p class="text-3xl font-bold">{{ number_format($totalBoys) }}</p>
            <p class="text-xs text-blue-200 mt-1">{{ $boysPct }}% of total</p>
        </div>

        <div class="bg-pink-500 rounded-xl p-5 text-white text-center shadow-sm">
            <p class="text-xs text-pink-100 uppercase tracking-wider mb-1">Total Girls</p>
            <p class="text-3xl font-bold">{{ number_format($totalGirls) }}</p>
            <p class="text-xs text-pink-100 mt-1">{{ $girlsPct }}% of total</p>
        </div>

        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Grand Total</p>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($grandTotal) }}</p>
            <p
                class="text-xs mt-1 font-semibold px-2 py-0.5 rounded-full inline-block
                {{ $parity <= 10 ? 'bg-green-100 text-green-700' : ($parity <= 20 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-600') }}">
                {{ $parity <= 10 ? '✓ Balanced' : ($parity <= 20 ? '⚠ Moderate gap' : '⚠ High gap') }}
            </p>
        </div>

        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Boy : Girl Ratio</p>
            <p class="text-3xl font-bold text-gray-800">{{ $ratio }} : 1</p>
            <p class="text-xs text-gray-400 mt-1">{{ $parity }}% gap</p>
        </div>
    </div>

    {{-- ── Row: Donut + Sector Bar ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

        {{-- Overall Donut --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-1">Overall Gender Split</h3>
            <p class="text-xs text-gray-400 mb-4">Distribution across all admission types</p>
            <div style="position:relative;height:260px;">
                <canvas id="overallPie"></canvas>
            </div>
            <div class="mt-4 space-y-3">
                <div>
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="flex items-center gap-1.5 font-semibold text-blue-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>Boys
                        </span>
                        <span class="font-bold text-gray-700">{{ $boysPct }}% &nbsp;·&nbsp;
                            {{ number_format($totalBoys) }}</span>
                    </div>
                    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width:{{ $boysPct }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="flex items-center gap-1.5 font-semibold text-pink-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-pink-500 inline-block"></span>Girls
                        </span>
                        <span class="font-bold text-gray-700">{{ $girlsPct }}% &nbsp;·&nbsp;
                            {{ number_format($totalGirls) }}</span>
                    </div>
                    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-pink-500 rounded-full" style="width:{{ $girlsPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Boys vs Girls by Sector --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-1">Boys vs Girls by Sector</h3>
            <p class="text-xs text-gray-400 mb-4">Grouped bar chart per sector</p>
            <div style="position:relative;height:260px;">
                <canvas id="sectorGenderBar"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Category Breakdown Cards ────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">

        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-6 bg-blue-500 rounded-full inline-block"></span>
                <p class="text-xs font-bold text-blue-700 uppercase tracking-wider">Regular</p>
            </div>
            <div class="flex justify-between items-end">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($regBoys) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Boys</p>
                </div>
                <div class="flex-1 mx-3">
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-1">
                        @php
                            $rTotal = $regBoys + $regGirls;
                            $rPct = $rTotal > 0 ? round(($regBoys / $rTotal) * 100) : 0;
                        @endphp
                        <div class="h-full rounded-full"
                            style="width:{{ $rPct }}%;background:linear-gradient(90deg,#3b82f6 0%,#ec4899 100%)">
                        </div>
                    </div>
                    <p class="text-xs text-center text-gray-400">{{ number_format($rTotal) }} total</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-pink-500">{{ number_format($regGirls) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Girls</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-purple-100 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-6 bg-purple-500 rounded-full inline-block"></span>
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider">OOSC Campaign</p>
            </div>
            <div class="flex justify-between items-end">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($ooscBoys) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Boys</p>
                </div>
                <div class="flex-1 mx-3">
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-1">
                        @php
                            $oTotal = $ooscBoys + $ooscGirls;
                            $oPct = $oTotal > 0 ? round(($ooscBoys / $oTotal) * 100) : 0;
                        @endphp
                        <div class="h-full rounded-full"
                            style="width:{{ $oPct }}%;background:linear-gradient(90deg,#8b5cf6 0%,#ec4899 100%)">
                        </div>
                    </div>
                    <p class="text-xs text-center text-gray-400">{{ number_format($oTotal) }} total</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-pink-500">{{ number_format($ooscGirls) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Girls</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-6 bg-orange-500 rounded-full inline-block"></span>
                <p class="text-xs font-bold text-orange-700 uppercase tracking-wider">P2G Transfer</p>
            </div>
            <div class="flex justify-between items-end">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($p2pBoys) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Boys</p>
                </div>
                <div class="flex-1 mx-3">
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-1">
                        @php
                            $pTotal = $p2pBoys + $p2pGirls;
                            $pPct = $pTotal > 0 ? round(($p2pBoys / $pTotal) * 100) : 0;
                        @endphp
                        <div class="h-full rounded-full"
                            style="width:{{ $pPct }}%;background:linear-gradient(90deg,#f97316 0%,#ec4899 100%)">
                        </div>
                    </div>
                    <p class="text-xs text-center text-gray-400">{{ number_format($pTotal) }} total</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-pink-500">{{ number_format($p2pGirls) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Girls</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Category Stacked Chart + Class Chart ────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-1">Boys vs Girls by Category</h3>
            <p class="text-xs text-gray-400 mb-4">Regular · OOSC · P2G breakdown</p>
            <div style="position:relative;height:260px;">
                <canvas id="categoryBar"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-1">Class-wise Gender Distribution</h3>
            <p class="text-xs text-gray-400 mb-4">Boys vs girls per class</p>
            <div style="position:relative;height:260px;">
                <canvas id="classBar"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Sector Table ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Sector-wise Gender Breakdown</h3>
                <p class="text-xs text-gray-400 mt-0.5">Boys, girls, total and visual ratio per sector</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-400 tracking-wider">
                        <th class="text-left px-5 py-3 font-semibold">Sector</th>
                        <th class="text-center px-4 py-3 font-semibold text-blue-600">Boys</th>
                        <th class="text-center px-4 py-3 font-semibold text-pink-500">Girls</th>
                        <th class="text-center px-4 py-3 font-semibold">Total</th>
                        <th class="text-left px-4 py-3 font-semibold w-44">B : G Split</th>
                        <th class="text-center px-4 py-3 font-semibold">Ratio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($bySector as $row)
                        @php
                            $rowTotal = $row['boys'] + $row['girls'];
                            $bPct = $rowTotal > 0 ? round(($row['boys'] / $rowTotal) * 100) : 0;
                            $gPct = 100 - $bPct;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-semibold text-gray-700">{{ $row['name'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-blue-600">{{ number_format($row['boys']) }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-pink-500">{{ number_format($row['girls']) }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-gray-800">{{ number_format($rowTotal) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <div class="flex h-2.5 rounded-full overflow-hidden flex-1">
                                        <div class="bg-blue-400 h-full transition-all"
                                            style="width:{{ $bPct }}%"></div>
                                        <div class="bg-pink-400 h-full transition-all"
                                            style="width:{{ $gPct }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400 w-12 text-right shrink-0">{{ $bPct }}%
                                        B</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-xs font-semibold text-gray-600">
                                {{ $row['girls'] > 0 ? number_format($row['boys'] / $row['girls'], 2) . ':1' : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400 text-sm">No data for selected
                                range.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-blue-100 bg-blue-50 font-bold text-sm">
                    <tr>
                        <td class="px-5 py-3 text-gray-700">TOTAL</td>
                        <td class="px-4 py-3 text-center text-blue-700">{{ number_format($totalBoys) }}</td>
                        <td class="px-4 py-3 text-center text-pink-600">{{ number_format($totalGirls) }}</td>
                        <td class="px-4 py-3 text-center text-gray-800">{{ number_format($grandTotal) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <div class="flex h-2.5 rounded-full overflow-hidden flex-1">
                                    <div class="bg-blue-500 h-full" style="width:{{ $boysPct }}%"></div>
                                    <div class="bg-pink-500 h-full" style="width:{{ $girlsPct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-12 text-right shrink-0">{{ $boysPct }}% B</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $ratio }}:1</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── Class Table ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800">Class-wise Gender Breakdown</h3>
            <p class="text-xs text-gray-400 mt-0.5">Only classes with at least one admission are shown</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-400 tracking-wider">
                        <th class="text-left px-5 py-3 font-semibold">Class</th>
                        <th class="text-center px-4 py-3 font-semibold text-blue-600">Boys</th>
                        <th class="text-center px-4 py-3 font-semibold text-pink-500">Girls</th>
                        <th class="text-center px-4 py-3 font-semibold">Total</th>
                        <th class="text-left px-4 py-3 font-semibold">Split</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($byClass as $row)
                        @php
                            $t = $row['boys'] + $row['girls'];
                            $bPct = $t > 0 ? round(($row['boys'] / $t) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-2.5 font-medium text-gray-700">{{ $row['name'] }}</td>
                            <td class="px-4 py-2.5 text-center text-blue-600 font-semibold">
                                {{ number_format($row['boys']) }}</td>
                            <td class="px-4 py-2.5 text-center text-pink-500 font-semibold">
                                {{ number_format($row['girls']) }}</td>
                            <td class="px-4 py-2.5 text-center font-bold text-gray-700">{{ number_format($t) }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex flex-1 h-2 rounded-full overflow-hidden">
                                        <div class="bg-blue-400 h-full" style="width:{{ $bPct }}%"></div>
                                        <div class="bg-pink-400 h-full" style="width:{{ 100 - $bPct }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400 w-10 shrink-0">{{ $bPct }}%B</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400 text-sm">No class data for
                                selected range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Inline chart data so JS can access it regardless of load order --}}
    <script>
        window._genderChartData = {
            totalBoys: {{ $totalBoys }},
            totalGirls: {{ $totalGirls }},
            grandTotal: {{ $grandTotal }},
            sectorLabels: {!! $sectorLabels !!},
            sectorBoys: {!! $sectorBoysData !!},
            sectorGirls: {!! $sectorGirlsData !!},
            catLabels: {!! $catLabels !!},
            catBoys: {!! $catBoys !!},
            catGirls: {!! $catGirls !!},
            classLabels: {!! $classLabels !!},
            classBoys: {!! $classBoysData !!},
            classGirls: {!! $classGirlsData !!}
        };
    </script>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function waitForChart() {
            if (typeof Chart === 'undefined') {
                return setTimeout(waitForChart, 30);
            }

            var d = window._genderChartData;
            var BLUE = '#3b82f6';
            var PINK = '#ec4899';
            var BLUE_A = 'rgba(59,130,246,0.15)';
            var PINK_A = 'rgba(236,72,153,0.15)';

            Chart.defaults.font.family = "'Outfit', sans-serif";
            Chart.defaults.color = '#6b7280';

            // ── 1. Overall Donut ─────────────────────────────────
            new Chart(document.getElementById('overallPie'), {
                type: 'doughnut',
                data: {
                    labels: ['Boys', 'Girls'],
                    datasets: [{
                        data: [d.totalBoys, d.totalGirls],
                        backgroundColor: [BLUE, PINK],
                        hoverBackgroundColor: ['#2563eb', '#db2777'],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 16,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    var pct = d.grandTotal > 0 ? Math.round(ctx.parsed / d.grandTotal *
                                        100) : 0;
                                    return ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString() + ' (' +
                                        pct + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // ── 2. Boys vs Girls by Sector ───────────────────────
            new Chart(document.getElementById('sectorGenderBar'), {
                type: 'bar',
                data: {
                    labels: d.sectorLabels,
                    datasets: [{
                            label: 'Boys',
                            data: d.sectorBoys,
                            backgroundColor: BLUE,
                            borderRadius: 4,
                            borderSkipped: false
                        },
                        {
                            label: 'Girls',
                            data: d.sectorGirls,
                            backgroundColor: PINK,
                            borderRadius: 4,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                padding: 16,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.04)'
                            }
                        }
                    }
                }
            });

            // ── 3. Boys vs Girls by Category ────────────────────
            new Chart(document.getElementById('categoryBar'), {
                type: 'bar',
                data: {
                    labels: d.catLabels,
                    datasets: [{
                            label: 'Boys',
                            data: d.catBoys,
                            backgroundColor: BLUE,
                            borderRadius: 6,
                            borderSkipped: false
                        },
                        {
                            label: 'Girls',
                            data: d.catGirls,
                            backgroundColor: PINK,
                            borderRadius: 6,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.04)'
                            }
                        }
                    }
                }
            });

            // ── 4. Class-wise Horizontal Bar ─────────────────────
            new Chart(document.getElementById('classBar'), {
                type: 'bar',
                data: {
                    labels: d.classLabels,
                    datasets: [{
                            label: 'Boys',
                            data: d.classBoys,
                            backgroundColor: BLUE_A,
                            borderColor: BLUE,
                            borderWidth: 1.5,
                            borderRadius: 4,
                            borderSkipped: false
                        },
                        {
                            label: 'Girls',
                            data: d.classGirls,
                            backgroundColor: PINK_A,
                            borderColor: PINK,
                            borderWidth: 1.5,
                            borderRadius: 4,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.04)'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        })();
    </script>
@endpush
