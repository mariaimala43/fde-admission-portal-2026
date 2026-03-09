{{-- SAVE AS: resources/views/fde/monitoring/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Admission Monitoring — All Records')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Monitoring — All Records</h2>
            <p class="text-sm text-gray-500 mt-1">Full system view · {{ $academicYear?->name }}</p>
        </div>
        <a href="{{ route('fde.monitoring.dashboard') }}"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
            ← Dashboard
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $e)
                <p>{{ $e }}</p>
            @endforeach
        </div>
    @endif

    {{-- ── Filters ──────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('fde.monitoring.index') }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5 flex flex-wrap gap-3 items-end">

        <div>
            <label class="block text-xs text-gray-500 mb-1">Sector</label>
            <select name="sector_id"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Sectors</option>
                @foreach ($sectors as $s)
                    <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">School</label>
            <select name="institution_id"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Schools</option>
                @foreach ($institutions as $inst)
                    <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>
                        {{ $inst->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Workflow</label>
            <select name="workflow"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Stages</option>
                @foreach (['draft' => 'Draft', 'test_verification' => 'Test Verification', 'merit_confirmation' => 'Merit Confirmation', 'doc_verification' => 'Doc Review', 'finalized' => 'Finalized'] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('workflow') === $val ? 'selected' : '' }}>
                        {{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Doc Status</label>
            <select name="doc_status"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All</option>
                @foreach (['pending' => 'Pending', 'provisional' => 'Provisional', 'affidavit_case' => 'Affidavit Case', 'complete' => 'Complete'] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('doc_status') === $val ? 'selected' : '' }}>
                        {{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Test Status</label>
            <select name="test_status"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All</option>
                @foreach (['not_required' => 'Not Required', 'pending' => 'Pending', 'passed' => 'Passed', 'failed' => 'Failed'] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('test_status') === $val ? 'selected' : '' }}>
                        {{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Search School</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or code…"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition">Filter</button>
        <a href="{{ route('fde.monitoring.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
    </form>

    {{-- ── Records Table ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">School / Class</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Workflow</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Test</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Merit</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Docs</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Override</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr
                            class="border-b border-gray-50 hover:bg-gray-50 transition-colors
                           {{ $record->isBlocked() ? 'bg-red-50' : '' }}
                           {{ $record->isFinalized() ? 'bg-green-50' : '' }}">

                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $record->admission_date->format('d M Y') }}
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800 text-xs">{{ $record->institution->name }}</p>
                                <p class="text-xs text-gray-400">{{ $record->classModel?->name }} ·
                                    {{ $record->institution->sector?->name }}</p>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->workflowBadge() }}">
                                    {{ $record->workflowLabel() }}
                                </span>
                            </td>

                            {{-- Test override --}}
                            <td class="px-4 py-3 text-center">
                                <button type="button"
                                    onclick="openOverrideModal({{ $record->id }}, 'test', '{{ $record->test_status }}')"
                                    class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->testStatusBadge() }} hover:opacity-80">
                                    {{ $record->testStatusLabel() }}
                                </button>
                            </td>

                            {{-- Merit override --}}
                            <td class="px-4 py-3 text-center">
                                <button type="button"
                                    onclick="openOverrideModal({{ $record->id }}, 'merit', '{{ $record->merit_status }}')"
                                    class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->meritStatusBadge() }} hover:opacity-80">
                                    {{ $record->meritStatusLabel() }}
                                </button>
                            </td>

                            {{-- Doc override --}}
                            <td class="px-4 py-3 text-center">
                                <button type="button"
                                    onclick="openOverrideModal({{ $record->id }}, 'doc', '{{ $record->doc_status }}')"
                                    class="text-xs px-2 py-1 rounded-full font-semibold {{ $record->docStatusBadge() }} hover:opacity-80">
                                    {{ $record->docStatusLabel() }}
                                    @if ($record->doc_status === 'affidavit_case' && $record->affidavit_path)
                                        📎
                                    @endif
                                </button>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('fde.monitoring.show', $record) }}"
                                    class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                                    Audit Log
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">
                                No records found. Try adjusting your filters or
                                <a href="{{ route('fde.monitoring.sync') }}" class="text-blue-600 hover:underline">sync
                                    records</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $records->links() }}</div>
        @endif
    </div>


    {{-- ══════════════════════════════════════════════════════════════════
     FDE OVERRIDE MODAL — handles test / merit / doc in one modal
     ══════════════════════════════════════════════════════════════════ --}}
    <div id="overrideModal" class="fixed inset-0 bg-black/40 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800" id="overrideTitle">Override Status</h3>
                <button onclick="closeOverrideModal()" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
            </div>

            <form id="overrideForm" method="POST" action="" enctype="multipart/form-data">
                @csrf @method('PATCH')

                <div class="px-6 py-5 space-y-4">

                    {{-- Test status select --}}
                    <div id="testFields" class="hidden">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Admission Test Status</label>
                        <select name="test_status" id="testSelect"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="not_required">Not Required</option>
                            <option value="pending">Pending</option>
                            <option value="passed">Passed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>

                    {{-- Merit status select --}}
                    <div id="meritFields" class="hidden">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Merit List Status</label>
                        <select name="merit_status" id="meritSelect"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="pending">Pending</option>
                            <option value="selected">Selected</option>
                            <option value="waiting">Waiting</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    {{-- Doc status select --}}
                    <div id="docFields" class="hidden">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Documentation Status</label>
                        <select name="doc_status" id="docSelect" onchange="toggleDocExtras(this.value)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="pending">Pending</option>
                            <option value="provisional">Provisional</option>
                            <option value="affidavit_case">Affidavit Case</option>
                            <option value="complete">✅ Complete (FDE Override)</option>
                        </select>

                        {{-- Affidavit upload --}}
                        <div id="affidavitUpload" class="hidden mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Upload Affidavit <span
                                    class="text-red-500">*</span></label>
                            <input type="file" name="affidavit" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG — max 5MB</p>
                        </div>

                        {{-- Override reason (required for 'complete') --}}
                        <div id="overrideReasonSection" class="hidden mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                Override Reason <span class="text-red-500">*</span>
                            </label>
                            <textarea name="override_reason" rows="2" minlength="10" maxlength="500"
                                placeholder="Reason for marking documentation as Complete…"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                                         focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
                        </div>
                    </div>

                    {{-- Audit reason (all field types) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Reason / Notes
                            <span class="text-gray-400 font-normal">(recommended)</span>
                        </label>
                        <textarea name="reason" rows="2" maxlength="500" placeholder="Why are you making this change?"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                                     focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
                        <p class="text-xs text-gray-400 mt-1">This will be recorded in the audit log.</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">
                        💾 Save Override
                    </button>
                    <button type="button" onclick="closeOverrideModal()"
                        class="px-5 py-2.5 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const baseUrl = "{{ url('fde/monitoring') }}";

        function openOverrideModal(id, type, currentValue) {
            // Reset all field sections
            ['testFields', 'meritFields', 'docFields'].forEach(f =>
                document.getElementById(f).classList.add('hidden')
            );

            const titleMap = {
                test: 'Override Test Status',
                merit: 'Override Merit Status',
                doc: 'Override Documentation Status'
            };
            document.getElementById('overrideTitle').textContent = titleMap[type];

            const routeMap = {
                test: 'test',
                merit: 'merit',
                doc: 'doc'
            };
            document.getElementById('overrideForm').action = `${baseUrl}/${id}/${routeMap[type]}`;

            if (type === 'test') {
                document.getElementById('testFields').classList.remove('hidden');
                document.getElementById('testSelect').value = currentValue;
            } else if (type === 'merit') {
                document.getElementById('meritFields').classList.remove('hidden');
                document.getElementById('meritSelect').value = currentValue;
            } else if (type === 'doc') {
                document.getElementById('docFields').classList.remove('hidden');
                document.getElementById('docSelect').value = currentValue;
                toggleDocExtras(currentValue);
            }

            document.getElementById('overrideModal').classList.remove('hidden');
        }

        function closeOverrideModal() {
            document.getElementById('overrideModal').classList.add('hidden');
        }

        function toggleDocExtras(val) {
            document.getElementById('affidavitUpload').classList.toggle('hidden', val !== 'affidavit_case');
            document.getElementById('overrideReasonSection').classList.toggle('hidden', val !== 'complete');
        }
    </script>
@endpush
