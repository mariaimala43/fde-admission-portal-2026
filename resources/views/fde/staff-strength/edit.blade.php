@extends('layouts.app')
@section('title', 'Edit Staff Strength — ' . $register->institution->name)

@section('content')

    <a href="{{ route('fde.staff-strength.show', $register) }}"
        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-5">
        ← Back to register
    </a>

    <div class="page-header">
        <div>
            <h2 class="page-title">Edit Staff Strength Register</h2>
            <p class="page-sub">
                {{ $register->institution->name }} · {{ $register->institution->type }} · {{ $register->academicYear->name }}
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('fde.staff-strength.update', $register) }}">
        @csrf
        @method('PUT')

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
                                <input type="number" min="0"
                                    name="entries[{{ $postType->id }}][number_of_posts]"
                                    value="{{ $entry?->number_of_posts ?? 0 }}"
                                    class="w-24 text-center border border-gray-200 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-400 focus:outline-none">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── FDE Remarks ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">FDE Remarks (optional)</label>
            <textarea name="fde_remarks" rows="3"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                placeholder="Enter any remarks for the institution...">{{ old('fde_remarks', $register->fde_remarks) }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm transition-colors">
                Save Changes
            </button>
            <a href="{{ route('fde.staff-strength.show', $register) }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg text-sm transition-colors">
                Cancel
            </a>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.sanctioned-input, .filled-input').forEach(input => {
        input.addEventListener('input', function () {
            const row = this.dataset.row;
            const sanctioned = parseInt(document.querySelector(`.sanctioned-input[data-row="${row}"]`)?.value || 0, 10);
            const filled     = parseInt(document.querySelector(`.filled-input[data-row="${row}"]`)?.value || 0, 10);
            const span = document.getElementById('vacant-' + row);
            if (span) span.textContent = Math.max(0, sanctioned - filled);
        });
    });
</script>
@endpush
