{{-- SAVE AS: resources/views/hoi/transfers/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Student Transfers')

@section('content')

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Student Transfers</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $institution->name }}</p>
        </div>
        <a href="{{ route('hoi.transfers.create') }}"
            class="px-5 py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-xl hover:bg-blue-800 transition">
            + Request Transfer
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif

    {{-- ── Filters ──────────────────────────────────────────────────── --}}
    @php $activeFilters = collect(request()->except(['page','_token']))->filter(fn($v) => $v !== '' && $v !== null)->count(); @endphp
    <form method="GET" action="{{ route('hoi.transfers.index') }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5 flex flex-wrap gap-3 items-end">

        <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Statuses</option>
                @foreach (['pending' => 'Pending', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
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

        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            Filter @if ($activeFilters > 0)<span class="ml-1 inline-flex items-center justify-center w-5 h-5 bg-white text-blue-900 rounded-full text-xs font-bold">{{ $activeFilters }}</span>@endif
        </button>
        <a href="{{ route('hoi.transfers.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Clear</a>
    </form>

    {{-- ── Incoming Transfers (action needed) ──────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-sm font-bold text-gray-800">📥 Incoming Transfer Requests</h3>
                <p class="text-xs text-gray-400 mt-0.5">Students being transferred TO your school — your action required</p>
            </div>
            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full font-semibold">
                {{ $incoming->whereIn('status', ['pending', 'info_requested'])->count() }} pending
            </span>
        </div>

        @if ($incoming->isEmpty())
            <div class="px-5 py-10 text-center text-gray-400 text-sm">No incoming transfer requests.</div>
        @else
            <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1 px-5"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Swipe right to see all columns</p>
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">From School</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Student</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Class</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Initiated By</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Date</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Status</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($incoming as $t)
                            <tr class="hover:bg-gray-50 transition {{ $t->isActionable() ? 'bg-yellow-50/30' : '' }}">
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap font-medium">{{ $t->fromInstitution->name }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                    {{ $t->student_name ?? '—' }}
                                    @if ($t->father_name)
                                        <div class="text-xs text-gray-400">S/O {{ $t->father_name }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">{{ $t->classModel->name }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell">
                                    {{ $t->initiatedBy->name }}<br>
                                    <span
                                        class="text-gray-400 text-xs">{{ $t->initiated_by_role === 'fde_cell' ? 'FDE Cell' : 'HOI' }}</span>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                    {{ $t->created_at->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $t->statusBadgeClass() }}">
                                        {{ $t->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    <a href="{{ route('hoi.transfers.show', $t) }}" title="{{ $t->isActionable() ? 'Review' : 'View' }}"
                                        class="inline-flex items-center gap-1 px-2 py-1.5 sm:px-3 text-xs sm:text-sm rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span class="hidden sm:inline">{{ $t->isActionable() ? 'Review' : 'View' }}</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ── Outgoing Transfers ───────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800">📤 Outgoing Transfer Requests</h3>
            <p class="text-xs text-gray-400 mt-0.5">Students being transferred FROM your school</p>
        </div>

        @if ($outgoing->isEmpty())
            <div class="px-5 py-10 text-center text-gray-400 text-sm">No outgoing transfer requests.</div>
        @else
            <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1 px-5"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Swipe right to see all columns</p>
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">To School</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Student</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Class</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Date</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Status</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($outgoing as $t)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap font-medium">{{ $t->toInstitution->name }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                    {{ $t->student_name ?? '—' }}
                                    @if ($t->father_name)
                                        <div class="text-xs text-gray-400">S/O {{ $t->father_name }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">{{ $t->classModel->name }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                    {{ $t->created_at->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $t->statusBadgeClass() }}">
                                        {{ $t->statusLabel() }}
                                    </span>
                                    @if ($t->isInfoRequested())
                                        <div class="text-xs text-blue-600 mt-0.5">ℹ️
                                            {{ Str::limit($t->info_request_note, 40) }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    <a href="{{ route('hoi.transfers.show', $t) }}" title="View"
                                        class="inline-flex items-center gap-1 px-2 py-1.5 sm:px-3 text-xs sm:text-sm rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span class="hidden sm:inline">View</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
