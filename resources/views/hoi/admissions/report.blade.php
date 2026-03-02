@extends('layouts.app')
@section('title', 'Admission Report')
@section('content')

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Report</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $institution->name }}</p>
        </div>
        <a href="{{ route('hoi.admissions.daily') }}" class="text-sm text-blue-600 hover:underline">Back to Daily Entry</a>
    </div>

    {{-- Date Range Filter --}}
    <form method="GET" action="{{ route('hoi.admissions.report') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <button type="submit"
                class="bg-blue-900 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                Apply Filter
            </button>
            <a href="{{ route('hoi.admissions.report') }}"
                class="px-6 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                Reset
            </a>
        </div>
    </form>

    {{-- Grand Total Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Admitted</p>
            <p class="text-3xl font-bold text-blue-900">{{ number_format($grandTotal) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Regular New</p>
            <p class="text-3xl font-bold text-blue-700">{{ number_format($grandRegular) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-purple-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">OOSC Campaign</p>
            <p class="text-3xl font-bold text-purple-700">{{ number_format($grandOosc) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Private to Public</p>
            <p class="text-3xl font-bold text-orange-600">{{ number_format($grandP2p) }}</p>
        </div>
    </div>

    {{-- ── CLASS-WISE SUMMARY (Document 7-Column Format) ──── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Class-wise Summary</h3>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ $from->format('d M Y') }} - {{ $to->format('d M Y') }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-center">Existing Enrollment</th>
                        <th class="px-4 py-3 text-center">Intake Capacity</th>
                        <th class="px-4 py-3 text-center text-green-600">Seats Available<br><span class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                        <th class="px-4 py-3 text-center text-blue-600">Newly Admitted<br><span class="normal-case font-normal text-gray-400">(Daily Updates)</span></th>
                        <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Enrollment<br><span class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($classes as $ic)
                        @php
                            $s = $classSummary[$ic->class_id] ?? null;
                            $admitted  = $s?->total ?? 0;
                            $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                            $totalEnrl = $ic->existing_enrollment + $admitted;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $ic->classModel?->name }}
                                @if ($ic->classModel?->is_ece)
                                    <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">ECE</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-orange-600 font-medium">
                                {{ number_format($ic->existing_enrollment) }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium text-gray-700">
                                {{ number_format($ic->total_seats) }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ number_format($available) }}
                            </td>
                            <td class="px-4 py-3 text-center text-blue-700 font-bold">
                                {{ number_format($admitted) }}
                                @if ($admitted > 0)
                                    <div class="text-xs text-gray-400 font-normal">
                                        Reg: {{ number_format(($s?->reg_boys ?? 0) + ($s?->reg_girls ?? 0)) }}
                                        &middot; OOSC: {{ number_format(($s?->oosc_boys ?? 0) + ($s?->oosc_girls ?? 0)) }}
                                        &middot; P2P: {{ number_format(($s?->p2p_boys ?? 0) + ($s?->p2p_girls ?? 0)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-blue-900 bg-blue-50">
                                {{ number_format($totalEnrl) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                    <tr>
                        <td class="px-4 py-3 text-gray-700">TOTAL</td>
                        <td class="px-4 py-3 text-center text-orange-600">
                            {{ number_format($classes->sum('existing_enrollment')) }}
                        </td>
                        <td class="px-4 py-3 text-center text-blue-900">
                            {{ number_format($classes->sum('total_seats')) }}
                        </td>
                        <td class="px-4 py-3 text-center {{ ($classes->sum('total_seats') - $classes->sum('existing_enrollment') - $grandTotal) > 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format(max(0, $classes->sum('total_seats') - $classes->sum('existing_enrollment') - $grandTotal)) }}
                        </td>
                        <td class="px-4 py-3 text-center text-blue-700">
                            {{ number_format($grandTotal) }}
                        </td>
                        <td class="px-4 py-3 text-center text-blue-900 bg-blue-100">
                            {{ number_format($classes->sum('existing_enrollment') + $grandTotal) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── DAY BY DAY BREAKDOWN ────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Day-by-Day Breakdown</h3>
        </div>

        @if ($dailyRows->isEmpty())
            <div class="px-6 py-10 text-center text-gray-400 text-sm">
                No admissions recorded for this date range.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Class</th>
                            <th class="px-4 py-3 text-center bg-blue-50 text-blue-600">Regular<br>Boys</th>
                            <th class="px-4 py-3 text-center bg-blue-50 text-pink-600">Regular<br>Girls</th>
                            <th class="px-4 py-3 text-center bg-purple-50 text-purple-600">OOSC<br>Boys</th>
                            <th class="px-4 py-3 text-center bg-purple-50 text-pink-600">OOSC<br>Girls</th>
                            <th class="px-4 py-3 text-center bg-orange-50 text-orange-600">P2P<br>Boys</th>
                            <th class="px-4 py-3 text-center bg-orange-50 text-pink-600">P2P<br>Girls</th>
                            <th class="px-4 py-3 text-center font-bold text-gray-700">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($dailyRows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">
                                    {{ $row->admission_date->format('d M Y') }}
                                </td>
                                <td class="px-4 py-2.5 font-medium text-gray-800">
                                    {{ $row->classModel?->name }}
                                </td>
                                <td class="px-4 py-2.5 text-center text-blue-700">{{ $row->boys_count }}</td>
                                <td class="px-4 py-2.5 text-center text-pink-700">{{ $row->girls_count }}</td>
                                <td class="px-4 py-2.5 text-center text-purple-700">{{ $row->oosc_boys }}</td>
                                <td class="px-4 py-2.5 text-center text-pink-700">{{ $row->oosc_girls }}</td>
                                <td class="px-4 py-2.5 text-center text-orange-700">{{ $row->p2p_boys }}</td>
                                <td class="px-4 py-2.5 text-center text-pink-700">{{ $row->p2p_girls }}</td>
                                <td class="px-4 py-2.5 text-center font-bold text-gray-900">
                                    {{ $row->totalAdmissions() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
