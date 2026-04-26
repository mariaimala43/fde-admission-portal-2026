{{-- SAVE AS: resources/views/hoi/corrections/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Correction Requests')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">Correction Requests<x-info-tooltip position="bottom" text="Submit a formal request to fix a mistake in your daily admission entry. FDE will review and approve or reject it." /></h2>
            <p class="text-sm text-gray-500 mt-1">{{ $institution->name }} — request corrections for past verified
                submissions</p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl px-4 py-3 mb-5 text-sm">⚠️
            {{ session('warning') }}</div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4 mb-5">
        @php $activeFilters = collect(request()->except(['page','_token']))->filter(fn($v) => $v !== '' && $v !== null)->count(); @endphp
        <form method="GET" class="flex flex-wrap gap-3 items-end">
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
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Correction Status</label>
                <select name="correction_status"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="none" {{ request('correction_status') === 'none' ? 'selected' : '' }}>No Request
                        Yet</option>
                    <option value="pending" {{ request('correction_status') === 'pending' ? 'selected' : '' }}>Pending
                    </option>
                    <option value="approved" {{ request('correction_status') === 'approved' ? 'selected' : '' }}>Approved
                    </option>
                    <option value="rejected" {{ request('correction_status') === 'rejected' ? 'selected' : '' }}>Rejected
                    </option>
                </select>
            </div>
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
                    Filter @if ($activeFilters > 0)<span class="ml-1 inline-flex items-center justify-center w-5 h-5 bg-white text-blue-900 rounded-full text-xs font-bold">{{ $activeFilters }}</span>@endif
                </button>
                <a href="{{ route('hoi.corrections.index') }}"
                    class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600 border border-gray-200 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if ($submissions->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            @if (request()->hasAny(['class_id', 'correction_status', 'date_from', 'date_to']))
                <p class="text-gray-500 font-semibold mb-1">No records match your filters</p>
                <p class="text-gray-400 text-sm">Try adjusting or resetting the filters above.</p>
            @else
                <p class="text-gray-500 font-semibold mb-1">No verified submissions found</p>
                <p class="text-gray-400 text-sm">Submit and verify daily admissions first before requesting corrections.</p>
                <a href="{{ route('hoi.admissions.daily') }}"
                    class="inline-block mt-4 px-5 py-2.5 bg-blue-900 text-white text-sm font-bold rounded-xl hover:bg-blue-800 transition">
                    Go to Daily Admissions →
                </a>
            @endif
        </div>
    @else
        <div class="bg-blue-50 border border-blue-100 rounded-xl px-5 py-3 mb-4 text-sm text-blue-700">
            ℹ️ Click <strong>Request Correction</strong> on any row to submit corrected numbers to FDE Cell for approval.
            Corrections are applied only after FDE approves them.
        </div>

        {{-- Results count --}}
        <p class="text-xs text-gray-400 mb-3">
            Showing {{ $submissions->firstItem() }}–{{ $submissions->lastItem() }} of
            {{ number_format($submissions->total()) }} records
        </p>

        <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1 px-5 pt-3"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Swipe right to see all columns</p>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Date</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Class</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Morning<br><span
                                    class="font-normal normal-case text-gray-400">Boys / Girls</span></th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">OOSC</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">P2G</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Total</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Correction Status</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $entry)
                            @php
                                $key = $entry->admission_date->toDateString() . '_' . $entry->class_id;
                                $correction = $corrections[$key] ?? null;
                                $total =
                                    $entry->morning_boys +
                                    $entry->morning_girls +
                                    $entry->evening_boys +
                                    $entry->evening_girls +
                                    $entry->morning_oosc_boys +
                                    $entry->morning_oosc_girls +
                                    $entry->morning_p2p_boys +
                                    $entry->morning_p2p_girls +
                                    $entry->evening_oosc_boys +
                                    $entry->evening_oosc_girls +
                                    $entry->evening_p2p_boys +
                                    $entry->evening_p2p_girls;
                            @endphp
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap font-medium text-gray-700">
                                    {{ $entry->admission_date->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap font-semibold">
                                    {{ $entry->classModel?->name ?? "Class {$entry->class_id}" }}
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center hidden sm:table-cell text-gray-600">
                                    {{ $entry->morning_boys }} / {{ $entry->morning_girls }}
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center hidden md:table-cell text-purple-700">
                                    {{ $entry->morning_oosc_boys + $entry->morning_oosc_girls + $entry->evening_oosc_boys + $entry->evening_oosc_girls }}
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center hidden md:table-cell text-orange-700">
                                    {{ $entry->morning_p2p_boys + $entry->morning_p2p_girls + $entry->evening_p2p_boys + $entry->evening_p2p_girls }}
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center hidden sm:table-cell font-bold text-blue-900">{{ $total }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    @if ($correction)
                                        <span
                                            class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $correction->statusBadgeClass() }}">
                                            {{ $correction->statusLabel() }}
                                        </span>
                                        @if ($correction->isApproved())
                                            <p class="text-xs text-green-600 mt-1">Applied ✓</p>
                                        @elseif ($correction->isRejected() && $correction->fde_note)
                                            <p class="text-xs text-red-500 mt-1 max-w-xs">{{ $correction->fde_note }}</p>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    @if (!$correction || $correction->isRejected())
                                        <a href="{{ route('hoi.corrections.create', ['date' => $entry->admission_date->toDateString(), 'class_id' => $entry->class_id]) }}"
                                            class="px-3 py-1.5 text-xs font-semibold bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition whitespace-nowrap">
                                            ✏️ Request Correction
                                        </a>
                                    @elseif ($correction->isPending())
                                        <span class="text-xs text-yellow-600 font-medium">⏳ Awaiting FDE</span>
                                    @elseif ($correction->isApproved())
                                        <span class="text-xs text-green-600 font-medium">✅ Applied</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($submissions->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-400">
                        Page {{ $submissions->currentPage() }} of {{ $submissions->lastPage() }}
                    </p>
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    @endif

@endsection
