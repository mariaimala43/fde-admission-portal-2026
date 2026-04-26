{{-- resources/views/fde/reports/vacancy.blade.php --}}

@extends('layouts.app')
@section('title', 'Vacancy Position Report')

@section('content')

    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Institution Vacancy Position</h2>
            <p class="text-sm text-gray-500 mt-0.5">Academic Year: <strong>{{ $academicYear?->name ?? '—' }}</strong></p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('fde.reports.dashboard') }}"
                class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                ← Dashboard
            </a>
            @can('reports.export')
                <a href="{{ route($exportPrefix.'.export.vacancy', array_merge(request()->query(), ['format' => 'excel'])) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition">
                    📊 Excel
                </a>
                <a href="{{ route($exportPrefix.'.export.vacancy', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium bg-red-600 text-white hover:bg-red-700 transition">
                    📄 PDF
                </a>
            @endcan
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route($exportPrefix.'.reports.vacancy') }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-wrap gap-4 items-end">
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
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
            <select name="type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <option value="">All Types</option>
                @foreach (['Primary', 'Middle', 'High', 'Higher Secondary'] as $t)
                    <option value="{{ $t }}" {{ $type == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Gender</label>
            <select name="gender" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                <option value="boys" {{ $gender == 'boys' ? 'selected' : '' }}>Boys</option>
                <option value="girls" {{ $gender == 'girls' ? 'selected' : '' }}>Girls</option>
                <option value="co_education" {{ $gender == 'co_education' ? 'selected' : '' }}>Co-Education</option>
            </select>
        </div>
        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-800 transition">
            Apply
        </button>
        <a href="{{ route('fde.reports.vacancy') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">Reset</a>

        <span class="ml-auto text-xs text-gray-400 self-center">
            Showing {{ $institutions->count() }} schools
        </span>
    </form>

    {{-- Summary Totals --}}
    @php
        $grandSeats = 0;
        $grandExisting = 0;
        $grandAdmitted = 0;
        $grandFilled = 0;
        foreach ($institutions as $inst) {
            $seats = $seatData->get($inst->id, collect());
            $ts = $seats->sum('total_seats');
            $te = $seats->sum('existing_enrollment');
            // $admData[$inst->id] is a Collection keyed by class_id — sum across all classes
            $adm = (int) ($admData->get($inst->id)?->sum('total') ?? 0);
            $grandSeats    += $ts;
            $grandExisting += $te;
            $grandAdmitted += $adm;
            $grandFilled   += $te + $adm;
        }
        // Apply max(0,...) once on the aggregate — not per-school — to avoid
        // over-enrolled schools clamping to 0 instead of offsetting the total.
        $grandRemaining = max(0, $grandSeats - $grandFilled);
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-5">
        <div class="bg-blue-900 rounded-xl p-4 text-white text-center">
            <p class="text-xs text-blue-200 mb-1">Total Seats</p>
            <p class="text-xl font-bold">{{ number_format($grandSeats) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1">Existing</p>
            <p class="text-xl font-bold text-orange-600">{{ number_format($grandExisting) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1">New Admissions</p>
            <p class="text-xl font-bold text-blue-700">{{ number_format($grandAdmitted) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1">Total Filled</p>
            <p class="text-xl font-bold text-gray-700">{{ number_format($grandFilled) }}</p>
        </div>
        <div class="{{ $grandRemaining > 0 ? 'bg-green-600' : 'bg-red-600' }} rounded-xl p-4 text-white text-center">
            <p class="text-xs text-white/70 mb-1">Remaining</p>
            <p class="text-xl font-bold">{{ number_format($grandRemaining) }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="text-left px-5 py-3 font-medium">School</th>
                        <th class="text-left px-3 py-3 font-medium">Sector</th>
                        <th class="text-center px-3 py-3 font-medium">Type</th>
                        <th class="text-center px-3 py-3 font-medium">Gender</th>
                        <th class="text-center px-3 py-3 font-medium">Seats</th>
                        <th class="text-center px-3 py-3 font-medium">Existing</th>
                        <th class="text-center px-3 py-3 font-medium">Admitted</th>
                        <th class="text-center px-3 py-3 font-medium">Filled</th>
                        <th class="text-center px-3 py-3 font-medium">Remaining</th>
                        <th class="text-center px-3 py-3 font-medium">Fill %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($institutions as $inst)
                        @php
                            $seats = $seatData->get($inst->id, collect());
                            $totalS = $seats->sum('total_seats');
                            $totalE = $seats->sum('existing_enrollment');
                            // $admData[$inst->id] is a Collection keyed by class_id — sum across all classes
                            $admitted = (int) ($admData->get($inst->id)?->sum('total') ?? 0);
                            $filled = $totalE + $admitted;
                            $remaining = max(0, $totalS - $filled);
                            $fillPct = $totalS > 0 ? round(($filled / $totalS) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-800">{{ $inst->name }}</p>
                                <p class="text-xs text-gray-400">{{ $inst->code }}</p>
                            </td>
                            <td class="px-3 py-3 text-gray-600 text-xs">{{ $inst->sector?->name }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                    {{ $inst->type }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center text-xs text-gray-500">
                                {{ ucfirst(str_replace('_', ' ', $inst->gender)) }}
                            </td>
                            <td class="px-3 py-3 text-center font-medium text-gray-700">{{ number_format($totalS) }}</td>
                            <td class="px-3 py-3 text-center text-orange-600">{{ number_format($totalE) }}</td>
                            <td class="px-3 py-3 text-center text-blue-700">{{ number_format($admitted) }}</td>
                            <td class="px-3 py-3 text-center font-medium text-gray-700">{{ number_format($filled) }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="font-bold {{ $remaining > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ number_format($remaining) }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full
                                            {{ $fillPct >= 90 ? 'bg-red-500' : ($fillPct >= 70 ? 'bg-yellow-400' : 'bg-green-500') }}"
                                            style="width: {{ min(100, $fillPct) }}%"></div>
                                    </div>
                                    <span
                                        class="text-xs font-semibold w-8 text-right
                                        {{ $fillPct >= 90 ? 'text-red-500' : ($fillPct >= 70 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ $fillPct }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-gray-400">No schools found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-blue-50 font-semibold text-sm border-t-2 border-blue-200">
                        <td class="px-5 py-3 text-blue-900" colspan="4">Grand Total</td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($grandSeats) }}</td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($grandExisting) }}</td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($grandAdmitted) }}</td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($grandFilled) }}</td>
                        <td class="px-3 py-3 text-center text-blue-900">{{ number_format($grandRemaining) }}</td>
                        <td class="px-3 py-3 text-center text-blue-900">
                            {{ $grandSeats > 0 ? round(($grandFilled / $grandSeats) * 100) : 0 }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

@endsection
