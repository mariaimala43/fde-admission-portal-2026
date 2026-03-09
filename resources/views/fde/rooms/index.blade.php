{{-- SAVE AS: resources/views/fde/rooms/index.blade.php --}}
@extends('layouts.app')
@section('title', 'New Construction Rooms — FDE Cell')

@section('content')

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">🏗️ New Construction Classrooms</h2>
            <p class="text-sm text-gray-500 mt-1">
                Enrollment & allocation status of newly constructed rooms across all ICT schools
                @if ($academicYear)
                    &nbsp;·&nbsp; {{ $academicYear->name }}
                @endif
            </p>
        </div>
        <a href="{{ route('fde.rooms.export') ?? '#' }}"
            class="inline-flex items-center gap-2 text-sm bg-blue-900 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export CSV
        </a>
    </div>

    {{-- ── Summary Cards ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Schools</p>
            <p class="text-2xl font-bold text-blue-900">{{ $stats->total_schools }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Rooms</p>
            <p class="text-2xl font-bold text-blue-900">{{ number_format($stats->total_rooms) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Completed</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats->completed }}</p>
        </div>
        <div class="bg-white rounded-xl border border-yellow-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Near Completion</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats->near_completion }}</p>
        </div>
        <div class="bg-white rounded-xl border border-purple-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Rooms Allocated</p>
            <p class="text-2xl font-bold text-purple-700">{{ number_format($stats->allocated_rooms) }}</p>
        </div>
        <div class="bg-blue-900 rounded-xl shadow-sm p-4 text-center">
            <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Capacity Added</p>
            <p class="text-2xl font-bold text-white">{{ number_format($stats->total_seats) }}</p>
            <p class="text-xs text-blue-300 mt-0.5">seats (×40/room)</p>
        </div>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('fde.rooms.index') }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5 flex flex-wrap gap-3 items-end">

        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs text-gray-500 mb-1">Cluster / Sector</label>
            <select name="sector_id"
                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                <option value="">All Clusters</option>
                @foreach ($sectors as $sector)
                    <option value="{{ $sector->id }}" {{ request('sector_id') == $sector->id ? 'selected' : '' }}>
                        {{ $sector->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs text-gray-500 mb-1">Construction Status</label>
            <select name="construction_status"
                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="completed" {{ request('construction_status') === 'completed' ? 'selected' : '' }}>✅
                    Completed</option>
                <option value="near_completion"
                    {{ request('construction_status') === 'near_completion' ? 'selected' : '' }}>🔨 Near Completion
                </option>
            </select>
        </div>

        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs text-gray-500 mb-1">Allocation Status</label>
            <select name="allocation_status"
                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                <option value="">All</option>
                <option value="allocated" {{ request('allocation_status') === 'allocated' ? 'selected' : '' }}>Rooms
                    Allocated</option>
                <option value="unallocated" {{ request('allocation_status') === 'unallocated' ? 'selected' : '' }}>Rooms
                    Unallocated</option>
                <option value="full" {{ request('allocation_status') === 'full' ? 'selected' : '' }}>Fully
                    Allocated</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-900 text-white text-sm px-5 py-2 rounded-lg hover:bg-blue-800 transition">
            Filter
        </button>
        @if (request()->hasAny(['sector_id', 'construction_status', 'allocation_status']))
            <a href="{{ route('fde.rooms.index') }}" class="text-sm text-gray-400 hover:text-gray-600 py-2">Clear</a>
        @endif
    </form>

    {{-- ── Main Table ────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">School</th>
                        <th class="px-4 py-3 text-center">Cluster</th>
                        <th class="px-4 py-3 text-center">Construction</th>
                        <th class="px-4 py-3 text-center">Rooms</th>
                        <th class="px-4 py-3 text-center border-l border-gray-100 bg-purple-50 text-purple-700">Grade
                            Allocated</th>
                        <th class="px-4 py-3 text-center bg-blue-50 text-blue-700">Existing<br><span
                                class="normal-case font-normal text-gray-400">Enrollment</span></th>
                        <th class="px-4 py-3 text-center bg-sky-50 text-sky-700">New Boys</th>
                        <th class="px-4 py-3 text-center bg-pink-50 text-pink-700">New Girls</th>
                        <th class="px-4 py-3 text-center bg-indigo-50 text-indigo-700">Total<br><span
                                class="normal-case font-normal text-gray-400">Enrollment</span></th>
                        <th class="px-4 py-3 text-center bg-green-50 text-green-700">Available<br><span
                                class="normal-case font-normal text-gray-400">Seats</span></th>
                        <th class="px-4 py-3 text-center">Fill %</th>
                        <th class="px-4 py-3 text-center">Details</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50">
                    @forelse ($records as $room)
                        @php
                            // Aggregate across all allocations for this school
                            $statsRow = $room->enrollment_stats;
                            $totalExisting = $statsRow->sum('existing');
                            $totalBoys = $statsRow->sum('boys');
                            $totalGirls = $statsRow->sum('girls');
                            $totalNewly = $statsRow->sum('newly');
                            $totalEnroll = $statsRow->sum('total_enroll');
                            $totalSeats = $statsRow->sum('seats') ?: $room->rooms_total * 40;
                            $totalAvail = max(0, $totalSeats - $totalEnroll);
                            $fillPct = $totalSeats > 0 ? min(100, round(($totalEnroll / $totalSeats) * 100)) : 0;
                            $isOver = $totalEnroll > $totalSeats;
                            $grades = $room->allocations->map(fn($a) => $a->classModel?->name)->filter()->implode(', ');
                        @endphp

                        <tr class="hover:bg-gray-50 transition">
                            {{-- # --}}
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $loop->iteration }}</td>

                            {{-- School --}}
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-800 text-xs leading-tight">
                                    {{ $room->institution?->name ?? '—' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $room->institution?->type }} &middot;
                                    {{ ucfirst(str_replace('_', ' ', $room->institution?->gender ?? '')) }}
                                </p>
                            </td>

                            {{-- Cluster --}}
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs text-gray-600 font-medium">
                                    {{ $room->institution?->sector?->name ?? ($room->notes ?? '—') }}
                                </span>
                            </td>

                            {{-- Construction Status --}}
                            <td class="px-4 py-3 text-center">
                                @if ($room->construction_status === 'completed')
                                    <span
                                        class="inline-flex items-center gap-1 text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-medium">
                                        ✅ Completed
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full font-medium">
                                        🔨 Near Completion
                                    </span>
                                @endif
                            </td>

                            {{-- Rooms --}}
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold text-blue-900">{{ $room->rooms_total }}</span>
                                @if ($room->rooms_allocated > 0)
                                    <span class="text-gray-400 text-xs">/{{ $room->rooms_allocated }} used</span>
                                @endif
                            </td>

                            {{-- Grade Allocated --}}
                            <td class="px-4 py-3 text-center bg-purple-50">
                                @if ($grades)
                                    <span class="text-xs font-semibold text-purple-700">{{ $grades }}</span>
                                @else
                                    <span class="text-xs text-gray-300 italic">Not allocated</span>
                                @endif
                            </td>

                            {{-- Existing Enrollment --}}
                            <td class="px-4 py-3 text-center bg-blue-50">
                                <span class="font-medium text-blue-700">
                                    {{ $room->allocations->count() > 0 ? number_format($totalExisting) : '—' }}
                                </span>
                            </td>

                            {{-- New Boys --}}
                            <td class="px-4 py-3 text-center bg-sky-50">
                                <span class="font-medium text-sky-700">
                                    {{ $room->allocations->count() > 0 ? number_format($totalBoys) : '—' }}
                                </span>
                            </td>

                            {{-- New Girls --}}
                            <td class="px-4 py-3 text-center bg-pink-50">
                                <span class="font-medium text-pink-600">
                                    {{ $room->allocations->count() > 0 ? number_format($totalGirls) : '—' }}
                                </span>
                            </td>

                            {{-- Total Enrollment --}}
                            <td class="px-4 py-3 text-center bg-indigo-50">
                                @if ($room->allocations->count() > 0)
                                    <span class="font-bold {{ $isOver ? 'text-red-600' : 'text-indigo-700' }}">
                                        {{ number_format($totalEnroll) }}
                                    </span>
                                    @if ($isOver)
                                        <span class="block text-xs text-red-400">over capacity</span>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Available Seats --}}
                            <td class="px-4 py-3 text-center bg-green-50">
                                @if ($room->allocations->count() > 0)
                                    <span class="font-bold {{ $totalAvail > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($totalAvail) }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Fill % with mini bar --}}
                            <td class="px-4 py-3 text-center w-24">
                                @if ($room->allocations->count() > 0)
                                    <div class="flex flex-col items-center gap-1">
                                        <span
                                            class="text-xs font-semibold {{ $fillPct >= 100 ? 'text-red-600' : ($fillPct >= 80 ? 'text-orange-500' : 'text-green-600') }}">
                                            {{ $fillPct }}%
                                        </span>
                                        <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all
                                            {{ $fillPct >= 100 ? 'bg-red-500' : ($fillPct >= 80 ? 'bg-orange-400' : 'bg-green-500') }}"
                                                style="width: {{ $fillPct }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Details link --}}
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('fde.rooms.show', $room) }}"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                    View →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-6 py-12 text-center text-gray-400">
                                No new construction rooms found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($records->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $records->links() }}
            </div>
        @endif
    </div>

@endsection
