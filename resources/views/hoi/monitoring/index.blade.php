{{-- SAVE AS: resources/views/hoi/monitoring/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Admission Monitoring')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Monitoring</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $institution->name }} &middot; {{ $academicYear?->name }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 mb-5 text-sm">ℹ️
            {{ session('info') }}</div>
    @endif

    {{-- ── Stats Summary ──────────────────────────────────────────────────────────── --}}
    @if ($stats && $stats->total > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3 mb-5">
            @php
                $statCards = [
                    [
                        'label' => 'Total Batches',
                        'value' => $stats->total,
                        'color' => 'bg-gray-50 text-gray-700',
                        'border' => 'border-gray-100',
                    ],
                    [
                        'label' => 'Finalized',
                        'value' => $stats->finalized,
                        'color' => 'bg-green-50 text-green-700',
                        'border' => 'border-green-100',
                    ],
                    [
                        'label' => 'Partial',
                        'value' => $stats->partial_finalized,
                        'color' => 'bg-purple-50 text-purple-700',
                        'border' => 'border-purple-100',
                    ],
                    [
                        'label' => 'Doc Pending',
                        'value' => $stats->doc_pending,
                        'color' => 'bg-yellow-50 text-yellow-700',
                        'border' => 'border-yellow-100',
                    ],
                    [
                        'label' => 'Test Pending',
                        'value' => $stats->test_pending,
                        'color' => 'bg-blue-50 text-blue-700',
                        'border' => 'border-blue-100',
                    ],
                    [
                        'label' => '✅ Passed',
                        'value' => $stats->total_passed_students,
                        'color' => 'bg-green-50 text-green-800',
                        'border' => 'border-green-200',
                    ],
                    [
                        'label' => '❌ Failed',
                        'value' => $stats->total_failed_students,
                        'color' => 'bg-red-50 text-red-700',
                        'border' => 'border-red-100',
                    ],
                ];
            @endphp
            @foreach ($statCards as $card)
                <div class="bg-white rounded-xl shadow-sm border {{ $card['border'] }} px-4 py-3 text-center">
                    <p class="text-2xl font-bold {{ $card['color'] }} rounded-lg px-2 py-0.5 inline-block">
                        {{ $card['value'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $card['label'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Filters ────────────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('hoi.monitoring.index') }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5 flex flex-wrap gap-3 items-end">

        <div>
            <label class="block text-xs text-gray-500 mb-1">Workflow Stage</label>
            <select name="workflow"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Stages</option>
                @foreach ([
            'draft' => 'Draft',
            'test_verification' => 'Test Verification',
            'merit_confirmation' => 'Merit Confirmation',
            'doc_verification' => 'Doc Review',
            'partial_finalized' => 'Partial — Retest Pending',
            'finalized' => 'Finalized',
        ] as $val => $lbl)
                    <option value="{{ $val }}" {{ $workflow === $val ? 'selected' : '' }}>{{ $lbl }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Doc Status</label>
            <select name="doc_status"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All</option>
                @foreach ([
            'pending' => 'Pending',
            'provisional' => 'Provisional',
            'affidavit_case' => 'Affidavit Case',
            'complete' => 'Complete',
        ] as $val => $lbl)
                    <option value="{{ $val }}" {{ $docStatus === $val ? 'selected' : '' }}>{{ $lbl }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition">Filter</button>
        <a href="{{ route('hoi.monitoring.index') }}"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">Clear</a>
    </form>

    {{-- ── Records Table ──────────────────────────────────────────────────────────── --}}
    <p class="block sm:hidden text-xs text-gray-400 mb-2">← Swipe right to see all columns</p>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr
                        class="border-b-2 border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-3 py-3 text-left hidden sm:table-cell">Date</th>
                        <th class="px-3 py-3 text-left">Class</th>
                        <th class="px-3 py-3 text-left hidden sm:table-cell">Total</th>
                        <th class="px-3 py-3 text-left">Outcome</th>
                        <th class="px-3 py-3 text-left">Workflow</th>
                        <th class="px-3 py-3 text-left hidden md:table-cell">Merit</th>
                        <th class="px-3 py-3 text-left hidden sm:table-cell">Docs</th>
                        <th class="px-3 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)

                        @php
                            $hasSplits = $record->splits->isNotEmpty();
                            $passedSplit = $record->splits->firstWhere('split_type', 'passed');
                            $failedSplit = $record->splits->firstWhere('split_type', 'failed');
                            $exemptedSplit = $record->splits->firstWhere('split_type', 'exempted');
                            $rowSpan = $hasSplits ? $record->splits->count() : 1;

                            // Parent row bg
                            $rowBg = $record->isFinalized()
                                ? 'bg-green-50/40'
                                : ($record->isBlocked()
                                    ? 'bg-red-50/40'
                                    : ($record->isPartiallyFinalized()
                                        ? 'bg-purple-50/30'
                                        : ''));
                        @endphp

                        @if (!$hasSplits)
                            {{-- ── No splits yet: single plain row ─────────────── --}}
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors {{ $rowBg }}">
                                <td class="px-3 py-3 text-gray-500 whitespace-nowrap hidden sm:table-cell text-xs">
                                    {{ $record->admission_date->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 font-semibold text-gray-900 whitespace-nowrap">
                                    {{ $record->classModel?->name ?? '—' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap hidden sm:table-cell">
                                    @if ($record->total_admitted > 0)
                                        <span class="text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">
                                            {{ $record->total_admitted }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span class="text-xs text-yellow-600 bg-yellow-50 px-2 py-1 rounded-full font-medium">
                                        ⏳ Counts pending
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->workflowBadge() }}">
                                        {{ $record->workflowLabel() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap hidden md:table-cell">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->meritStatusBadge() }}">
                                        {{ $record->meritStatusLabel() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap hidden sm:table-cell">
                                    <span class="text-xs text-gray-400">—</span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <a href="{{ route('hoi.monitoring.show', $record) }}"
                                        class="inline-flex items-center gap-1 px-2 py-1.5 sm:px-3 text-xs rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition font-medium">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span class="hidden sm:inline">Enter Counts</span>
                                    </a>
                                </td>
                            </tr>
                        @else
                            {{-- ── Has splits: render each split as a visual sub-row ──── --}}
                            @foreach ($record->splits as $splitIndex => $split)
                                <tr
                                    class="border-b border-gray-50 hover:bg-gray-50/70 transition-colors
                                {{ $split->split_type === 'passed' ? 'bg-green-50/30' : '' }}
                                {{ $split->split_type === 'failed' ? 'bg-red-50/30' : '' }}
                                {{ $split->split_type === 'exempted' ? 'bg-blue-50/20' : '' }}">

                                    {{-- Date + class only on first split row --}}
                                    @if ($splitIndex === 0)
                                        <td class="px-3 py-2.5 text-gray-500 whitespace-nowrap hidden sm:table-cell text-xs"
                                            rowspan="{{ $rowSpan }}">
                                            {{ $record->admission_date->format('d M Y') }}
                                        </td>
                                        <td class="px-3 py-2.5 font-semibold text-gray-900 whitespace-nowrap"
                                            rowspan="{{ $rowSpan }}">
                                            <div>{{ $record->classModel?->name ?? '—' }}</div>
                                            @if ($record->isPartiallyFinalized())
                                                <span class="text-xs text-purple-600 font-normal">Partial</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 whitespace-nowrap hidden sm:table-cell"
                                            rowspan="{{ $rowSpan }}">
                                            <span
                                                class="text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">
                                                {{ $record->total_admitted }}
                                            </span>
                                        </td>
                                    @endif

                                    {{-- Per-split: outcome badge + count --}}
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $split->splitTypeBadge() }}">
                                                {{ $split->splitTypeIcon() }} {{ $split->splitTypeLabel() }}
                                            </span>
                                            <span class="text-xs font-bold text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded">
                                                {{ $split->student_count }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Per-split: workflow --}}
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $split->workflowBadge() }}">
                                            {{ $split->workflowLabel() }}
                                        </span>
                                    </td>

                                    {{-- Merit (only on first row, spans all) --}}
                                    @if ($splitIndex === 0)
                                        <td class="px-3 py-2.5 whitespace-nowrap hidden md:table-cell"
                                            rowspan="{{ $rowSpan }}">
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $record->meritStatusBadge() }}">
                                                {{ $record->meritStatusLabel() }}
                                            </span>
                                        </td>
                                    @endif

                                    {{-- Per-split: doc status --}}
                                    <td class="px-3 py-2.5 whitespace-nowrap hidden sm:table-cell">
                                        @if ($split->doc_status)
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $split->docStatusBadge() }}">
                                                {{ $split->docStatusLabel() }}
                                                @if ($split->doc_status === 'affidavit_case' && $split->affidavit_path)
                                                    📎
                                                @endif
                                            </span>
                                        @elseif ($split->split_type === 'passed')
                                            <span class="text-xs text-gray-400">N/A</span>
                                        @elseif ($split->split_type === 'failed')
                                            <span class="text-xs text-gray-400">Re-test</span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>

                                    {{-- Action (only on first row, spans all) --}}
                                    @if ($splitIndex === 0)
                                        <td class="px-3 py-2.5 whitespace-nowrap" rowspan="{{ $rowSpan }}">
                                            <a href="{{ route('hoi.monitoring.show', $record) }}"
                                                class="inline-flex items-center gap-1 px-2 py-1.5 sm:px-3 text-xs rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition font-medium">
                                                <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span class="hidden sm:inline">View</span>
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif

                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400 text-sm">
                                @if ($workflow || $docStatus)
                                    No records match your filters.
                                @else
                                    No monitoring records yet. They appear automatically after daily admissions are
                                    verified.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ $records->total() }}
                </p>
                {{ $records->links() }}
            </div>
        @endif
    </div>

@endsection
