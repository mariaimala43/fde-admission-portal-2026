{{-- SAVE AS: resources/views/hoi/transfers/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Student Transfers')

@section('content')

    <div class="flex justify-between items-center mb-6">
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">From School</th>
                            <th class="px-4 py-3 text-left">Student</th>
                            <th class="px-3 py-3 text-center">Class</th>
                            <th class="px-3 py-3 text-center">Initiated By</th>
                            <th class="px-3 py-3 text-center">Date</th>
                            <th class="px-3 py-3 text-center">Status</th>
                            <th class="px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($incoming as $t)
                            <tr class="hover:bg-gray-50 transition {{ $t->isActionable() ? 'bg-yellow-50/30' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $t->fromInstitution->name }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $t->student_name ?? '—' }}
                                    @if ($t->father_name)
                                        <div class="text-xs text-gray-400">S/O {{ $t->father_name }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center text-gray-700">{{ $t->classModel->name }}</td>
                                <td class="px-3 py-3 text-center text-gray-500 text-xs">
                                    {{ $t->initiatedBy->name }}<br>
                                    <span
                                        class="text-gray-400">{{ $t->initiated_by_role === 'fde_cell' ? 'FDE Cell' : 'HOI' }}</span>
                                </td>
                                <td class="px-3 py-3 text-center text-gray-500 text-xs whitespace-nowrap">
                                    {{ $t->created_at->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $t->statusBadgeClass() }}">
                                        {{ $t->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('hoi.transfers.show', $t) }}"
                                        class="text-xs text-blue-600 hover:underline font-medium">
                                        {{ $t->isActionable() ? 'Review →' : 'View →' }}
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">To School</th>
                            <th class="px-4 py-3 text-left">Student</th>
                            <th class="px-3 py-3 text-center">Class</th>
                            <th class="px-3 py-3 text-center">Date</th>
                            <th class="px-3 py-3 text-center">Status</th>
                            <th class="px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($outgoing as $t)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $t->toInstitution->name }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $t->student_name ?? '—' }}
                                    @if ($t->father_name)
                                        <div class="text-xs text-gray-400">S/O {{ $t->father_name }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center text-gray-700">{{ $t->classModel->name }}</td>
                                <td class="px-3 py-3 text-center text-gray-500 text-xs whitespace-nowrap">
                                    {{ $t->created_at->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $t->statusBadgeClass() }}">
                                        {{ $t->statusLabel() }}
                                    </span>
                                    @if ($t->isInfoRequested())
                                        <div class="text-xs text-blue-600 mt-0.5">ℹ️
                                            {{ Str::limit($t->info_request_note, 40) }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('hoi.transfers.show', $t) }}"
                                        class="text-xs text-blue-600 hover:underline font-medium">View →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
