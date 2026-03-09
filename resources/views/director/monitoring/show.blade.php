{{-- SAVE AS: resources/views/director/monitoring/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Monitoring Record — Director View')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Monitoring Record</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $monitoring->institution->name }} · {{ $monitoring->classModel->name }}
            </p>
        </div>
        <a href="{{ route('director.monitoring.index') }}"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">
            ← Back to Monitoring
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Details ────────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Status Card --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-5">Admission Process Status</h3>

                <div class="grid grid-cols-3 gap-4">
                    {{-- Test --}}
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Admission Test</p>
                        @php
                            $testBadge = match ($monitoring->test_status) {
                                'passed' => 'bg-green-100 text-green-700',
                                'conducted' => 'bg-blue-100 text-blue-700',
                                'scheduled' => 'bg-yellow-100 text-yellow-700',
                                'not_applicable' => 'bg-gray-100 text-gray-500',
                                default => 'bg-gray-100 text-gray-400',
                            };
                        @endphp
                        <span class="text-sm px-3 py-1.5 rounded-full font-semibold {{ $testBadge }}">
                            {{ ucfirst(str_replace('_', ' ', $monitoring->test_status)) }}
                        </span>
                    </div>

                    {{-- Merit --}}
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Merit List</p>
                        @php
                            $meritBadge =
                                $monitoring->merit_status === 'published'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-yellow-100 text-yellow-700';
                        @endphp
                        <span class="text-sm px-3 py-1.5 rounded-full font-semibold {{ $meritBadge }}">
                            {{ ucfirst($monitoring->merit_status) }}
                        </span>
                    </div>

                    {{-- Docs --}}
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Documentation</p>
                        @php
                            $docBadge = match ($monitoring->doc_status) {
                                'complete' => 'bg-green-100 text-green-700',
                                'provisional' => 'bg-orange-100 text-orange-700',
                                'affidavit' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-400',
                            };
                        @endphp
                        <span class="text-sm px-3 py-1.5 rounded-full font-semibold {{ $docBadge }}">
                            {{ ucfirst($monitoring->doc_status) }}
                        </span>
                    </div>
                </div>

                @if ($monitoring->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Notes</p>
                        <p class="text-sm text-gray-700">{{ $monitoring->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Audit Trail --}}
            @if ($monitoring->audits && $monitoring->audits->count())
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Audit Trail</h3>
                    <div class="space-y-3 text-sm">
                        @foreach ($monitoring->audits->sortByDesc('created_at') as $audit)
                            <div class="flex items-start gap-3">
                                <span class="w-2 h-2 rounded-full bg-blue-400 mt-1.5 shrink-0"></span>
                                <div>
                                    <p class="font-medium text-gray-800">
                                        {{ ucfirst(str_replace('_', ' ', $audit->field)) }}
                                        changed to <strong>{{ $audit->new_value }}</strong>
                                        @if ($audit->old_value)
                                            <span class="text-gray-400">(was: {{ $audit->old_value }})</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $audit->created_at->format('d M Y, g:i A') }}
                                        · {{ $audit->changedBy?->name }}
                                        ({{ $audit->changedBy?->getRoleNames()->first() ?? '—' }})
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Info Panel ───────────────────────────────────────────────── --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h4 class="text-sm font-bold text-gray-800 mb-4">Details</h4>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">School</dt>
                        <dd class="font-semibold text-gray-800">{{ $monitoring->institution->name }}</dd>
                        <dd class="text-xs text-gray-400">{{ $monitoring->institution->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Sector</dt>
                        <dd class="font-medium text-gray-700">{{ $monitoring->institution->sector?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Class</dt>
                        <dd class="font-semibold text-gray-800">{{ $monitoring->classModel->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Admission Date</dt>
                        <dd class="font-medium text-gray-700">
                            {{ $monitoring->dailyAdmission?->admission_date?->format('d M Y') ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Last Updated</dt>
                        <dd class="font-medium text-gray-700">{{ $monitoring->updated_at->format('d M Y, g:i A') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-700">
                <p class="font-semibold text-blue-800 mb-1">👁 Read-only view</p>
                <p>Status updates are performed by HOI (test & docs) and FDE Cell (merit & overrides).</p>
            </div>
        </div>

    </div>

@endsection
