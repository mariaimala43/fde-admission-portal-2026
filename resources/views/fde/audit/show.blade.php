{{-- SAVE AS: resources/views/fde/audit/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Audit Record — FDE Cell')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Audit Record #{{ $auditLog->id }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $auditLog->created_at->format('l, d M Y — H:i:s') }}</p>
        </div>
        <a href="{{ route('fde.audit.index') }}"
            class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            ← Back to Audit Log
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ── Change Details ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-5">Change Details</h3>
            <dl class="space-y-4 text-sm">

                <div class="p-4 bg-gray-50 rounded-xl">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Field Changed</dt>
                    <dd class="text-base font-bold text-gray-900">{{ $auditLog->fieldLabel() }}</dd>
                    <dd class="text-xs text-gray-400 font-mono mt-0.5">{{ $auditLog->field_name }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-red-50 border border-red-100 rounded-lg">
                        <p class="text-xs text-red-400 uppercase tracking-wide mb-1">Old Value</p>
                        <p class="font-semibold text-red-700">{{ $auditLog->old_value ?? '(none)' }}</p>
                    </div>
                    <div class="p-3 bg-green-50 border border-green-100 rounded-lg">
                        <p class="text-xs text-green-400 uppercase tracking-wide mb-1">New Value</p>
                        <p class="font-semibold text-green-700">{{ $auditLog->new_value ?? '(none)' }}</p>
                    </div>
                </div>

                @if ($auditLog->reason)
                    <div class="p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                        <p class="text-xs text-yellow-600 uppercase tracking-wide mb-1">Reason / Note</p>
                        <p class="text-sm text-gray-700">{{ $auditLog->reason }}</p>
                    </div>
                @endif

            </dl>
        </div>

        {{-- ── Context ────────────────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Who --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h4 class="text-sm font-bold text-gray-800 mb-3">Who Made This Change</h4>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">User</dt>
                        <dd class="font-semibold text-gray-800">{{ $auditLog->changedBy?->name ?? '—' }}</dd>
                        <dd class="text-xs text-gray-400">{{ $auditLog->changedBy?->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Role at Time</dt>
                        @php
                            $roleBadge = match ($auditLog->role_at_time) {
                                'fde_cell' => 'bg-red-100 text-red-700',
                                'aeo' => 'bg-blue-100 text-blue-700',
                                'hoi' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <dd>
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $roleBadge }}">
                                {{ strtoupper($auditLog->role_at_time ?? '—') }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">IP Address</dt>
                        <dd class="font-mono text-xs text-gray-600">{{ $auditLog->ip_address ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- What record --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h4 class="text-sm font-bold text-gray-800 mb-3">Related Record</h4>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Institution</dt>
                        <dd class="font-semibold text-gray-800">
                            {{ $auditLog->monitoring?->institution?->name ?? '—' }}
                        </dd>
                        <dd class="text-xs text-gray-400">
                            {{ $auditLog->monitoring?->institution?->sector?->name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Class</dt>
                        <dd class="font-medium text-gray-700">
                            {{ $auditLog->monitoring?->classModel?->name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Monitoring Record</dt>
                        <dd>
                            <a href="{{ route('fde.monitoring.show', $auditLog->monitoring_id) }}"
                                class="text-blue-700 underline text-xs hover:text-blue-800">
                                View monitoring record →
                            </a>
                        </dd>
                    </div>
                </dl>
            </div>

        </div>
    </div>

@endsection
