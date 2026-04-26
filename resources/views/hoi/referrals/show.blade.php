{{-- resources/views/hoi/referrals/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Referral ' . $referral->reference_no)

@section('content')

    {{-- ── Page Header ─────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <div>
            <p class="text-xs text-gray-400 mb-1">
                <a href="{{ route('hoi.referrals.index') }}" class="hover:underline">Referrals</a>
                → {{ $referral->reference_no }}
            </p>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Referral {{ $referral->reference_no }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $institution->name }}</p>
        </div>
        <a href="{{ route('hoi.referrals.index') }}"
            class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg px-4 py-2 transition">
            ← Back to Referrals
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">❌
            {{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left Column: Details + Tracking ────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Referral Details Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-start mb-5">
                    <h3 class="text-sm font-bold text-gray-800">Referral Details</h3>
                    <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $referral->statusBadgeClass() }}">
                        {{ $referral->statusLabel() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Reference No</p>
                        <p class="font-mono font-bold text-blue-700">{{ $referral->reference_no }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Referred By</p>
                        <p class="font-semibold text-gray-800">{{ $referral->referredBy?->name ?? 'FDE Cell' }}</p>
                        <p class="text-xs text-gray-400">{{ $referral->created_at->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Student Name</p>
                        <p class="font-semibold text-gray-800">{{ $referral->student_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Father's Name</p>
                        <p class="font-semibold text-gray-800">{{ $referral->father_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Class</p>
                        <p class="font-semibold text-gray-800">{{ $referral->classModel?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Gender</p>
                        <p class="font-semibold text-gray-800">{{ ucfirst($referral->gender ?? '—') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Shift</p>
                        <p class="font-semibold text-gray-800">{{ ucfirst($referral->shift) }}</p>
                    </div>
                    @if ($referral->isAccepted() && $referral->accepted_at)
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Accepted On</p>
                            <p class="font-semibold text-green-700">
                                {{ $referral->accepted_at->format('d M Y') }}
                            </p>
                        </div>
                    @endif
                </div>

                @if ($referral->notes)
                    <div class="mt-4 bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Notes</p>
                        <p class="text-sm text-gray-700">{{ $referral->notes }}</p>
                    </div>
                @endif

                @if ($referral->isRejected() && $referral->rejection_reason)
                    <div class="mt-4 bg-red-50 border border-red-100 rounded-lg px-4 py-3">
                        <p class="text-xs text-red-400 uppercase tracking-wide mb-1">Rejection Reason</p>
                        <p class="text-sm text-red-700">{{ $referral->rejection_reason }}</p>
                    </div>
                @endif
            </div>

            {{-- ── Post-Acceptance Tracking Card ───────────────────── --}}
            @if ($referral->isAccepted())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-1">📋 Post-Acceptance Tracking</h3>
                    <p class="text-xs text-gray-400 mb-5">
                        Record the test outcome and final admission decision for this referred student.
                    </p>

                    {{-- ── Stage 1: Test ────────────────────────────────── --}}
                    <div class="border border-gray-100 rounded-xl p-5 mb-4"
                         x-data="{
                             editing: {{ $referral->test_conducted ? 'false' : 'true' }},
                             testConducted: '{{ $referral->test_conducted ?? '' }}'
                         }">

                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-sm font-semibold text-gray-700">🧪 Stage 1 — Admission Test</h4>
                            @if ($referral->test_conducted)
                                <button type="button" @click="editing = !editing"
                                    class="text-xs px-3 py-1 rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 transition"
                                    x-text="editing ? 'Cancel' : '✏️ Edit'">
                                </button>
                            @endif
                        </div>

                        {{-- Read-only display --}}
                        <div x-show="!editing">
                            @if ($referral->test_conducted)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase mb-0.5">Test Conducted</p>
                                        <p class="font-semibold text-gray-800 capitalize">
                                            {{ match($referral->test_conducted) {
                                                'yes'      => 'Yes',
                                                'no'       => 'No (no test required)',
                                                'exempted' => 'Exempted',
                                                default    => $referral->test_conducted,
                                            } }}
                                        </p>
                                    </div>
                                    @if ($referral->test_conducted === 'yes')
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase mb-0.5">Result</p>
                                            <span class="inline-block text-xs px-2.5 py-1 rounded-full font-semibold
                                                {{ $referral->test_result === 'pass'
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-red-100 text-red-700' }}">
                                                {{ $referral->test_result === 'pass' ? '✅ Pass' : '❌ Fail' }}
                                            </span>
                                        </div>
                                    @endif
                                    @if ($referral->test_updated_at)
                                        <div class="col-span-2 pt-1">
                                            <p class="text-xs text-gray-400">
                                                Updated by <strong>{{ $referral->testUpdatedBy?->name ?? '—' }}</strong>
                                                on {{ $referral->test_updated_at->format('d M Y, g:i A') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm text-gray-400 italic">No test details recorded yet.</p>
                            @endif
                        </div>

                        {{-- Edit form --}}
                        <form x-show="editing" method="POST"
                              action="{{ route('hoi.referrals.update-test', $referral) }}"
                              class="mt-1">
                            @csrf
                            <div class="space-y-4">

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                        Was a test conducted? <span class="text-red-500">*</span>
                                    </label>
                                    <select name="test_conducted" required
                                        @change="testConducted = $event.target.value"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        <option value="">— Select —</option>
                                        <option value="yes"
                                            {{ $referral->test_conducted === 'yes' ? 'selected' : '' }}>
                                            Yes
                                        </option>
                                        <option value="no"
                                            {{ $referral->test_conducted === 'no' ? 'selected' : '' }}>
                                            No (no test required)
                                        </option>
                                        <option value="exempted"
                                            {{ $referral->test_conducted === 'exempted' ? 'selected' : '' }}>
                                            Exempted
                                        </option>
                                    </select>
                                </div>

                                <div x-show="testConducted === 'yes'">
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                        Test Result <span class="text-red-500">*</span>
                                    </label>
                                    <select name="test_result"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        <option value="">— Select —</option>
                                        <option value="pass"
                                            {{ $referral->test_result === 'pass' ? 'selected' : '' }}>
                                            ✅ Pass
                                        </option>
                                        <option value="fail"
                                            {{ $referral->test_result === 'fail' ? 'selected' : '' }}>
                                            ❌ Fail
                                        </option>
                                    </select>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-2">
                                    <button type="submit"
                                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-700 text-white rounded-xl text-sm font-semibold
                                               hover:bg-blue-800 transition">
                                        Save Test Details
                                    </button>
                                    @if ($referral->test_conducted)
                                        <button type="button" @click="editing = false"
                                            class="w-full sm:w-auto px-4 py-2.5 text-sm text-gray-500 hover:text-gray-700
                                                   border border-gray-200 rounded-xl transition">
                                            Cancel
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- ── Stage 2: Admission Decision ─────────────────── --}}
                    @if ($referral->canUpdateAdmission())
                        <div class="border border-gray-100 rounded-xl p-5"
                             x-data="{ editing: {{ $referral->admission_status ? 'false' : 'true' }} }">

                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-semibold text-gray-700">🎓 Stage 2 — Admission Decision</h4>
                                @if ($referral->admission_status)
                                    <button type="button" @click="editing = !editing"
                                        class="text-xs px-3 py-1 rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 transition"
                                        x-text="editing ? 'Cancel' : '✏️ Edit'">
                                    </button>
                                @endif
                            </div>

                            {{-- Read-only display --}}
                            <div x-show="!editing">
                                @if ($referral->admission_status)
                                    <div class="space-y-2">
                                        <span class="inline-block text-sm px-3 py-1.5 rounded-full font-semibold {{ $referral->trackingBadgeClass() }}">
                                            {{ $referral->trackingStatusLabel() }}
                                        </span>
                                        @if ($referral->admission_updated_at)
                                            <p class="text-xs text-gray-400">
                                                Updated by <strong>{{ $referral->admissionUpdatedBy?->name ?? '—' }}</strong>
                                                on {{ $referral->admission_updated_at->format('d M Y, g:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400 italic">No decision recorded yet.</p>
                                @endif
                            </div>

                            {{-- Edit form --}}
                            <form x-show="editing" method="POST"
                                  action="{{ route('hoi.referrals.update-admission', $referral) }}"
                                  class="mt-1">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-2">
                                            Admission Decision <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <label class="flex items-center gap-3 border border-gray-200 rounded-xl px-4 py-3
                                                          cursor-pointer hover:bg-emerald-50 hover:border-emerald-300 transition">
                                                <input type="radio" name="admission_status" value="admitted" required
                                                    {{ $referral->admission_status === 'admitted' ? 'checked' : '' }}
                                                    class="text-emerald-600">
                                                <span class="text-sm font-medium text-gray-700">✅ Admitted</span>
                                            </label>
                                            <label class="flex items-center gap-3 border border-gray-200 rounded-xl px-4 py-3
                                                          cursor-pointer hover:bg-orange-50 hover:border-orange-300 transition">
                                                <input type="radio" name="admission_status" value="not_admitted" required
                                                    {{ $referral->admission_status === 'not_admitted' ? 'checked' : '' }}
                                                    class="text-orange-600">
                                                <span class="text-sm font-medium text-gray-700">Not Admitted</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <button type="submit"
                                            class="w-full sm:w-auto px-5 py-2.5 bg-emerald-700 text-white rounded-xl text-sm font-semibold
                                                   hover:bg-emerald-800 transition">
                                            Save Admission Decision
                                        </button>
                                        @if ($referral->admission_status)
                                            <button type="button" @click="editing = false"
                                                class="w-full sm:w-auto px-4 py-2.5 text-sm text-gray-500 hover:text-gray-700
                                                       border border-gray-200 rounded-xl transition">
                                                Cancel
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        {{-- Stage 2 locked until Stage 1 is complete --}}
                        <div class="border border-dashed border-gray-200 rounded-xl p-5 text-center">
                            <p class="text-sm text-gray-400">🎓 Stage 2 — Admission Decision</p>
                            <p class="text-xs text-gray-300 mt-1">
                                Complete Stage 1 (Test) first to unlock this section.
                            </p>
                        </div>
                    @endif

                </div>
            @endif

        </div>

        {{-- ── Right Column: Status + Quick Actions ────────────────── --}}
        <div class="space-y-4">

            {{-- Overall tracking status badge --}}
            @if ($referral->isAccepted())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                        Overall Tracking Status
                    </h4>
                    <span class="inline-block text-sm px-4 py-2 rounded-full font-semibold {{ $referral->trackingBadgeClass() }}">
                        {{ $referral->trackingStatusLabel() }}
                    </span>

                    {{-- 3-step progress summary --}}
                    <div class="mt-4 space-y-2 text-xs text-gray-500">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-white text-xs font-bold
                                {{ $referral->test_conducted ? 'bg-green-500' : 'bg-gray-200' }}">
                                {{ $referral->test_conducted ? '✓' : '1' }}
                            </span>
                            <span class="{{ $referral->test_conducted ? 'text-green-700 font-medium' : '' }}">
                                Test Stage
                                @if ($referral->test_conducted === 'yes')
                                    — {{ ucfirst($referral->test_result ?? 'pending result') }}
                                @elseif ($referral->test_conducted)
                                    — {{ ucfirst($referral->test_conducted) }}
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-white text-xs font-bold
                                {{ $referral->admission_status ? 'bg-green-500' : 'bg-gray-200' }}">
                                {{ $referral->admission_status ? '✓' : '2' }}
                            </span>
                            <span class="{{ $referral->admission_status ? 'text-green-700 font-medium' : '' }}">
                                Admission Decision
                                @if ($referral->admission_status)
                                    — {{ $referral->admission_status === 'admitted' ? 'Admitted' : 'Not Admitted' }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Quick actions for pending referrals --}}
            @if ($referral->isPending())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-3">Respond to Referral</h4>
                    <div class="space-y-2">
                        <button type="button"
                            onclick="openAcceptModal({{ $referral->id }}, '{{ $referral->reference_no }}', '{{ $referral->gender }}', '{{ $referral->shift }}')"
                            class="w-full py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold
                                   hover:bg-green-700 transition">
                            ✅ Accept Referral
                        </button>
                        <button type="button"
                            onclick="openRejectModal({{ $referral->id }}, '{{ $referral->reference_no }}')"
                            class="w-full py-2.5 bg-red-50 text-red-700 rounded-xl text-sm font-medium
                                   hover:bg-red-100 transition border border-red-100">
                            ❌ Reject Referral
                        </button>
                    </div>
                </div>
            @endif

            {{-- Status info for non-pending --}}
            @if (!$referral->isPending())
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 text-center text-xs text-gray-400">
                    Referral status: <strong class="text-gray-600">{{ $referral->statusLabel() }}</strong>
                </div>
            @endif

        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════
         ACCEPT MODAL (only if pending)
    ══════════════════════════════════════════════════════════════ --}}
    @if ($referral->isPending())
        <div id="acceptModal" class="fixed inset-0 bg-black/40 z-50 items-center justify-center p-4"
             style="display:none">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">

                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">✅ Accept Referral</h3>
                    <button onclick="closeAcceptModal()"
                        class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
                </div>

                <form id="acceptForm" method="POST"
                      action="{{ route('hoi.referrals.accept', $referral) }}">
                    @csrf
                    @method('PATCH')

                    <div class="px-6 py-5 space-y-4">
                        <p class="text-sm text-gray-600">
                            Accepting referral <strong>{{ $referral->reference_no }}</strong> will add
                            <strong>1 student</strong> to today's daily admission count.
                        </p>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                Shift <span class="text-red-500">*</span>
                            </label>
                            <select name="shift" id="acceptShift" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-green-400">
                                <option value="morning" {{ $referral->shift === 'morning' ? 'selected' : '' }}>
                                    🌅 Morning
                                </option>
                                <option value="evening" {{ $referral->shift === 'evening' ? 'selected' : '' }}>
                                    🌆 Evening
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                Gender <span class="text-red-500">*</span>
                            </label>
                            <select name="gender" id="acceptGender" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-green-400">
                                <option value="male"   {{ $referral->gender === 'male'   ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $referral->gender === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        <div class="bg-green-50 border border-green-100 rounded-lg px-4 py-3 text-xs text-green-700">
                            ℹ️ This will increment today's
                            <strong id="acceptPreview">morning boys</strong> count by 1 and mark as verified.
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3">
                        <button type="submit"
                            class="flex-1 py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold
                                   hover:bg-green-700 transition">
                            ✅ Confirm Accept
                        </button>
                        <button type="button" onclick="closeAcceptModal()"
                            class="px-5 py-2.5 text-sm text-gray-500 hover:text-gray-700 rounded-xl border border-gray-200">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- REJECT MODAL --}}
        <div id="rejectModal" class="fixed inset-0 bg-black/40 z-50 items-center justify-center p-4"
             style="display:none">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">

                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">❌ Reject Referral</h3>
                    <button onclick="closeRejectModal()"
                        class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
                </div>

                <form id="rejectForm" method="POST"
                      action="{{ route('hoi.referrals.reject', $referral) }}">
                    @csrf
                    @method('PATCH')

                    <div class="px-6 py-5 space-y-4">
                        <p class="text-sm text-gray-600">
                            Rejecting referral <strong>{{ $referral->reference_no }}</strong>.
                            Provide a reason — the FDE Cell can re-refer this student to another school.
                        </p>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                Reason for Rejection <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_reason" required minlength="10" maxlength="500" rows="4"
                                placeholder="e.g. No seats available in this class…"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                                       focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
                            <p class="text-xs text-gray-400 mt-1">Minimum 10 characters.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3">
                        <button type="submit"
                            class="flex-1 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold
                                   hover:bg-red-700 transition">
                            ❌ Confirm Reject
                        </button>
                        <button type="button" onclick="closeRejectModal()"
                            class="px-5 py-2.5 text-sm text-gray-500 hover:text-gray-700 rounded-xl border border-gray-200">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        function openAcceptModal() {
            document.getElementById('acceptModal').style.display = 'flex';
            updatePreview();
        }
        function closeAcceptModal() {
            document.getElementById('acceptModal').style.display = 'none';
        }
        function openRejectModal() {
            document.getElementById('rejectModal').style.display = 'flex';
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }
        function updatePreview() {
            const shift  = document.getElementById('acceptShift')?.value  ?? 'morning';
            const gender = document.getElementById('acceptGender')?.value ?? 'male';
            const el = document.getElementById('acceptPreview');
            if (el) el.textContent = `${shift} ${gender === 'male' ? 'boys' : 'girls'}`;
        }
        document.getElementById('acceptShift')?.addEventListener('change', updatePreview);
        document.getElementById('acceptGender')?.addEventListener('change', updatePreview);

        ['acceptModal','rejectModal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', function(e) {
                if (e.target === this) this.style.display = 'none';
            });
        });
    </script>
@endpush
