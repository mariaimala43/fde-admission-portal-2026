{{-- SAVE AS: resources/views/fde/audit/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Audit Log — FDE Cell')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Audit Log</h2>
            <p class="text-sm text-gray-500 mt-1">All write actions across the portal — immutable record</p>
        </div>
        <a href="{{ route('fde.audit.export') . '?' . http_build_query(request()->only('from', 'to', 'role', 'institution_id')) }}"
            class="px-4 py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition flex items-center gap-2">
            📥 Export CSV
        </a>
    </div>

    {{-- ── Stats ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
            <p class="text-2xl font-bold text-blue-900">{{ number_format($stats->total_today) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Actions Today</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ number_format($stats->total_week) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">This Week</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
            <p class="text-2xl font-bold text-gray-700">{{ number_format($stats->total_all) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">All Time</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm px-4 py-4 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ number_format($stats->overrides) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">FDE Overrides</p>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────── --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">User</label>
                <select name="user_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Role</label>
                <select name="role"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Roles</option>
                    <option value="hoi" {{ request('role') === 'hoi' ? 'selected' : '' }}>HOI</option>
                    <option value="aeo" {{ request('role') === 'aeo' ? 'selected' : '' }}>AEO</option>
                    <option value="fde_cell" {{ request('role') === 'fde_cell' ? 'selected' : '' }}>FDE Cell</option>
                    <option value="director" {{ request('role') === 'director' ? 'selected' : '' }}>Director</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Field Changed</label>
                <select name="field"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Fields</option>
                    <option value="test_status" {{ request('field') === 'test_status' ? 'selected' : '' }}>Test Status
                    </option>
                    <option value="merit_status" {{ request('field') === 'merit_status' ? 'selected' : '' }}>Merit
                        Status</option>
                    <option value="doc_status" {{ request('field') === 'doc_status' ? 'selected' : '' }}>Doc Status
                    </option>
                    <option value="workflow_status" {{ request('field') === 'workflow_status' ? 'selected' : '' }}>Workflow
                        Stage</option>
                    <option value="affidavit_path" {{ request('field') === 'affidavit_path' ? 'selected' : '' }}>Affidavit
                        Upload</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Institution</label>
                <select name="institution_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Institutions</option>
                    @foreach ($institutions as $inst)
                        <option value="{{ $inst->id }}"
                            {{ request('institution_id') == $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <button type="submit" class="px-5 py-2 bg-blue-900 text-white text-sm rounded-lg hover:bg-blue-800 transition">
                Filter
            </button>

            @if (request()->hasAny(['user_id', 'role', 'field', 'institution_id', 'from', 'to']))
                <a href="{{ route('fde.audit.index') }}"
                    class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ── Log Table ───────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date & Time</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Institution</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Field</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Old → New</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $roleBadge = match ($log->role_at_time) {
                                'fde_cell' => 'bg-red-100 text-red-700',
                                'aeo' => 'bg-blue-100 text-blue-700',
                                'hoi' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y') }}<br>
                                <span class="text-gray-400">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800 text-sm">{{ $log->changedBy?->name ?? '—' }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $roleBadge }}">
                                    {{ strtoupper($log->role_at_time ?? '—') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $log->monitoring?->institution?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium">
                                {{ $log->fieldLabel() }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if ($log->old_value)
                                    <span class="text-red-500 line-through">{{ $log->old_value }}</span>
                                    <span class="mx-1 text-gray-400">→</span>
                                @endif
                                <span class="text-green-700 font-semibold">{{ $log->new_value ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('fde.audit.show', $log) }}"
                                    class="text-xs px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                                    👁 View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400">
                                No audit records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
