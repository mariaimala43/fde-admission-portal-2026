{{-- SAVE AS: resources/views/fde/monitoring/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Monitoring — ' . ($monitoring->institution->name ?? 'Record'))

@section('content')

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Admission Monitoring</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $monitoring->institution->name }}
                @if ($monitoring->institution->sector)
                    · {{ $monitoring->institution->sector->name }}
                @endif
            </p>
        </div>
        <a href="{{ route('fde.monitoring.index') }}"
            class="inline-flex items-center gap-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
            ← Back to All Records
        </a>
    </div>

    {{-- ── Section 1 — Student / Admission Info Card ───────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-5 mb-5">
        <div class="flex flex-wrap items-start justify-between gap-4">

            <div class="flex-1 min-w-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Student Name</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $monitoring->dailyAdmission->student_name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Father Name</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $monitoring->dailyAdmission->father_name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Class</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $monitoring->classModel->name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Gender</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ ucfirst($monitoring->dailyAdmission->gender ?? '—') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Admission Date</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $monitoring->admission_date?->format('d M Y') ?? '—' }}
                        </p>
                    </div>

                </div>
            </div>

            <div class="shrink-0 text-right">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Workflow Status</p>
                <span class="text-xs px-3 py-1.5 rounded-full font-semibold {{ $monitoring->workflowBadge() }}">
                    {{ $monitoring->workflowLabel() }}
                </span>
            </div>

        </div>
    </div>

    {{-- ── Section 2 — Workflow Progress Bar ──────────────────────────────── --}}
    @php
        $wfOrder = ['test_verification', 'merit_confirmation', 'doc_verification', 'finalized'];
        $wfSteps = [
            'test_verification' => 'Written Test',
            'merit_confirmation' => 'Merit List',
            'doc_verification' => 'Document Check',
        ];
        $currentWf = $monitoring->workflow_status;
        $currentIdx = array_search($currentWf, $wfOrder);
        if ($currentIdx === false) {
            $currentIdx = -1;
        }
        $stepKeys = array_keys($wfSteps);
    @endphp

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-5 mb-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-4">Progress</p>
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-0">
            @foreach ($wfSteps as $key => $label)
                @php
                    $stepIdx = array_search($key, $wfOrder);
                    $isDone = $currentWf === 'finalized' || $currentIdx > $stepIdx;
                    $isCurrent = $currentIdx === $stepIdx;
                    $loopIdx = array_search($key, $stepKeys);
                    $isLast = $loopIdx === count($stepKeys) - 1;
                @endphp

                <div class="flex sm:flex-col items-center gap-3 sm:gap-1 flex-1 sm:flex-none">
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                        {{ $isDone
                            ? 'bg-green-500 text-white'
                            : ($isCurrent
                                ? 'bg-blue-900 text-white ring-4 ring-blue-100'
                                : 'bg-gray-100 text-gray-400') }}">
                        @if ($isDone)
                            ✓
                        @else
                            {{ $loopIdx + 1 }}
                        @endif
                    </div>
                    <p
                        class="mt-0 sm:mt-1.5 text-xs font-medium
                        {{ $isDone ? 'text-green-600' : ($isCurrent ? 'text-blue-900' : 'text-gray-400') }}">
                        {{ $label }}
                    </p>
                </div>

                @if (!$isLast)
                    @php
                        $nextKey = $stepKeys[$loopIdx + 1];
                        $nextIdx = array_search($nextKey, $wfOrder);
                        $connDone = $currentWf === 'finalized' || $currentIdx > $nextIdx;
                    @endphp
                    <div class="hidden sm:block flex-1 h-0.5 mb-5 mx-1 {{ $connDone ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── Section 3 — Test Status Card ───────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-5 mb-5">

        <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-gray-800">Admission Test</h3>
            <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $monitoring->testStatusBadge() }}">
                {{ $monitoring->testStatusLabel() }}
            </span>
        </div>

        @if ($monitoring->test_updated_at)
            <p class="text-xs text-gray-400 mb-4">
                Updated {{ $monitoring->test_updated_at->format('d M Y, H:i') }}
                @if ($monitoring->testUpdatedBy)
                    by <span class="font-medium text-gray-600">{{ $monitoring->testUpdatedBy->name }}</span>
                @endif
            </p>
        @endif

        <form method="POST" action="{{ url('fde/monitoring/' . $monitoring->id . '/test') }}"
            class="border-t border-gray-100 pt-4 mt-2">
            @csrf
            @method('PATCH')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Test Status</label>
                    <select name="test_status"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="not_required" {{ $monitoring->test_status === 'not_required' ? 'selected' : '' }}>
                            Not Required</option>
                        <option value="pending" {{ $monitoring->test_status === 'pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="passed" {{ $monitoring->test_status === 'passed' ? 'selected' : '' }}>Passed
                        </option>
                        <option value="failed" {{ $monitoring->test_status === 'failed' ? 'selected' : '' }}>Failed
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Reason / Notes <span class="text-gray-400 font-normal">(recommended)</span>
                    </label>
                    <input type="text" name="reason" maxlength="500" placeholder="Why are you making this change?"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">
                    💾 Update Test Status
                </button>
            </div>
        </form>

    </div>

    {{-- ── Section 4 — Merit Status Card ──────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-5 mb-5">

        <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-gray-800">Merit List</h3>
            <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $monitoring->meritStatusBadge() }}">
                {{ $monitoring->meritStatusLabel() }}
            </span>
        </div>

        @if ($monitoring->merit_updated_at)
            <p class="text-xs text-gray-400 mb-4">
                Updated {{ $monitoring->merit_updated_at->format('d M Y, H:i') }}
                @if ($monitoring->meritUpdatedBy)
                    by <span class="font-medium text-gray-600">{{ $monitoring->meritUpdatedBy->name }}</span>
                @endif
            </p>
        @endif

        @if ($monitoring->isBlocked())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">
                🚫 Processing blocked — student rejected on merit list.
            </div>
        @endif

        @if ($monitoring->isFinalized())
            <div class="bg-gray-50 border border-gray-200 text-gray-500 rounded-xl px-4 py-3 text-sm">
                ℹ️ Record is finalized — merit status cannot be changed.
            </div>
        @else
            <form method="POST" action="{{ url('fde/monitoring/' . $monitoring->id . '/merit') }}"
                class="border-t border-gray-100 pt-4 mt-2
                         {{ $monitoring->isBlocked() ? 'opacity-50 pointer-events-none' : '' }}">
                @csrf
                @method('PATCH')
                <fieldset @if ($monitoring->isBlocked()) disabled @endif>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Merit Status</label>
                            <select name="merit_status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="pending" {{ $monitoring->merit_status === 'pending' ? 'selected' : '' }}>
                                    Pending</option>
                                <option value="selected" {{ $monitoring->merit_status === 'selected' ? 'selected' : '' }}>
                                    Selected</option>
                                <option value="waiting" {{ $monitoring->merit_status === 'waiting' ? 'selected' : '' }}>
                                    Waiting</option>
                                <option value="rejected" {{ $monitoring->merit_status === 'rejected' ? 'selected' : '' }}>
                                    Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                Reason / Notes <span class="text-gray-400 font-normal">(recommended)</span>
                            </label>
                            <input type="text" name="reason" maxlength="500"
                                placeholder="Why are you making this change?"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">
                            💾 Update Merit Status
                        </button>
                    </div>
                </fieldset>
            </form>
        @endif

    </div>

    {{-- ── Section 5 — Document Status Card ───────────────────────────────── --}}
    @php
        $canComplete = $monitoring->canCompleteDoc();
        $meritSelected = $monitoring->merit_status === 'selected';
    @endphp

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-5 mb-5">

        <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-gray-800">Documentation</h3>
            <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $monitoring->docStatusBadge() }}">
                {{ $monitoring->docStatusLabel() }}
            </span>
        </div>

        @if ($monitoring->doc_updated_at)
            <p class="text-xs text-gray-400 mb-2">
                Updated {{ $monitoring->doc_updated_at->format('d M Y, H:i') }}
                @if ($monitoring->docUpdatedBy)
                    by <span class="font-medium text-gray-600">{{ $monitoring->docUpdatedBy->name }}</span>
                @endif
            </p>
        @endif

        @if ($monitoring->doc_override_at && $monitoring->docOverrideBy)
            <p class="text-xs text-purple-500 mb-3">
                🔓 FDE override by <span class="font-medium">{{ $monitoring->docOverrideBy->name }}</span>
                on {{ $monitoring->doc_override_at->format('d M Y, H:i') }}
                @if ($monitoring->doc_override_reason)
                    — <em>{{ $monitoring->doc_override_reason }}</em>
                @endif
            </p>
        @endif

        {{-- Affidavit file --}}
        @if ($monitoring->affidavit_path)
            <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 mb-4 text-sm">
                📎
                <a href="{{ Storage::url($monitoring->affidavit_path) }}" target="_blank"
                    class="text-blue-700 hover:underline truncate">
                    {{ $monitoring->affidavit_original_name ?? basename($monitoring->affidavit_path) }}
                </a>
            </div>
        @endif

        {{-- Business-rule warnings --}}
        @if (!$canComplete)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl px-4 py-3 mb-3 text-sm">
                ⚠️ Cannot mark Complete — test was failed or merit is not Selected.
            </div>
        @elseif(!$meritSelected)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl px-4 py-3 mb-3 text-sm">
                ⚠️ Merit must be Selected before documentation can be marked Complete.
            </div>
        @endif

        {{-- Override form --}}
        <form method="POST" action="{{ url('fde/monitoring/' . $monitoring->id . '/doc') }}"
            enctype="multipart/form-data" class="border-t border-gray-100 pt-4 mt-2">
            @csrf
            @method('PATCH')

            <div class="space-y-4">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Documentation Status</label>
                    <select name="doc_status" id="docStatusSelect" onchange="toggleDocExtras(this.value)"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="pending" {{ $monitoring->doc_status === 'pending' ? 'selected' : '' }}>
                            Pending</option>
                        <option value="provisional" {{ $monitoring->doc_status === 'provisional' ? 'selected' : '' }}>
                            Provisional</option>
                        <option value="affidavit_case"
                            {{ $monitoring->doc_status === 'affidavit_case' ? 'selected' : '' }}>Affidavit Case</option>
                        <option value="complete" {{ $monitoring->doc_status === 'complete' ? 'selected' : '' }}
                            @if (!$canComplete) disabled @endif>
                            ✅ Complete (FDE Override)@if (!$canComplete)
                                — unavailable
                            @endif
                        </option>
                    </select>
                </div>

                {{-- Affidavit upload — visible only when affidavit_case selected --}}
                <div id="affidavitUploadSection"
                    class="{{ $monitoring->doc_status === 'affidavit_case' ? '' : 'hidden' }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Upload Affidavit <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="affidavit" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG — max 5 MB</p>
                </div>

                {{-- Override reason — visible only when complete selected --}}
                <div id="overrideReasonSection" class="{{ $monitoring->doc_status === 'complete' ? '' : 'hidden' }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Override Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea name="override_reason" rows="2" minlength="10" maxlength="500"
                        placeholder="Reason for marking documentation as Complete…"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                                     focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
                </div>

                {{-- General audit reason --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Reason / Notes <span class="text-gray-400 font-normal">(recommended)</span>
                    </label>
                    <textarea name="reason" rows="2" maxlength="500" placeholder="Why are you making this change?"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                                     focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
                    <p class="text-xs text-gray-400 mt-1">This will be recorded in the audit log.</p>
                </div>

            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">
                    💾 Save Override
                </button>
            </div>
        </form>

    </div>

    {{-- ── Section 6 — Finalization Banner ─────────────────────────────────── --}}
    @if ($monitoring->isFinalized())
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4 mb-5 flex items-start gap-3">
            <span class="text-xl">✅</span>
            <div>
                <p class="font-semibold text-sm">Admission Finalized</p>
                @if ($monitoring->finalized_at)
                    <p class="text-xs mt-0.5">
                        {{ $monitoring->finalized_at->format('d M Y, H:i') }}
                        @if ($monitoring->finalizedBy)
                            by <span class="font-medium">{{ $monitoring->finalizedBy->name }}</span>
                        @endif
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Section 7 — Audit Trail ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-5">

        <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-700 text-sm">🕐 Audit Trail</h3>
            <span class="text-xs text-gray-400">{{ $monitoring->audits->count() }} change(s)</span>
        </div>

        @if ($monitoring->audits->isNotEmpty())
            <p class="text-xs text-gray-400 px-5 pt-2 sm:hidden">Scroll horizontally to see all columns</p>
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide whitespace-nowrap">
                                Date / Time</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide whitespace-nowrap">
                                Changed By</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide hidden sm:table-cell">Role</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Field</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide whitespace-nowrap hidden sm:table-cell">
                                Old Value</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide whitespace-nowrap">
                                New Value</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- audits() relationship uses ->latest() so already newest-first --}}
                        @foreach ($monitoring->audits as $audit)
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    <span class="text-gray-500 text-xs">{{ $audit->created_at->format('d M Y') }}</span><br>
                                    <span class="text-gray-400 text-xs">{{ $audit->created_at->format('H:i') }}</span>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    <span class="font-medium text-gray-800 text-xs">{{ $audit->changedBy->name ?? '—' }}</span>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                    <span class="text-gray-500 text-xs">{{ $audit->role_at_time ?? '—' }}</span>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">
                                        {{ $audit->field_name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                    <span class="text-xs text-red-500 line-through">{{ $audit->old_value ?: '—' }}</span>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    <span class="text-xs text-green-700 font-semibold">{{ $audit->new_value ?: '—' }}</span>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 hidden md:table-cell">
                                    <span class="text-xs text-gray-500">{{ $audit->reason ? \Illuminate\Support\Str::limit($audit->reason, 80) : '—' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                No changes recorded yet.
            </div>
        @endif

    </div>

@endsection

@push('scripts')
    <script>
        function toggleDocExtras(val) {
            document.getElementById('affidavitUploadSection').classList.toggle('hidden', val !== 'affidavit_case');
            document.getElementById('overrideReasonSection').classList.toggle('hidden', val !== 'complete');
        }
    </script>
@endpush
