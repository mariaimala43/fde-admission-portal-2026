{{-- SAVE AS: resources/views/aeo/monitoring/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Admission Monitoring — ' . $sector->name)

@section('content')

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Process Monitoring</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $sector->name }}
                @if ($academicYear)
                    · <span class="font-medium text-blue-700">{{ $academicYear->name }}</span>
                @endif
                · Read-only view
            </p>
        </div>
    </div>

    {{-- ── Stats ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
            <p class="text-2xl font-bold text-blue-900">{{ number_format($stats->total) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Records</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats->test_passed) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Test Passed</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats->merit_published) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Merit Published</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
            <p class="text-2xl font-bold text-teal-600">{{ number_format($stats->docs_complete) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Docs Complete</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm px-4 py-4 text-center">
            <p class="text-2xl font-bold text-orange-500">{{ number_format($stats->docs_provisional) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Provisional / Affidavit</p>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────── --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <select name="institution_id"
            class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">All Schools in Sector</option>
            @foreach ($institutions as $inst)
                <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>
                    {{ $inst->name }}
                </option>
            @endforeach
        </select>

        <select name="test_status"
            class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">All Test Status</option>
            <option value="not_required" {{ request('test_status') === 'not_required' ? 'selected' : '' }}>Not Required
            </option>
            <option value="pending" {{ request('test_status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="scheduled" {{ request('test_status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            <option value="conducted" {{ request('test_status') === 'conducted' ? 'selected' : '' }}>Conducted</option>
            <option value="passed" {{ request('test_status') === 'passed' ? 'selected' : '' }}>Passed</option>
            <option value="failed" {{ request('test_status') === 'failed' ? 'selected' : '' }}>Failed</option>
        </select>

        <select name="merit_status"
            class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">All Merit Status</option>
            <option value="pending" {{ request('merit_status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="published" {{ request('merit_status') === 'published' ? 'selected' : '' }}>Published</option>
        </select>

        <select name="doc_status"
            class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">All Doc Status</option>
            <option value="pending" {{ request('doc_status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="provisional" {{ request('doc_status') === 'provisional' ? 'selected' : '' }}>Provisional
            </option>
            <option value="affidavit_case"{{ request('doc_status') === 'affidavit_case' ? 'selected' : '' }}>Affidavit Case
            </option>
            <option value="complete" {{ request('doc_status') === 'complete' ? 'selected' : '' }}>Complete</option>
        </select>

        <button type="submit" class="px-5 py-2.5 bg-blue-900 text-white text-sm rounded-lg hover:bg-blue-800 transition">
            Filter
        </button>
        @if (request()->hasAny(['institution_id', 'test_status', 'merit_status', 'doc_status']))
            <a href="{{ route('aeo.monitoring.index') }}"
                class="px-4 py-2.5 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
                Clear
            </a>
        @endif
    </form>

    {{-- ── Table ───────────────────────────────────────────────────────── --}}
    <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        Swipe right to see all columns
    </p>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase max-w-[160px] min-w-[120px]">School</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Class</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Test</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Merit</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Docs</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                        @php
                            $testBadge = match ($rec->test_status ?? 'pending') {
                                'passed' => 'bg-green-100 text-green-700',
                                'conducted' => 'bg-blue-100 text-blue-700',
                                'scheduled' => 'bg-yellow-100 text-yellow-700',
                                'failed' => 'bg-red-100 text-red-700',
                                'not_required' => 'bg-gray-100 text-gray-500',
                                default => 'bg-gray-100 text-gray-400',
                            };
                            $meritBadge =
                                $rec->merit_status === 'published'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-yellow-100 text-yellow-700';
                            $docBadge = match ($rec->doc_status ?? 'pending') {
                                'complete' => 'bg-green-100 text-green-700',
                                'provisional' => 'bg-orange-100 text-orange-700',
                                'affidavit_case' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-400',
                            };
                        @endphp
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 max-w-[160px]">
                                <p class="font-medium text-gray-800 truncate max-w-[160px]" title="{{ $rec->institution->name }}">{{ $rec->institution->name }}</p>
                                <p class="text-xs text-gray-400">{{ $rec->institution->code }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $rec->classModel->name }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $testBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $rec->test_status ?? 'pending')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center hidden md:table-cell">
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $meritBadge }}">
                                    {{ ucfirst($rec->merit_status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $docBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $rec->doc_status ?? 'pending')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500 hidden md:table-cell">
                                {{ $rec->admission_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('aeo.monitoring.show', $rec) }}"
                                    class="text-xs px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                                    👁 View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-400">
                                No monitoring records found for {{ $sector->name }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $records->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
