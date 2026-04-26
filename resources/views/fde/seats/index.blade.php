{{-- SAVE AS: resources/views/fde/seats/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Seat Configuration — FDE Cell')

@section('content')

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Seat Configuration</h2>
            <p class="text-sm text-gray-500 mt-1">
                Set and lock authorized seat capacity per school per class
                @if ($academicYear)
                    · <span class="font-medium text-blue-700">{{ $academicYear->name }}</span>
                @endif
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">❌ {{ session('error') }}
        </div>
    @endif

    {{-- ── Stats ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ number_format($totalSeats) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Authorized Seats</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $lockedCount }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Schools Locked</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $unlockedCount }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Schools Unlocked</p>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
            {{-- School name search --}}
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Search School</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Type school name or code…"
                        class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                </div>
            </div>

            {{-- Lock status --}}
            <div class="sm:w-48">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Lock Status</label>
                <select name="locked"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">All Schools</option>
                    <option value="no"  {{ request('locked') === 'no'  ? 'selected' : '' }}>🟡 Unlocked</option>
                    <option value="yes" {{ request('locked') === 'yes' ? 'selected' : '' }}>🔒 Locked</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2">
                <button type="submit"
                    class="px-5 py-2.5 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-800 transition">
                    🔍 Search
                </button>
                @if (request()->hasAny(['search', 'locked']))
                    <a href="{{ route('fde.seats.index') }}"
                        class="px-4 py-2.5 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        ✕ Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase max-w-[160px] min-w-[120px]">School</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Classes</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Total Seats</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Lock Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($institutions as $inst)
                        @php
                            $isLocked = $inst->seats_locked_at !== null;
                            $totalSeatsI = $inst->classes->sum('total_seats');
                        @endphp
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">

                            <td class="px-4 py-3 max-w-[160px]">
                                <p class="font-medium text-gray-800 truncate max-w-[160px]" title="{{ $inst->name }}">{{ $inst->name }}</p>
                                <p class="text-xs text-gray-400">{{ $inst->code }} · {{ ucfirst($inst->type) }}</p>
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($inst->configured_classes_count > 0)
                                    <span class="text-gray-700 font-medium">{{ $inst->configured_classes_count }}</span>
                                @else
                                    <span class="text-xs text-red-400 italic">Not configured</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($totalSeatsI > 0)
                                    <span class="font-semibold text-blue-700">{{ number_format($totalSeatsI) }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($isLocked)
                                    <span class="inline-flex flex-col items-center">
                                        <span
                                            class="text-xs px-2.5 py-1 rounded-full font-semibold bg-green-100 text-green-700">
                                            🔒 Locked
                                        </span>
                                        <span class="text-xs text-gray-400 mt-0.5">
                                            {{ $inst->seats_locked_at->format('d M Y') }}
                                        </span>
                                    </span>
                                @else
                                    <span
                                        class="text-xs px-2.5 py-1 rounded-full font-semibold bg-yellow-100 text-yellow-700">
                                        🟡 Unlocked
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('fde.seats.edit', $inst) }}"
                                    class="text-xs px-3 py-1.5 rounded-lg {{ $isLocked ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }} transition font-medium">
                                    {{ $isLocked ? '👁 View' : '✏️ Configure' }}
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">
                                No schools found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($institutions->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $institutions->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
