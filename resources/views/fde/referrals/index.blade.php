{{-- SAVE AS: resources/views/fde/referrals/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Referrals — FDE Cell')

@section('content')

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">Admission Referrals<x-info-tooltip position="bottom" text="Refer specific students to schools for admission. Track each referral's outcome here." /></h2>
            <p class="text-sm text-gray-500 mt-1">Manage and track all student referrals sent to schools</p>
        </div>
        <a href="{{ route('fde.referrals.create') }}"
            class="px-5 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition shadow-sm">
            + New Referral
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- ── Stats Cards ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach ([['label' => 'Total', 'value' => $stats->total, 'color' => 'gray'], ['label' => 'Pending', 'value' => $stats->pending, 'color' => 'yellow'], ['label' => 'Accepted', 'value' => $stats->accepted, 'color' => 'green'], ['label' => 'Rejected', 'value' => $stats->rejected, 'color' => 'red'], ['label' => 'Closed', 'value' => $stats->closed, 'color' => 'gray']] as $card)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
                <p class="text-2xl font-bold text-{{ $card['color'] }}-600">{{ $card['value'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('fde.referrals.index') }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5 flex flex-wrap gap-3 items-end">
        @php $activeFilters = collect(request()->except(['page','_token']))->filter(fn($v) => $v !== '' && $v !== null)->count(); @endphp

        <div>
            <label class="block text-xs text-gray-500 mb-1">Sector</label>
            <select name="sector_id" onchange="this.form.submit()"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Sectors</option>
                @foreach ($sectors as $s)
                    <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Statuses</option>
                @foreach (['pending', 'accepted', 'rejected', 're_referred', 'closed'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">School</label>
            <select name="institution_id"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Schools</option>
                @foreach ($institutions as $inst)
                    <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>
                        {{ $inst->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Class</label>
            <select name="class_id"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Classes</option>
                @foreach ($classes as $cls)
                    <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Gender</label>
            <select name="gender"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All</option>
                <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Shift</label>
            <select name="shift"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Shifts</option>
                <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>Evening</option>
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, father name, ref no…"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-48 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            Filter @if ($activeFilters > 0)<span class="ml-1 inline-flex items-center justify-center w-5 h-5 bg-white text-blue-900 rounded-full text-xs font-bold">{{ $activeFilters }}</span>@endif
        </button>
        <a href="{{ route('fde.referrals.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Clear</a>
    </form>

    {{-- ── Table ────────────────────────────────────────────────────────── --}}
    <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        Swipe right to see all columns
    </p>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-100 bg-gray-50">
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Ref No</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Student</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">School</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Class</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Date</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Status</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Tracking</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $ref)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                <span class="font-mono text-xs font-semibold text-blue-700">{{ $ref->reference_no }}</span>
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                <p class="font-medium text-gray-800">{{ $ref->student_name ?? '—' }}</p>
                                @if ($ref->father_name)
                                    <p class="text-xs text-gray-400">S/O {{ $ref->father_name }}</p>
                                @endif
                            </td>

                            <td class="px-3 py-3 max-w-[128px] sm:max-w-none">
                                <div class="truncate font-medium text-gray-900 max-w-[120px] sm:max-w-none"
                                    title="{{ $ref->institution->name }}">{{ $ref->institution->name ?? '—' }}</div>
                                <p class="text-xs text-gray-400 hidden sm:block">{{ $ref->institution->sector?->name }}</p>
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ $ref->classModel?->name ?? '—' }}
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ $ref->created_at->format('d M Y') }}
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <span
                                    class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $ref->statusBadgeClass() }}">
                                    {{ $ref->statusLabel() }}
                                </span>
                                @if ($ref->isRejected())
                                    <p class="text-xs text-red-500 mt-1 max-w-32 mx-auto truncate"
                                        title="{{ $ref->rejection_reason }}">
                                        {{ $ref->rejection_reason }}
                                    </p>
                                @endif
                            </td>

                            {{-- Tracking status (only meaningful after acceptance) --}}
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell">
                                @if ($ref->isAccepted())
                                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $ref->trackingBadgeClass() }}">
                                        {{ $ref->trackingStatusLabel() }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    {{-- View --}}
                                    <a href="{{ route('fde.referrals.show', $ref) }}" title="View"
                                        class="inline-flex items-center gap-1 px-2 py-1.5 sm:px-3 text-xs sm:text-sm rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span class="hidden sm:inline">View</span>
                                    </a>

                                    {{-- Edit (pending only) --}}
                                    @if ($ref->isPending())
                                        <a href="{{ route('fde.referrals.edit', $ref) }}"
                                            class="text-xs px-2 py-1.5 sm:px-3 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition hidden sm:inline-flex">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('fde.referrals.cancel', $ref) }}"
                                            onsubmit="return confirm('Cancel this referral?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="text-xs px-2 py-1.5 sm:px-3 rounded-md bg-red-50 text-red-600 hover:bg-red-100 transition hidden sm:inline-flex">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Re-refer (rejected only) --}}
                                    @if ($ref->isRejected())
                                        <a href="{{ route('fde.referrals.re-refer', $ref) }}"
                                            class="text-xs px-2 py-1.5 sm:px-3 rounded-md bg-orange-50 text-orange-700 hover:bg-orange-100 transition hidden sm:inline-flex">
                                            Re-refer
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400 text-sm">
                                No referrals found.
                                <a href="{{ route('fde.referrals.create') }}" class="text-blue-600 hover:underline ml-1">
                                    Create the first one →
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($referrals->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $referrals->links() }}
            </div>
        @endif
    </div>

@endsection
