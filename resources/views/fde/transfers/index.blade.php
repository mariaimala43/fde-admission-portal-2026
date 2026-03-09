{{-- SAVE AS: resources/views/fde/transfers/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Student Transfers — FDE Cell')

@section('content')

    <div class="flex justify-between items-center mb-6">
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
        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-800 transition">Filter</button>
        <a href="{{ route('fde.transfers.index') }}"
            class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:text-gray-700 transition">Reset</a>
    </form>

    {{-- ── Transfers Table ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">From School</th>
                        <th class="px-4 py-3 text-left">To School</th>
                        <th class="px-3 py-3 text-center">Class</th>
                        <th class="px-4 py-3 text-left">Student</th>
                        <th class="px-3 py-3 text-center">Initiated By</th>
                        <th class="px-3 py-3 text-center">Date</th>
                        <th class="px-3 py-3 text-center">Status</th>
                        <th class="px-3 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($transfers as $t)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-400 text-xs">#{{ $t->id }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $t->fromInstitution->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $t->toInstitution->name }}</td>
                            <td class="px-3 py-3 text-center text-gray-700">{{ $t->classModel->name }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $t->student_name ?? '—' }}
                                @if ($t->father_name)
                                    <div class="text-xs text-gray-400">S/O {{ $t->father_name }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center text-xs text-gray-500">
                                {{ $t->initiatedBy->name }}<br>
                                <span
                                    class="text-gray-400">{{ $t->initiated_by_role === 'fde_cell' ? 'FDE Cell' : 'HOI' }}</span>
                            </td>
                            <td class="px-3 py-3 text-center text-gray-500 text-xs whitespace-nowrap">
                                {{ $t->created_at->format('d M Y') }}</td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full {{ $t->statusBadgeClass() }}">{{ $t->statusLabel() }}</span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('fde.transfers.show', $t) }}"
                                    class="text-xs text-blue-600 hover:underline font-medium">View →</a>
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
