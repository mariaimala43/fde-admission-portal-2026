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

    {{-- ── Stats Summary ─────────────────────────────────────────────────── --}}
    @if ($stats && $stats->total > 0)
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
            @php
                $statCards = [
                    [
                        'label' => 'Total',
                        'value' => $stats->total,
                        'color' => 'bg-gray-50  text-gray-700',
                        'border' => 'border-gray-100',
                    ],
                    [
                        'label' => 'Finalized',
                        'value' => $stats->finalized,
                        'color' => 'bg-green-50 text-green-700',
                        'border' => 'border-green-100',
                    ],
                    [
                        'label' => 'Doc Pending',
                        'value' => $stats->doc_pending,
                        'color' => 'bg-yellow-50 text-yellow-700',
                        'border' => 'border-yellow-100',
                    ],
                    [
                        'label' => 'Provisional',
                        'value' => $stats->provisional,
                        'color' => 'bg-blue-50  text-blue-700',
                        'border' => 'border-blue-100',
                    ],
                    [
                        'label' => 'Test Failed',
                        'value' => $stats->test_failed,
                        'color' => 'bg-red-50   text-red-700',
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

    {{-- ── Filters ───────────────────────────────────────────────────────── --}}
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
            class="px-5 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            Filter
        </button>
        <a href="{{ route('hoi.monitoring.index') }}"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">
            Clear
        </a>
    </form>

    {{-- ── Records Table ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-center">Workflow</th>
                        <th class="px-4 py-3 text-center">Test</th>
                        <th class="px-4 py-3 text-center">Merit</th>
                        <th class="px-4 py-3 text-center">Docs</th>
                        <th class="px-4 py-3 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr
                            class="border-b border-gray-50 hover:bg-gray-50 transition-colors
                        {{ $record->isBlocked() ? 'bg-red-50' : '' }}
                        {{ $record->isFinalized() ? 'bg-green-50' : '' }}">

                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                {{ $record->admission_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $record->classModel?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->workflowBadge() }}">
                                    {{ $record->workflowLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->testStatusBadge() }}">
                                    {{ $record->testStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->meritStatusBadge() }}">
                                    {{ $record->meritStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->docStatusBadge() }}">
                                    {{ $record->docStatusLabel() }}
                                    @if ($record->doc_status === 'affidavit_case' && $record->affidavit_path)
                                        📎
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('hoi.monitoring.show', $record) }}"
                                    class="text-xs px-3 py-1.5 rounded-lg bg-blue-900 text-white hover:bg-blue-800 transition">
                                    Update →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">
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
