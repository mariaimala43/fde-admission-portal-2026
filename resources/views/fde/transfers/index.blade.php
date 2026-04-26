{{-- SAVE AS: resources/views/fde/transfers/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Student Transfers — FDE Cell')

@section('content')

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Student Transfers</h2>
            <p class="text-sm text-gray-500 mt-1">All transfer requests across all schools</p>
        </div>
        <a href="{{ route('fde.transfers.create') }}"
            class="px-5 py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-xl hover:bg-blue-800 transition">
            + Initiate Transfer
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif

    {{-- ── Filters ──────────────────────────────────────────────────── --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap gap-4 items-end">
        @php
            $activeFilters = collect(request()->except(['page', '_token']))
                ->filter(fn($v) => $v !== '' && $v !== null)
                ->count();
        @endphp

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sector</label>
            <select name="sector_id" onchange="this.form.submit()"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Sectors</option>
                @foreach ($sectors as $s)
                    <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
            <select name="status"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="info_requested" {{ request('status') === 'info_requested' ? 'selected' : '' }}>Info Requested
                </option>
                <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Class</label>
            <select name="class_id"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Classes</option>
                @foreach ($classes as $cls)
                    <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>
                        {{ $cls->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From School</label>
            <select name="from_institution"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Any</option>
                @foreach ($institutions as $inst)
                    <option value="{{ $inst->id }}" {{ request('from_institution') == $inst->id ? 'selected' : '' }}>
                        {{ $inst->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To School</label>
            <select name="to_institution"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Any</option>
                @foreach ($institutions as $inst)
                    <option value="{{ $inst->id }}" {{ request('to_institution') == $inst->id ? 'selected' : '' }}>
                        {{ $inst->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Student / Father</label>
            <input type="text" name="student_name" value="{{ request('student_name') }}" placeholder="Search name…"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-800 transition">
            Filter
            @if ($activeFilters > 0)
                <span
                    class="ml-1 inline-flex items-center justify-center w-5 h-5 bg-white text-blue-900 rounded-full text-xs font-bold">{{ $activeFilters }}</span>
            @endif
        </button>
        <a href="{{ route('fde.transfers.index') }}"
            class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:text-gray-700 transition">Reset</a>
    </form>

    {{-- ── Transfers Table ──────────────────────────────────────────── --}}
    <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        Swipe right to see all columns
    </p>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">#</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">From School</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">To School</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Class</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Student</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Initiated By</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Date</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Status</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($transfers as $t)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                <span class="text-gray-400 text-xs">#{{ $t->id }}</span>
                            </td>
                            <td class="px-3 py-3 max-w-[128px] sm:max-w-none">
                                <div class="truncate font-medium text-gray-900 max-w-[120px] sm:max-w-none"
                                    title="{{ $t->fromInstitution->name }}">{{ $t->fromInstitution->name ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">{{ $t->toInstitution->name }}</td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">{{ $t->classModel->name }}</td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ $t->student_name ?? '—' }}
                                @if ($t->father_name)
                                    <div class="text-xs text-gray-400">S/O {{ $t->father_name }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell">
                                {{ $t->initiatedBy->name }}<br>
                                <span
                                    class="text-gray-400 text-xs">{{ $t->initiated_by_role === 'fde_cell' ? 'FDE Cell' : 'HOI' }}</span>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ $t->created_at->format('d M Y') }}</td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full {{ $t->statusBadgeClass() }}">{{ $t->statusLabel() }}</span>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <a href="{{ route('fde.transfers.show', $t) }}" title="View"
                                    class="inline-flex items-center gap-1 px-2 py-1.5 sm:px-3 text-xs sm:text-sm rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span class="hidden sm:inline">View</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-gray-400">No transfer requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transfers->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $transfers->links() }}</div>
        @endif
    </div>

@endsection
