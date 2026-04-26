{{-- SAVE AS: resources/views/fde/rooms/show.blade.php --}}
@extends('layouts.app')
@section('title', ($room->institution?->name ?? 'School') . ' — New Construction Rooms')

@section('content')

    {{-- ── Breadcrumb ───────────────────────────────────────────────────── --}}
    <div class="mb-5">
        <a href="{{ route('fde.rooms.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
            ← Back to All Schools
        </a>
    </div>

    {{-- ── School Header ────────────────────────────────────────────────── --}}
    <div
        class="bg-blue-900 text-white rounded-xl px-6 py-5 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="text-2xl">🏗️</span>
                <h2 class="text-xl font-bold">{{ $room->institution?->name ?? 'Unknown School' }}</h2>
            </div>
            <p class="text-blue-200 text-sm">
                {{ $room->institution?->sector?->name ?? 'Unknown Sector' }}
                &nbsp;·&nbsp; {{ $room->institution?->type }}
                &nbsp;·&nbsp; {{ ucfirst(str_replace('_', ' ', $room->institution?->gender ?? '')) }}
                @if ($academicYear)
                    &nbsp;·&nbsp; {{ $academicYear->name }}
                @endif
            </p>
        </div>
        <div class="flex gap-3">
            @if ($room->construction_status === 'completed')
                <span class="bg-green-500 text-white text-xs px-3 py-1.5 rounded-full font-semibold">
                    ✅ Completed
                </span>
            @else
                <span class="bg-yellow-400 text-yellow-900 text-xs px-3 py-1.5 rounded-full font-semibold">
                    🔨 Near Completion
                </span>
            @endif
        </div>
    </div>

    {{-- ── School-Level Summary Cards ───────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-7 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Rooms</p>
            <p class="text-2xl font-bold text-blue-900">{{ $schoolTotals->rooms }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Allocated</p>
            <p class="text-2xl font-bold text-purple-700">{{ $schoolTotals->allocated }}</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Existing</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($schoolTotals->existing) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-sky-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">New Boys</p>
            <p class="text-2xl font-bold text-sky-600">{{ number_format($schoolTotals->total_boys) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-pink-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">New Girls</p>
            <p class="text-2xl font-bold text-pink-500">{{ number_format($schoolTotals->total_girls) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-indigo-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Capacity</p>
            <p
                class="text-2xl font-bold {{ $schoolTotals->total_enroll > $schoolTotals->seats_added ? 'text-red-600' : 'text-indigo-700' }}">
                {{ number_format($schoolTotals->total_enroll) }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Available Seats</p>
            @php $avail = max(0, $schoolTotals->available); @endphp
            <p class="text-2xl font-bold {{ $avail > 0 ? 'text-green-600' : 'text-red-500' }}">
                {{ number_format($avail) }}
            </p>
        </div>
    </div>

    {{-- ── Allocation & Enrollment Breakdown Table ──────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-bold text-gray-800">Grade-wise Allocation & Enrollment Status</h3>
            <p class="text-xs text-gray-400 mt-0.5">
                Each row = one grade allocated to new construction rooms
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left">Grade / Class</th>
                        <th class="px-4 py-3 text-center">Rooms<br><span class="normal-case font-normal">Assigned</span>
                        </th>
                        <th class="px-4 py-3 text-center">Seat<br><span class="normal-case font-normal">Capacity</span></th>
                        <th class="px-4 py-3 text-center bg-blue-50 text-blue-700">Existing<br><span
                                class="normal-case font-normal">Enrollment</span></th>
                        <th class="px-4 py-3 text-center bg-sky-50 text-sky-700">New<br><span
                                class="normal-case font-normal">Boys</span></th>
                        <th class="px-4 py-3 text-center bg-pink-50 text-pink-700">New<br><span
                                class="normal-case font-normal">Girls</span></th>
                        <th class="px-4 py-3 text-center bg-orange-50 text-orange-700">Total Newly<br><span
                                class="normal-case font-normal">Admitted</span></th>
                        <th class="px-4 py-3 text-center bg-indigo-50 text-indigo-700">Total<br><span
                                class="normal-case font-normal">Enrollment</span></th>
                        <th class="px-4 py-3 text-center bg-green-50 text-green-700">Available<br><span
                                class="normal-case font-normal">Seats</span></th>
                        <th class="px-4 py-3 text-center">Fill Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($allocations as $alloc)
                        @php $e = $alloc->enroll; @endphp
                        <tr class="hover:bg-gray-50 transition">

                            {{-- Grade --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ substr($alloc->classModel?->name ?? '?', 0, 2) }}
                                    </span>
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            {{ $alloc->classModel?->name ?? 'Unknown Grade' }}
                                        </p>
                                        <p class="text-xs text-gray-400 capitalize">
                                            {{ $alloc->purpose }}
                                            @if ($alloc->hoi_note)
                                                &nbsp;·&nbsp; <em>{{ Str::limit($alloc->hoi_note, 40) }}</em>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Rooms --}}
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-bold text-blue-900">{{ $e->total_seats / 40 }}</span>
                                <span class="text-xs text-gray-400 block">{{ number_format($e->total_seats) }} seats</span>
                            </td>

                            {{-- Seat Capacity --}}
                            <td class="px-4 py-3.5 text-center font-medium text-gray-700">
                                {{ number_format($e->total_seats) }}
                            </td>

                            {{-- Existing --}}
                            <td class="px-4 py-3.5 text-center bg-blue-50">
                                <span class="font-medium text-blue-700">{{ number_format($e->existing) }}</span>
                            </td>

                            {{-- New Boys --}}
                            <td class="px-4 py-3.5 text-center bg-sky-50">
                                <span class="font-medium text-sky-700">{{ number_format($e->total_boys) }}</span>
                                @if ($e->oosc_boys > 0)
                                    <span class="block text-xs text-sky-400">+{{ $e->oosc_boys }} OOSC/P2G</span>
                                @endif
                            </td>

                            {{-- New Girls --}}
                            <td class="px-4 py-3.5 text-center bg-pink-50">
                                <span class="font-medium text-pink-600">{{ number_format($e->total_girls) }}</span>
                                @if ($e->oosc_girls > 0)
                                    <span class="block text-xs text-pink-400">+{{ $e->oosc_girls }} OOSC/P2G</span>
                                @endif
                            </td>

                            {{-- Total Newly Admitted --}}
                            <td class="px-4 py-3.5 text-center bg-orange-50">
                                <span class="font-bold text-orange-600">{{ number_format($e->newly) }}</span>
                            </td>

                            {{-- Total Capacity --}}
                            <td class="px-4 py-3.5 text-center bg-indigo-50">
                                <span class="font-bold {{ $e->is_over ? 'text-red-600' : 'text-indigo-700' }}">
                                    {{ number_format($e->total_enroll) }}
                                </span>
                                @if ($e->is_over)
                                    <span class="block text-xs text-red-400">⚠ over capacity</span>
                                @endif
                            </td>

                            {{-- Available --}}
                            <td class="px-4 py-3.5 text-center bg-green-50">
                                @php $av = max(0, $e->available); @endphp
                                <span class="font-bold {{ $av > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ number_format($av) }}
                                </span>
                                @if ($e->is_over)
                                    <span class="block text-xs text-red-400">FULL</span>
                                @endif
                            </td>

                            {{-- Fill Rate --}}
                            <td class="px-4 py-3.5 text-center w-28">
                                <div class="flex flex-col items-center gap-1">
                                    <span
                                        class="text-xs font-bold
                                    {{ $e->fill_pct >= 100 ? 'text-red-600' : ($e->fill_pct >= 80 ? 'text-orange-500' : 'text-green-600') }}">
                                        {{ $e->fill_pct }}%
                                    </span>
                                    <div class="w-20 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full
                                        {{ $e->fill_pct >= 100 ? 'bg-red-500' : ($e->fill_pct >= 80 ? 'bg-orange-400' : 'bg-green-500') }}"
                                            style="width: {{ $e->fill_pct }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400">
                                        {{ number_format($e->total_enroll) }} / {{ number_format($e->total_seats) }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10 text-center text-gray-400">
                                <p class="font-medium">No grades allocated yet</p>
                                <p class="text-xs mt-1">The HOI must allocate classes to these rooms from the school portal.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- Totals footer --}}
                @if ($allocations->count() > 0)
                    <tfoot class="bg-blue-50 border-t-2 border-blue-100 text-sm font-bold">
                        <tr>
                            <td class="px-5 py-3 text-gray-700">TOTAL</td>
                            <td class="px-4 py-3 text-center text-blue-900">{{ $schoolTotals->allocated }}</td>
                            <td class="px-4 py-3 text-center text-blue-900">{{ number_format($schoolTotals->seats_added) }}
                            </td>
                            <td class="px-4 py-3 text-center text-blue-700 bg-blue-100">
                                {{ number_format($schoolTotals->existing) }}</td>
                            <td class="px-4 py-3 text-center text-sky-700 bg-sky-100">
                                {{ number_format($schoolTotals->total_boys) }}</td>
                            <td class="px-4 py-3 text-center text-pink-600 bg-pink-100">
                                {{ number_format($schoolTotals->total_girls) }}</td>
                            <td class="px-4 py-3 text-center text-orange-600 bg-orange-100">
                                {{ number_format($schoolTotals->newly) }}</td>
                            <td
                                class="px-4 py-3 text-center bg-indigo-100 {{ $schoolTotals->total_enroll > $schoolTotals->seats_added ? 'text-red-700' : 'text-indigo-800' }}">
                                {{ number_format($schoolTotals->total_enroll) }}
                            </td>
                            <td
                                class="px-4 py-3 text-center bg-green-100 {{ max(0, $schoolTotals->available) > 0 ? 'text-green-700' : 'text-red-600' }}">
                                {{ number_format(max(0, $schoolTotals->available)) }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500">
                                @php
                                    $grandSeats = $schoolTotals->seats_added;
                                    $grandFill =
                                        $grandSeats > 0
                                            ? min(100, round(($schoolTotals->total_enroll / $grandSeats) * 100))
                                            : 0;
                                @endphp
                                {{ $grandFill }}%
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ── Enrollment Formula Note ──────────────────────────────────────── --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl px-5 py-3 text-xs text-gray-500">
        <strong class="text-gray-700">Formula:</strong>
        Total Capacity = Promoted Students + Newly Admitted (Boys + Girls, Regular + OOSC + P2G)
        &nbsp;·&nbsp;
        Available Seats = Seat Capacity − Total Capacity
        &nbsp;·&nbsp;
        Seat Capacity = Rooms × 40 seats
    </div>

@endsection
