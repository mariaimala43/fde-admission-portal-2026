@extends('layouts.app')
@section('title', $institution->name . ' — College Profile')

@section('content')

    @php
        $isModel = $institution->type === 'Model College';
        $typeEmoji = $isModel ? '🏛️' : '🎓';
        $typeSlug = $isModel ? 'model' : 'ex-fg';
        $backRoute = $isModel ? route('fde.colleges.model') : route('fde.colleges.ex-fg');
        $exportUrl = route('fde.colleges.export-pdf', $typeSlug);
    @endphp

    {{-- ── Back + Export ────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            @role('fde_cell')
                <a href="{{ $backRoute }}" class="text-gray-400 hover:text-gray-600 transition text-sm">← Back</a>
            @endrole
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ $typeEmoji }} {{ $institution->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $institution->type }}
                    @if ($institution->code)
                        &middot; EMIS: <span class="font-mono">{{ $institution->code }}</span>
                    @endif
                    @if ($institution->ib_number)
                        &middot; IB: <span class="font-mono">{{ $institution->ib_number }}</span>
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ $exportUrl }}"
            class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            ⬇️ Export PDF
        </a>
    </div>

    <div class="space-y-5">

        {{-- ── Section 1: College Info ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-blue-900 text-white px-5 py-3">
                <h3 class="text-sm font-bold tracking-wide uppercase">College Information</h3>
            </div>
            <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">EMIS Code</p>
                    <p class="text-sm font-mono text-gray-800">{{ $institution->code ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">IB Number</p>
                    <p class="text-sm font-mono text-gray-800">{{ $institution->ib_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Category</p>
                    <span
                        class="inline-block text-xs font-bold px-2 py-0.5 rounded-full
                        {{ $isModel ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $institution->type }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Gender</p>
                    <p class="text-sm text-gray-700">
                        @if ($institution->gender === 'boys')
                            ♂ Boys
                        @elseif ($institution->gender === 'girls')
                            ♀ Girls
                        @else
                            Co-Education
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Union Council</p>
                    <p class="text-sm text-gray-700">
                        {{ $institution->unionCouncil?->code }} — {{ $institution->unionCouncil?->name ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Sector</p>
                    <p class="text-sm text-gray-700">{{ $institution->sector?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Shift</p>
                    <p class="text-sm text-gray-700 capitalize">{{ $institution->shift ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Address</p>
                    <p class="text-sm text-gray-700">{{ $institution->address ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- ── Section 2: Current HOI ───────────────────────────────────────── --}}
        {{--
            HOI info is now sourced directly from institution.hoi_name / hoi_contact
            (model fields added via migration), with the linked user record as fallback.
        --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-violet-700 text-white px-5 py-3">
                <h3 class="text-sm font-bold tracking-wide uppercase">Current HOI / Principal</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Name</p>
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $institution->hoi_name ?: $hoi?->name ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Contact / Phone</p>
                    <p class="text-sm font-mono text-gray-700">
                        {{ $institution->hoi_contact ?: $hoi?->phone ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Email</p>
                    <p class="text-sm text-gray-700">{{ $hoi?->email ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- ── Section 3: Admission Stats ───────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-emerald-700 text-white px-5 py-3 flex justify-between items-center">
                <h3 class="text-sm font-bold tracking-wide uppercase">Admission Statistics</h3>
                <span class="text-xs text-emerald-200">{{ $academicYear?->name ?? 'Active Year' }}</span>
            </div>
            <div class="p-5">

                {{-- Grand totals --}}
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center bg-green-50 rounded-xl p-4">
                        <p class="text-2xl font-bold text-green-700">
                            {{ number_format($totals->total_admitted ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Total Admitted</p>
                    </div>
                    <div class="text-center bg-sky-50 rounded-xl p-4">
                        <p class="text-2xl font-bold text-sky-700">
                            {{ number_format($totals->total_boys ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Boys</p>
                    </div>
                    <div class="text-center bg-pink-50 rounded-xl p-4">
                        <p class="text-2xl font-bold text-pink-600">
                            {{ number_format($totals->total_girls ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Girls</p>
                    </div>
                </div>

                {{-- Class-wise breakdown --}}
                @if ($classSummary->isNotEmpty())
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Class-wise Breakdown</h4>
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Class</th>
                                    <th class="px-4 py-2.5 text-center">Boys</th>
                                    <th class="px-4 py-2.5 text-center">Girls</th>
                                    <th class="px-4 py-2.5 text-center font-bold text-gray-700">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($classSummary as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2.5 font-medium text-gray-700">
                                            {{ $row->classModel?->name ?? 'Class ' . $row->class_id }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center text-sky-700">
                                            {{ number_format($row->boys) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center text-pink-600">
                                            {{ number_format($row->girls) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-bold text-gray-800">
                                            {{ number_format($row->total) }}
                                        </td>
                                    </tr>
                                @endforeach
                                {{-- Grand total row --}}
                                <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                                    <td class="px-4 py-2.5 text-gray-700">Grand Total</td>
                                    <td class="px-4 py-2.5 text-center text-sky-700">
                                        {{ number_format($classSummary->sum('boys')) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-pink-600">
                                        {{ number_format($classSummary->sum('girls')) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-green-700">
                                        {{ number_format($classSummary->sum('total')) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-400 text-center py-6">
                        No admission data recorded for this academic year.
                    </p>
                @endif

            </div>
        </div>

    </div>

    {{-- ── Footer Actions ─────────────────────────────────────────────────── --}}
    <div class="mt-6 flex gap-3">
        @role('fde_cell')
            <a href="{{ $backRoute }}"
                class="px-6 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                ← Back to List
            </a>
        @endrole
        <a href="{{ $exportUrl }}"
            class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            ⬇️ Export PDF
        </a>
    </div>

@endsection
