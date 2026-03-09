{{-- SAVE AS: resources/views/fde/corrections/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Admission Correction Requests')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Correction Requests</h2>
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-4 py-3 text-left">School</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-center">Admission Date</th>
                        <th class="px-4 py-3 text-center">Old Total</th>
                        <th class="px-4 py-3 text-center">New Total</th>
                        <th class="px-4 py-3 text-center">Net Change</th>
                        <th class="px-4 py-3 text-center">Requested By</th>
                        <th class="px-4 py-3 text-center">Requested On</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($corrections as $c)
                        <tr
                            class="border-b border-gray-50 hover:bg-gray-50 transition-colors {{ $c->isPending() ? 'bg-yellow-50' : '' }}">
                            <td class="px-4 py-3 font-medium text-gray-800 max-w-[180px] truncate">
                                {{ $c->institution->name }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $c->classModel?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600 whitespace-nowrap">
                                {{ $c->admission_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-center text-orange-700 font-semibold">{{ $c->oldTotal() }}</td>
                            <td class="px-4 py-3 text-center text-blue-700 font-semibold">{{ $c->newTotal() }}</td>
                            <td class="px-4 py-3 text-center font-bold">
                                @php $diff = $c->netDiff(); @endphp
                                <span
                                    class="{{ $diff > 0 ? 'text-green-600' : ($diff < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                    {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 text-xs whitespace-nowrap">
                                {{ $c->requestedBy?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-400 text-xs whitespace-nowrap">
                                {{ $c->created_at->format('d M Y') }}<br>
                                <span class="text-gray-300">{{ $c->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $c->statusBadgeClass() }}">
                                    {{ $c->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('fde.corrections.show', $c) }}"
                                    class="px-3 py-1.5 text-xs font-semibold {{ $c->isPending() ? 'bg-blue-900 text-white hover:bg-blue-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} rounded-lg transition whitespace-nowrap">
                                    {{ $c->isPending() ? '👁 Review' : 'View' }}
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
