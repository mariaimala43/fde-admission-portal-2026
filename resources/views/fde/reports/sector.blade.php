{{-- resources/views/fde/reports/sector.blade.php --}}

@extends('layouts.app')
@section('title', 'Sector & UC Report')

@section('content')

    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Sector / UC Wise Report</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Academic Year: <strong>{{ $academicYear?->name ?? '—' }}</strong>
                &nbsp;·&nbsp; {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('fde.reports.dashboard') }}"
                class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                ← Dashboard
            </a>
            @can('reports.export')
                @if ($exportPrefix !== 'aeo')
                    <a href="{{ route($exportPrefix . '.export.master', array_merge(request()->query(), ['format' => 'excel'])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition">
                        📊 Excel
                    </a>
                    <a href="{{ route($exportPrefix . '.export.master', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-red-600 text-white hover:bg-red-700 transition">
                        📄 PDF
                    </a>
                @endif
            @endcan
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route($exportPrefix . '.reports.sector') }}"
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
        <a href="{{ route('fde.reports.sector') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">Reset</a>
    </form>

    {{-- Sectors --}}
    @foreach ($sectorReport as $item)
        @php
            $filled = $item['total_existing'] + $item['total_admitted'];
            $rem = max(0, $item['total_seats'] - $filled);
            $fillRate = $item['total_seats'] > 0 ? round(($filled / $item['total_seats']) * 100) : 0;
            // Pre-compute UC count here so we never put {{ }} inside an Alpine x-text string.
            // Mixing Blade interpolation inside Alpine attribute strings causes a PHP parse error
            // because Blade processes the file first and the \' escapes break PHP string parsing.
