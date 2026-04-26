{{-- SAVE AS: resources/views/fde/corrections/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Admission Correction Requests')

@section('content')

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">Admission Correction Requests<x-info-tooltip position="bottom" text="Schools use this to request fixes for past submissions. Review, approve, or reject each request here." /></h2>
            @if ($pendingCount > 0)
                <p class="text-sm text-yellow-600 mt-1 font-semibold">⏳ {{ $pendingCount }} pending review</p>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4 mb-5">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            @php
                $activeFilters = collect(request()->except(['page', '_token']))
                    ->filter(fn($v) => $v !== '' && $v !== null)
                    ->count();
            @endphp

            {{-- Sector --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sector</label>
                <select name="sector_id" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Sectors</option>
                    @foreach ($sectors as $s)
                        <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- School search --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">School</label>
                <select name="institution_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[160px]">
                    <option value="">All Schools</option>
                    @foreach ($institutions as $inst)
                        <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Class --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Class</label>
                <select name="class_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Classes</option>
                    @foreach ($classes as $cls)
                        <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
                <select name="status"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>✅ Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>✕ Rejected</option>
                </select>
            </div>

            {{-- Date range --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                    Filter
                    @if ($activeFilters > 0)
                        <span
                            class="ml-1 inline-flex items-center justify-center w-5 h-5 bg-white text-blue-900 rounded-full text-xs font-bold">{{ $activeFilters }}</span>
                    @endif
                </button>
                <a href="{{ route('fde.corrections.index') }}"
                    class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600 border border-gray-200 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Results count --}}
    <p class="text-xs text-gray-400 mb-3">
        Showing {{ $corrections->firstItem() ?? 0 }}–{{ $corrections->lastItem() ?? 0 }}
        of {{ number_format($corrections->total()) }} requests
    </p>

    <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        Swipe right to see all columns
    </p>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">School</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Class</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Admission Date</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Old Total</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">New Total</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Net Change</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden lg:table-cell">Requested By</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Requested On</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Status</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($corrections as $c)
                        <tr
                            class="border-b border-gray-50 hover:bg-gray-50 transition-colors {{ $c->isPending() ? 'bg-yellow-50' : '' }}">
                            <td class="px-3 py-3 max-w-[128px] sm:max-w-none">
                                <div class="truncate font-medium text-gray-900 max-w-[120px] sm:max-w-none"
                                    title="{{ $c->institution->name }}">{{ $c->institution->name }}</div>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ $c->classModel?->name ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ $c->admission_date->format('d M Y') }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center text-orange-700 font-semibold hidden md:table-cell">{{ $c->oldTotal() }}</td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center text-blue-700 font-semibold hidden md:table-cell">{{ $c->newTotal() }}</td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center font-bold hidden md:table-cell">
                                @php $diff = $c->netDiff(); @endphp
                                <span
                                    class="{{ $diff > 0 ? 'text-green-600' : ($diff < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                    {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden lg:table-cell">
                                {{ $c->requestedBy?->name ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ $c->created_at->format('d M Y') }}<br>
                                <span class="text-gray-300 text-xs">{{ $c->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $c->statusBadgeClass() }}">
                                    {{ $c->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <a href="{{ route('fde.corrections.show', $c) }}"
                                    class="inline-flex items-center gap-1 px-2 py-1.5 sm:px-3 text-xs sm:text-sm rounded-md {{ $c->isPending() ? 'bg-blue-900 text-white hover:bg-blue-800' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }} transition" title="View">
                                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span class="hidden sm:inline">{{ $c->isPending() ? 'Review' : 'View' }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-gray-400 text-sm">
                                @if (request()->hasAny(['institution_id', 'class_id', 'status', 'date_from', 'date_to']))
                                    No correction requests match your filters.
                                @else
                                    No correction requests yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($corrections->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Page {{ $corrections->currentPage() }} of {{ $corrections->lastPage() }}
                </p>
                {{ $corrections->links() }}
            </div>
        @endif
    </div>

@endsection
