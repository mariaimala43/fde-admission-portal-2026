{{-- resources/views/fde/reports/gender.blade.php --}}

@extends('layouts.app')
@section('title', 'Gender Analytics')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Gender Analytics</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Academic Year: <strong>{{ $academicYear?->name ?? '—' }}</strong>
                &nbsp;·&nbsp; {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
            </p>
        </div>
        <a href="{{ route('fde.reports.dashboard') }}"
            class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
            Back
        </a>
    </div>

    <form method="GET" action="{{ route('fde.reports.gender') }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-800 transition">
            Apply
        </button>
        <a href="{{ route('fde.reports.gender') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">Reset</a>
    </form>

    {{-- Overall Gender Cards --}}
    @php
        $totalBoys = $overall->total_boys ?? 0;
        $totalGirls = $overall->total_girls ?? 0;
        $grandTotal = $totalBoys + $totalGirls;
        $boysPct = $grandTotal > 0 ? round(($totalBoys / $grandTotal) * 100) : 0;
        $girlsPct = $grandTotal > 0 ? round(($totalGirls / $grandTotal) * 100) : 0;
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-700 rounded-xl p-4 text-white text-center">
            <p class="text-xs text-blue-200 mb-1">Total Boys</p>
            <p class="text-2xl font-bold">{{ number_format($totalBoys) }}</p>
            <p class="text-xs text-blue-200 mt-1">{{ $boysPct }}% of total</p>
        </div>
        <div class="bg-pink-500 rounded-xl p-4 text-white text-center">
            <p class="text-xs text-pink-100 mb-1">Total Girls</p>
            <p class="text-2xl font-bold">{{ number_format($totalGirls) }}</p>
            <p class="text-xs text-pink-100 mt-1">{{ $girlsPct }}% of total</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1">Grand Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($grandTotal) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1">Boy:Girl Ratio</p>
            <p class="text-2xl font-bold text-gray-800">
                {{ $totalGirls > 0 ? number_format($totalBoys / $totalGirls, 2) : '—' }} : 1
            </p>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-2 gap-5 mb-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4">Overall Gender Split</h3>
            <canvas id="overallPie" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4">Boys vs Girls by Sector</h3>
            <canvas id="sectorGenderBar" height="200"></canvas>
        </div>
    </div>

    {{-- Category Breakdown --}}
    <div class="grid grid-cols-3 gap-4 mb-5">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">Regular</p>
            <div class="flex justify-between text-sm">
                <span class="text-blue-700">Boys: <strong>{{ number_format($overall->reg_boys ?? 0) }}</strong></span>
                <span class="text-pink-600">Girls: <strong>{{ number_format($overall->reg_girls ?? 0) }}</strong></span>
            </div>
        </div>
        <div class="bg-purple-50 border border-purple-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider mb-2">OOSC Campaign</p>
            <div class="flex justify-between text-sm">
                <span class="text-blue-700">Boys: <strong>{{ number_format($overall->oosc_boys ?? 0) }}</strong></span>
                <span class="text-pink-600">Girls: <strong>{{ number_format($overall->oosc_girls ?? 0) }}</strong></span>
            </div>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-orange-600 uppercase tracking-wider mb-2">P2P Transfer</p>
            <div class="flex justify-between text-sm">
                <span class="text-blue-700">Boys: <strong>{{ number_format($overall->p2p_boys ?? 0) }}</strong></span>
                <span class="text-pink-600">Girls: <strong>{{ number_format($overall->p2p_girls ?? 0) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Sector Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <h3 class="text-sm font-bold text-gray-800 mb-4">Sector-wise Gender Breakdown</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="text-left px-4 py-3 font-medium">Sector</th>
                        <th class="text-center px-3 py-3 font-medium">Boys</th>
                        <th class="text-center px-3 py-3 font-medium">Girls</th>
                        <th class="text-center px-3 py-3 font-medium">Total</th>
                        <th class="text-left px-3 py-3 font-medium w-40">Ratio (B:G)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($bySector as $row)
                        @php
                            $rowTotal = $row['boys'] + $row['girls'];
                            $bPct = $rowTotal > 0 ? round(($row['boys'] / $rowTotal) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $row['name'] }}</td>
                            <td class="px-3 py-3 text-center text-blue-600 font-medium">{{ number_format($row['boys']) }}
                            </td>
                            <td class="px-3 py-3 text-center text-pink-500 font-medium">{{ number_format($row['girls']) }}
                            </td>
                            <td class="px-3 py-3 text-center font-bold text-gray-800">{{ number_format($rowTotal) }}</td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-1 text-xs">
                                    <div class="bg-blue-200 rounded-l h-3"
                                        style="width: {{ $bPct }}px; min-width: 2px"></div>
                                    <div class="bg-pink-200 rounded-r h-3"
                                        style="width: {{ 100 - $bPct }}px; min-width: 2px"></div>
                                    <span class="text-gray-500 ml-1">{{ $bPct }}% B</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Class Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-bold text-gray-800 mb-4">Class-wise Gender Breakdown</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="text-left px-4 py-3 font-medium">Class</th>
                        <th class="text-center px-3 py-3 font-medium">Boys</th>
                        <th class="text-center px-3 py-3 font-medium">Girls</th>
                        <th class="text-center px-3 py-3 font-medium">Total</th>
                        <th class="text-left px-3 py-3 font-medium">Split</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($byClass as $row)
                        @php
                            $t = $row['boys'] + $row['girls'];
                            $bPct = $t > 0 ? round(($row['boys'] / $t) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $row['name'] }}</td>
                            <td class="px-3 py-3 text-center text-blue-600">{{ number_format($row['boys']) }}</td>
                            <td class="px-3 py-3 text-center text-pink-500">{{ number_format($row['girls']) }}</td>
                            <td class="px-3 py-3 text-center font-bold text-gray-700">{{ number_format($t) }}</td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-1">
                                    <div class="flex-1 flex h-2 rounded overflow-hidden">
                                        <div class="bg-blue-400 h-2" style="width: {{ $bPct }}%"></div>
                                        <div class="bg-pink-400 h-2" style="width: {{ 100 - $bPct }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400 w-10">{{ $bPct }}%B</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Overall pie
        new Chart(document.getElementById('overallPie'), {
            type: 'doughnut',
            data: {
                labels: ['Boys', 'Girls'],
                datasets: [{
                    data: [{{ $totalBoys }}, {{ $totalGirls }}],
                    backgroundColor: ['#3b82f6', '#ec4899'],
                    borderWidth: 3,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Sector bar
        new Chart(document.getElementById('sectorGenderBar'), {
            type: 'bar',
            data: {
                labels: @json(collect($bySector)->pluck('name')),
                datasets: [{
                        label: 'Boys',
                        data: @json(collect($bySector)->pluck('boys')),
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                    },
                    {
                        label: 'Girls',
                        data: @json(collect($bySector)->pluck('girls')),
                        backgroundColor: '#ec4899',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12
                        }
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
    </script>
@endpush