$ucCount   = $item['uc_breakdown']->count();
            $instCount = $item['inst_breakdown']->count();
            $expandLabel = $ucCount
                ? "▼ Show {$ucCount} UCs"
                : ($instCount ? "▼ Show {$instCount} institutions" : '');
            $collapseLabel = $ucCount ? '▲ Hide UCs' : '▲ Hide institutions';
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 overflow-hidden" x-data="{ open: false }">

            {{-- Sector header --}}
            <div class="bg-blue-900 px-5 py-4 flex flex-wrap justify-between items-center gap-3 {{ ($ucCount || $instCount) ? 'cursor-pointer select-none' : '' }}"
                @if($ucCount || $instCount) @click="open = !open" @endif>
                <div>
                    <h3 class="text-base font-bold text-white">{{ $item['sector']->name }}</h3>
                    <p class="text-xs text-blue-200 mt-0.5">
                        {{ $item['school_count'] }} schools &nbsp;·&nbsp; Code: {{ $item['sector']->code }}
                        @if($ucCount || $instCount)
                            &nbsp;·&nbsp;
                            <span x-text="open ? '{{ $collapseLabel }}' : '{{ $expandLabel }}'"></span>
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-5 text-center items-center">
                    <div>
                        <p class="text-lg font-bold text-white">{{ number_format($item['total_admitted']) }}</p>
                        <p class="text-xs text-blue-200">Admitted</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-white">{{ number_format($item['total_oosc']) }}</p>
                        <p class="text-xs text-blue-200">OOSC</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-white">{{ number_format($item['total_p2p']) }}</p>
                        <p class="text-xs text-blue-200">P2G</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-white">{{ $fillRate }}%</p>
                        <p class="text-xs text-blue-200">Fill Rate</p>
                    </div>
                    {{-- Chevron toggle indicator --}}
                    <div class="ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-blue-200 transition-transform duration-300"
                            :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Sector summary strip --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-px bg-gray-100">
                <div class="bg-white px-4 py-3 text-center">
                    <p class="text-xs text-gray-400 mb-0.5">Total Seats</p>
                    <p class="font-bold text-gray-700">{{ number_format($item['total_seats']) }}</p>
                </div>
                <div class="bg-white px-4 py-3 text-center">
                    <p class="text-xs text-gray-400 mb-0.5">Existing</p>
                    <p class="font-bold text-orange-600">{{ number_format($item['total_existing']) }}</p>
                </div>
                <div class="bg-white px-4 py-3 text-center">
                    <p class="text-xs text-gray-400 mb-0.5">Boys</p>
                    <p class="font-bold text-blue-600">{{ number_format($item['total_boys']) }}</p>
                </div>
                <div class="bg-white px-4 py-3 text-center">
                    <p class="text-xs text-gray-400 mb-0.5">Girls</p>
                    <p class="font-bold text-pink-500">{{ number_format($item['total_girls']) }}</p>
                </div>
                <div class="bg-white px-4 py-3 text-center">
                    <p class="text-xs text-gray-400 mb-0.5">Remaining</p>
                    <p class="font-bold {{ $rem > 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ number_format($rem) }}
                    </p>
                </div>
            </div>

            {{-- Breakdown table (UC-based for normal sectors; institution-based for sectors with no UCs e.g. Model Colleges) --}}
            @if ($item['uc_breakdown']->count() || $item['inst_breakdown']->count())
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider">
                                    <th class="text-left px-5 py-3 font-medium">
                                        {{ $item['uc_breakdown']->count() ? 'Union Council' : 'Institution' }}
                                    </th>
                                    @if ($item['uc_breakdown']->count())
                                        <th class="text-center px-3 py-3 font-medium">Schools</th>
                                    @endif
                                    <th class="text-center px-3 py-3 font-medium">Seats</th>
                                    <th class="text-center px-3 py-3 font-medium">Existing</th>
                                    <th class="text-center px-3 py-3 font-medium">Boys</th>
                                    <th class="text-center px-3 py-3 font-medium">Girls</th>
                                    <th class="text-center px-3 py-3 font-medium">OOSC</th>
                                    <th class="text-center px-3 py-3 font-medium">P2G</th>
                                    <th class="text-center px-3 py-3 font-medium">Total</th>
                                    <th class="text-center px-3 py-3 font-medium">Fill %</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @if ($item['uc_breakdown']->count())
                                    {{-- Normal sector: UC rows --}}
                                    @foreach ($item['uc_breakdown'] as $uc)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-5 py-3 font-medium text-gray-700">
                                                {{ $uc->name }}
                                                <span class="text-xs text-gray-400 ml-1">({{ $uc->code }})</span>
                                            </td>
                                            <td class="px-3 py-3 text-center text-gray-600">{{ $uc->school_count }}</td>
                                            <td class="px-3 py-3 text-center text-gray-600">{{ number_format($uc->total_seats) }}</td>
                                            <td class="px-3 py-3 text-center text-orange-600">{{ number_format($uc->total_existing) }}</td>
                                            <td class="px-3 py-3 text-center text-blue-600">{{ number_format($uc->total_boys) }}</td>
                                            <td class="px-3 py-3 text-center text-pink-500">{{ number_format($uc->total_girls) }}</td>
                                            <td class="px-3 py-3 text-center text-purple-600">{{ number_format($uc->total_oosc) }}</td>
                                            <td class="px-3 py-3 text-center text-orange-500">{{ number_format($uc->total_p2p) }}</td>
                                            <td class="px-3 py-3 text-center font-semibold text-gray-800">{{ number_format($uc->total_admitted) }}</td>
                                            <td class="px-3 py-3 text-center">
                                                <span class="text-xs font-bold {{ $uc->fill_rate >= 90 ? 'text-red-500' : ($uc->fill_rate >= 70 ? 'text-yellow-600' : 'text-green-600') }}">
                                                    {{ $uc->fill_rate }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- No-UC sector (Model Colleges): institution rows --}}
                                    @foreach ($item['inst_breakdown'] as $inst)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-5 py-3 font-medium text-gray-700">
                                                {{ $inst->name }}
                                                @if($inst->code)
                                                    <span class="text-xs text-gray-400 ml-1">({{ $inst->code }})</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 text-center text-gray-600">{{ number_format($inst->total_seats) }}</td>
                                            <td class="px-3 py-3 text-center text-orange-600">{{ number_format($inst->total_existing) }}</td>
                                            <td class="px-3 py-3 text-center text-blue-600">{{ number_format($inst->total_boys) }}</td>
                                            <td class="px-3 py-3 text-center text-pink-500">{{ number_format($inst->total_girls) }}</td>
                                            <td class="px-3 py-3 text-center text-purple-600">{{ number_format($inst->total_oosc) }}</td>
                                            <td class="px-3 py-3 text-center text-orange-500">{{ number_format($inst->total_p2p) }}</td>
                                            <td class="px-3 py-3 text-center font-semibold text-gray-800">{{ number_format($inst->total_admitted) }}</td>
                                            <td class="px-3 py-3 text-center">
                                                <span class="text-xs font-bold {{ $inst->fill_rate >= 90 ? 'text-red-500' : ($inst->fill_rate >= 70 ? 'text-yellow-600' : 'text-green-600') }}">
                                                    {{ $inst->fill_rate }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="bg-blue-50 font-semibold text-sm border-t-2 border-blue-200">
                                    <td class="px-5 py-3 text-blue-900">Sector Total</td>
                                    @if ($item['uc_breakdown']->count())
                                        <td class="px-3 py-3 text-center text-blue-900">{{ $item['school_count'] }}</td>
                                    @endif
                                    <td class="px-3 py-3 text-center text-blue-900">{{ number_format($item['total_seats']) }}</td>
                                    <td class="px-3 py-3 text-center text-blue-900">{{ number_format($item['total_existing']) }}</td>
                                    <td class="px-3 py-3 text-center text-blue-900">{{ number_format($item['total_boys']) }}</td>
                                    <td class="px-3 py-3 text-center text-blue-900">{{ number_format($item['total_girls']) }}</td>
                                    <td class="px-3 py-3 text-center text-blue-900">{{ number_format($item['total_oosc']) }}</td>
                                    <td class="px-3 py-3 text-center text-blue-900">{{ number_format($item['total_p2p']) }}</td>
                                    <td class="px-3 py-3 text-center text-blue-900">{{ number_format($item['total_admitted']) }}</td>
                                    <td class="px-3 py-3 text-center text-blue-900">{{ $fillRate }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>{{-- end x-show wrapper --}}
            @endif

        </div>
    @endforeach

@endsection
