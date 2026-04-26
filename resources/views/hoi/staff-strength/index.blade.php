@extends('layouts.app')
@section('title', 'Staff Strength Register')

@section('content')

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h2 class="page-title">Staff Strength Register</h2>
            <p class="page-sub">
                {{ $institution->name }} · {{ $institution->type }} · {{ $academicYear->name }}
            </p>
        </div>
        @if($register->isLocked())
            <span class="badge badge-blue" style="font-size:13px;padding:6px 16px;">🔒 Locked by FDE</span>
        @elseif($register->isSubmitted())
            <span class="badge badge-green" style="font-size:13px;padding:6px 16px;">✅ Submitted</span>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ❌ {{ session('error') }}
        </div>
    @endif

    @if($register->isLocked() && $register->fde_remarks)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-4 mb-5 text-sm text-yellow-800">
            <p class="font-semibold mb-1">FDE Remarks</p>
            <p>{{ $register->fde_remarks }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('hoi.staff-strength.save') }}" id="staffForm">
        @csrf

        {{-- ── Section A — Teaching Posts ──────────────────────────────── --}}
        <h3 class="text-base font-semibold text-gray-700 mb-3">Section A — Teaching &amp; Academic Posts</h3>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto mb-6">
            <table class="min-w-full text-xs text-gray-700">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left min-w-[160px]">Post</th>
                        <th class="px-2 py-3 text-center">Sanctioned</th>
                        <th class="px-2 py-3 text-center">Filled</th>
                        <th class="px-2 py-3 text-center bg-amber-50 text-amber-700">Vacant</th>
                        <th class="px-2 py-3 text-center">Sacked</th>
                        <th class="px-2 py-3 text-center">DW-IN</th>
                        <th class="px-2 py-3 text-center">DW-OUT</th>
                        <th class="px-2 py-3 text-center">Study Leave</th>
                        <th class="px-2 py-3 text-center">Dep-IN</th>
                        <th class="px-2 py-3 text-center">Dep-OUT</th>
                        <th class="px-2 py-3 text-center">Temp-IN</th>
                        <th class="px-2 py-3 text-center">Temp-OUT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($teachingTypes as $postType)
                        @php $entry = $entries[$postType->id] ?? null; @endphp
                        <tr class="hover:bg-gray-50/40">
                            <td class="px-4 py-2 font-medium text-gray-800">{{ $postType->name }}</td>
                            @if($register->isLocked())
                                <td class="px-2 py-2 text-center">{{ $entry?->sanctioned_posts ?? 0 }}</td>
                                <td class="px-2 py-2 text-center">{{ $entry?->filled_posts ?? 0 }}</td>
                                <td class="px-2 py-2 text-center bg-amber-50 font-semibold text-amber-700">{{ $entry?->vacant_posts ?? 0 }}</td>
                                <td class="px-2 py-2 text-center">{{ $entry?->sacked_employees ?? 0 }}</td>
                                <td class="px-2 py-2 text-center">{{ $entry?->daily_wagers_in ?? 0 }}</td>
                                <td class="px-2 py-2 text-center">{{ $entry?->daily_wagers_out ?? 0 }}</td>
                                <td class="px-2 py-2 text-center">{{ $entry?->study_leave ?? 0 }}</td>
                                <td class="px-2 py-2 text-center">{{ $entry?->deputationist_in ?? 0 }}</td>
                                <td class="px-2 py-2 text-center">{{ $entry?->deputationist_out ?? 0 }}</td>
                                <td class="px-2 py-2 text-center">{{ $entry?->temporary_in ?? 0 }}</td>
                                <td class="px-2 py-2 text-center">{{ $entry?->temporary_out ?? 0 }}</td>
                            @else
                                @php
                                    $f = [
                                        'sanctioned_posts','filled_posts','sacked_employees',
                                        'daily_wagers_in','daily_wagers_out','study_leave',
                                        'deputationist_in','deputationist_out','temporary_in','temporary_out'
                                    ];
                                @endphp
                                <td class="px-1 py-1">
                                    <input type="number" min="0"
                                        name="entries[{{ $postType->id }}][sanctioned_posts]"
                                        value="{{ $entry?->sanctioned_posts ?? 0 }}"
                                        class="w-16 text-center border border-gray-200 rounded px-1 py-1 text-xs focus:ring-1 focus:ring-blue-400 focus:outline-none sanctioned-input"
                                        data-row="{{ $postType->id }}">
                                </td>
                                <td class="px-1 py-1">
                                    <input type="number" min="0"
                                        name="entries[{{ $postType->id }}][filled_posts]"
                                        value="{{ $entry?->filled_posts ?? 0 }}"
                                        class="w-16 text-center border border-gray-200 rounded px-1 py-1 text-xs focus:ring-1 focus:ring-blue-400 focus:outline-none filled-input"
                                        data-row="{{ $postType->id }}">
                                </td>
                                <td class="px-1 py-1 bg-amber-50">
                                    <span id="vacant-{{ $postType->id }}"
                                        class="block w-16 text-center font-semibold text-amber-700 py-1">
                                        {{ $entry?->vacant_posts ?? 0 }}
                                    </span>
                                </td>
                                @foreach(['sacked_employees','daily_wagers_in','daily_wagers_out','study_leave','deputationist_in','deputationist_out','temporary_in','temporary_out'] as $field)
                                <td class="px-1 py-1">
                                    <input type="number" min="0"
                                        name="entries[{{ $postType->id }}][{{ $field }}]"
                                        value="{{ $entry?->$field ?? 0 }}"
                                        class="w-16 text-center border border-gray-200 rounded px-1 py-1 text-xs focus:ring-1 focus:ring-blue-400 focus:outline-none">
                                </td>
                                @endforeach
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Section B — Program Posts ────────────────────────────────── --}}
        <h3 class="text-base font-semibold text-gray-700 mb-3">Section B — Program Posts</h3>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <table class="min-w-full text-xs text-gray-700">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">Program</th>
                        <th class="px-4 py-3 text-center w-36">Number of Posts</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($programTypes as $postType)
                        @php $entry = $entries[$postType->id] ?? null; @endphp
                        <tr class="hover:bg-gray-50/40">
                            <td class="px-4 py-2 font-medium text-gray-800">{{ $postType->name }}</td>
                            <td class="px-4 py-2 text-center">
                                @if($register->isLocked())
                                    {{ $entry?->number_of_posts ?? 0 }}
                                @else
                                    <input type="number" min="0"
                                        name="entries[{{ $postType->id }}][number_of_posts]"
                                        value="{{ $entry?->number_of_posts ?? 0 }}"
                                        class="w-24 text-center border border-gray-200 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-400 focus:outline-none">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Total Present on Duty ────────────────────────────────────── --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl px-6 py-4 flex justify-between items-center mb-6">
            <p class="text-sm font-semibold text-blue-800">Total Staff Physically Present on Duty</p>
            <p class="text-2xl font-bold text-blue-900" id="total-present">
                {{ number_format($register->totalPresentOnDuty()) }}
            </p>
        </div>

        {{-- ── Actions ──────────────────────────────────────────────────── --}}
        @if(! $register->isLocked())
            <div class="flex gap-3">
                <button type="submit" name="action" value="save"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm transition-colors">
                    Save Draft
                </button>
                <button type="submit" name="action" value="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm transition-colors"
                    onclick="return confirm('Submit staff strength register? You can still edit it after submission until FDE locks it.')">
                    Submit Register
                </button>
            </div>
        @else
            <p class="text-sm text-gray-500">
                This register has been locked by FDE. Contact FDE Cell if corrections are needed.
            </p>
        @endif
    </form>

@endsection

@push('scripts')
<script>
    // Live vacant = sanctioned − filled
    document.querySelectorAll('.sanctioned-input, .filled-input').forEach(input => {
        input.addEventListener('input', function () {
            const row = this.dataset.row;
            const sanctioned = parseInt(document.querySelector(`.sanctioned-input[data-row="${row}"]`)?.value || 0, 10);
            const filled     = parseInt(document.querySelector(`.filled-input[data-row="${row}"]`)?.value || 0, 10);
            const vacant     = Math.max(0, sanctioned - filled);
            const span = document.getElementById('vacant-' + row);
            if (span) span.textContent = vacant;
        });
    });
</script>
@endpush
