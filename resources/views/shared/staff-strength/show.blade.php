@extends('layouts.app')
@section('title', 'Staff Strength — ' . $register->institution->name)

@section('content')

    {{-- ── Back link ──────────────────────────────────────────────────── --}}
    <a href="{{ route($indexRoute) }}"
        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-5">
        ← Back to list
    </a>

    {{-- ── Metadata header ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-5 mb-6">
        <div class="flex justify-between items-start flex-wrap gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $register->institution->name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    EMIS: <span class="font-medium text-gray-700">{{ $register->institution->code }}</span>
                    · Type: <span class="font-medium text-gray-700">{{ $register->institution->type }}</span>
                    · Sector: <span class="font-medium text-gray-700">{{ $register->institution->sector->name ?? '—' }}</span>
                </p>
                <p class="text-sm text-gray-500 mt-0.5">
                    Academic Year: <span class="font-medium text-gray-700">{{ $register->academicYear->name }}</span>
                </p>
            </div>
            <div class="text-right flex flex-col items-end gap-2">
                @if($register->status === 'locked')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">Locked</span>
                @elseif($register->status === 'submitted')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">Submitted</span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-500">Draft</span>
                @endif

                @can('export', $register)
                    @if(isset($exportPdfRoute) && isset($exportExcelRoute))
                        <div class="flex gap-2 mt-1">
                            <a href="{{ route($exportPdfRoute, $register) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100">
                                ↓ PDF
                            </a>
                            <a href="{{ route($exportExcelRoute, $register) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-green-50 text-green-700 border border-green-200 rounded-lg hover:bg-green-100">
                                ↓ Excel
                            </a>
                        </div>
                    @endif
                @endcan
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-gray-500 border-t border-gray-50 pt-4">
            <div>
                <p class="text-gray-400">Submitted By</p>
                <p class="font-medium text-gray-700 mt-0.5">{{ $register->submittedBy?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Submitted At</p>
                <p class="font-medium text-gray-700 mt-0.5">{{ $register->submitted_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Locked By</p>
                <p class="font-medium text-gray-700 mt-0.5">{{ $register->lockedBy?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Locked At</p>
                <p class="font-medium text-gray-700 mt-0.5">{{ $register->locked_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>
        </div>

        @if($register->fde_remarks)
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-800">
                <p class="font-medium mb-0.5">FDE Remarks</p>
                <p>{{ $register->fde_remarks }}</p>
            </div>
        @endif
    </div>

    {{-- ── Section A — Teaching Posts ──────────────────────────────────── --}}
    <h3 class="text-base font-semibold text-gray-700 mb-3">Section A — Teaching &amp; Academic Posts</h3>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto mb-6">
        <table class="min-w-full text-xs text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3 text-left">Post</th>
                    <th class="px-3 py-3 text-center">Sanctioned</th>
                    <th class="px-3 py-3 text-center">Filled</th>
                    <th class="px-3 py-3 text-center bg-amber-50 text-amber-700">Vacant</th>
                    <th class="px-3 py-3 text-center">Sacked</th>
                    <th class="px-3 py-3 text-center">DW-IN</th>
                    <th class="px-3 py-3 text-center">DW-OUT</th>
                    <th class="px-3 py-3 text-center">Study Leave</th>
                    <th class="px-3 py-3 text-center">Dep-IN</th>
                    <th class="px-3 py-3 text-center">Dep-OUT</th>
                    <th class="px-3 py-3 text-center">Temp-IN</th>
                    <th class="px-3 py-3 text-center">Temp-OUT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($teachingEntries as $entry)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-2.5 font-medium text-gray-800">{{ $entry->postType->name }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->sanctioned_posts }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->filled_posts }}</td>
                        <td class="px-3 py-2.5 text-center bg-amber-50 font-semibold text-amber-700">{{ $entry->vacant_posts }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->sacked_employees }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->daily_wagers_in }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->daily_wagers_out }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->study_leave }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->deputationist_in }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->deputationist_out }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->temporary_in }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $entry->temporary_out }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-6 text-center text-gray-400">No teaching entries recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Section B — Program Posts ────────────────────────────────────── --}}
    <h3 class="text-base font-semibold text-gray-700 mb-3">Section B — Program Posts</h3>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <table class="min-w-full text-xs text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3 text-left">Program</th>
                    <th class="px-4 py-3 text-center">Number of Posts</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($programEntries as $entry)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-2.5 font-medium text-gray-800">{{ $entry->postType->name }}</td>
                        <td class="px-4 py-2.5 text-center">{{ $entry->number_of_posts }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-6 text-center text-gray-400">No program entries recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Footer: Total Present on Duty ───────────────────────────────── --}}
    <div class="bg-blue-50 border border-blue-100 rounded-xl px-6 py-4 flex justify-between items-center">
        <p class="text-sm font-semibold text-blue-800">Total Staff Physically Present on Duty</p>
        <p class="text-2xl font-bold text-blue-900">{{ number_format($register->totalPresentOnDuty()) }}</p>
    </div>

@endsection
