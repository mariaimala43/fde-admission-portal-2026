{{-- resources/views/fde/reports/oosc.blade.php --}}

@extends('layouts.app')
@section('title', 'OOSC & P2G Report')

@section('content')

    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">OOSC & Private to Government Tracking</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Academic Year: <strong>{{ $academicYear?->name ?? '—' }}</strong>
                &nbsp;·&nbsp; {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route($exportPrefix . '.reports.dashboard') }}"
                class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                Back
            </a>
            @can('reports.export')
                <a href="{{ route($exportPrefix.'.export.oosc', array_merge(request()->query(), ['format' => 'excel'])) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition">
                    Excel
                </a>
            @endcan
        </div>
    </div>

    <form method="GET" action="{{ route($exportPrefix.'.reports.oosc') }}"
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
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sector</label>
            <select name="sector_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm min-w-[160px]">
                <option value="">All Sectors</option>
                @foreach ($sectors as $s)
                    <option value="{{ $s->id }}" {{ $sectorId == $s->id ? 'selected' : '' }}>{{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-800 transition">
            Apply
        </button>
        <a href="{{ route($exportPrefix . '.reports.oosc') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">Reset</a>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-purple-600 rounded-xl p-4 text-white text-center">
            <p class="text-xs text-purple-200 mb-1">Total OOSC</p>
            <p class="text-2xl font-bold">{{ number_format($grandOosc) }}</p>
        </div>
        <div class="bg-orange-500 rounded-xl p-4 text-white text-center">
            <p class="text-xs text-orange-100 mb-1">Total P2G</p>
            <p class="text-2xl font-bold">{{ number_format($grandP2p) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1">Combined</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($grandOosc + $grandP2p) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1">Schools Reporting</p>
            <p class="text-2xl font-bold text-blue-700">
                {{ $ooscData->filter(fn($d) => $d->oosc_total + $d->p2p_total > 0)->count() }}
            </p>
        </div>
    </div>

    {{-- Sector Summary --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <h3 class="text-sm font-bold text-gray-800 mb-4">Sector-wise Summary</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="text-left px-4 py-3 font-medium">Sector</th>
                        <th class="text-center px-3 py-3 font-medium">OOSC Boys</th>
                        <th class="text-center px-3 py-3 font-medium">OOSC Girls</th>
                        <th class="text-center px-3 py-3 font-medium">OOSC Total</th>
                        <th class="text-center px-3 py-3 font-medium">P2G Boys</th>
                        <th class="text-center px-3 py-3 font-medium">P2G Girls</th>
                        <th class="text-center px-3 py-3 font-medium">P2G Total</th>
                        <th class="text-center px-3 py-3 font-medium">Combined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($sectorOosc as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $row['name'] }}</td>
                            <td class="px-3 py-3 text-center text-blue-600">{{ number_format($row['oosc_boys']) }}</td>
                            <td class="px-3 py-3 text-center text-pink-500">{{ number_format($row['oosc_girls']) }}</td>
                            <td class="px-3 py-3 text-center font-semibold text-purple-700">
                                {{ number_format($row['oosc_total']) }}</td>
                            <td class="px-3 py-3 text-center text-blue-600">{{ number_format($row['p2p_boys']) }}</td>
                            <td class="px-3 py-3 text-center text-pink-500">{{ number_format($row['p2p_girls']) }}</td>
                            <td class="px-3 py-3 text-center font-semibold text-orange-600">
                                {{ number_format($row['p2p_total']) }}</td>
                            <td class="px-3 py-3 text-center font-bold text-gray-800">
                                {{ number_format($row['oosc_total'] + $row['p2p_total']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- School Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800">School-wise Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="text-left px-5 py-3 font-medium">School</th>
                        <th class="text-left px-3 py-3 font-medium">Sector</th>
                        <th class="text-center px-3 py-3 font-medium">OOSC Boys</th>
                        <th class="text-center px-3 py-3 font-medium">OOSC Girls</th>
                        <th class="text-center px-3 py-3 font-medium">OOSC Total</th>
                        <th class="text-center px-3 py-3 font-medium">P2G Boys</th>
                        <th class="text-center px-3 py-3 font-medium">P2G Girls</th>
                        <th class="text-center px-3 py-3 font-medium">P2G Total</th>
                        <th class="text-center px-3 py-3 font-medium">Combined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($institutions as $inst)
                        @php $d = $ooscData[$inst->id] ?? null; @endphp
                        @if ($d && $d->oosc_total + $d->p2p_total > 0)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3 text-xs font-medium text-gray-800">{{ $inst->name }}</td>
                                <td class="px-3 py-3 text-xs text-gray-500">{{ $inst->sector?->name }}</td>
                                <td class="px-3 py-3 text-center text-blue-600">{{ number_format($d->oosc_boys) }}</td>
                                <td class="px-3 py-3 text-center text-pink-500">{{ number_format($d->oosc_girls) }}</td>
                                <td class="px-3 py-3 text-center font-semibold text-purple-700">
                                    {{ number_format($d->oosc_total) }}</td>
                                <td class="px-3 py-3 text-center text-blue-600">{{ number_format($d->p2p_boys) }}</td>
                                <td class="px-3 py-3 text-center text-pink-500">{{ number_format($d->p2p_girls) }}</td>
                                <td class="px-3 py-3 text-center font-semibold text-orange-600">
                                    {{ number_format($d->p2p_total) }}</td>
                                <td class="px-3 py-3 text-center font-bold text-gray-800">
                                    {{ number_format($d->oosc_total + $d->p2p_total) }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-gray-400">No data found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-blue-50 font-semibold text-sm border-t-2 border-blue-200">
                        <td class="px-5 py-3 text-blue-900" colspan="2">Grand Total</td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($ooscData->sum('oosc_boys')) }}
                        </td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($ooscData->sum('oosc_girls')) }}
                        </td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($grandOosc) }}</td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($ooscData->sum('p2p_boys')) }}
                        </td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($ooscData->sum('p2p_girls')) }}
                        </td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($grandP2p) }}</td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($grandOosc + $grandP2p) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

@endsection
