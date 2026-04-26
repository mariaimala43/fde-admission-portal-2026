{{-- SAVE AS: resources/views/hoi/referrals/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Referrals — ' . $institution->name)

@section('content')

    {{-- ── Page Header ─────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">Admission Referrals<x-info-tooltip position="bottom" text="Students referred to your school by FDE for admission. Confirm whether each student was admitted." /></h2>
            <p class="text-sm text-gray-500 mt-1">Students referred to your school by the FDE Cell</p>
        </div>
        @if ($stats->pending > 0)
            <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-xl text-sm font-semibold">
                ⏳ {{ $stats->pending }} Pending Response
            </span>
        @endif
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">❌ {{ session('error') }}
        </div>
    @endif

    {{-- ── Stats Cards ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach ([['label' => 'Total', 'value' => $stats->total, 'color' => 'gray'], ['label' => 'Pending', 'value' => $stats->pending, 'color' => 'yellow'], ['label' => 'Accepted', 'value' => $stats->accepted, 'color' => 'green'], ['label' => 'Rejected', 'value' => $stats->rejected, 'color' => 'red']] as $card)
            <a href="{{ route('hoi.referrals.index', ['status' => strtolower($card['label'])]) }}"
                class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center hover:shadow-md transition">
                <p class="text-2xl font-bold text-{{ $card['color'] }}-600">{{ $card['value'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</p>
            </a>
        @endforeach
    </div>

    {{-- ── Status Tabs ─────────────────────────────────────────────── --}}
    <div class="flex gap-1 mb-4 bg-gray-100 rounded-xl p-1 w-fit">
        @foreach (['all' => 'All', 'pending' => '⏳ Pending', 'accepted' => '✅ Accepted', 'rejected' => '❌ Rejected'] as $val => $label)
            <a href="{{ route('hoi.referrals.index', array_filter(['status' => $val, 'class_id' => request('class_id')])) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium transition
                      {{ ($status ?? 'all') === $val ? 'bg-white text-blue-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ── Class Filter ─────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('hoi.referrals.index') }}" class="mb-5 flex items-end gap-3">
        <input type="hidden" name="status" value="{{ $status ?? 'all' }}">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Class</label>
            <select name="class_id" onchange="this.form.submit()"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Classes</option>
                @foreach ($classes as $cls)
                    <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                @endforeach
            </select>
        </div>
        @if (request()->filled('class_id'))
            <a href="{{ route('hoi.referrals.index', ['status' => $status ?? 'all']) }}"
                class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600 border border-gray-200 rounded-lg self-end">
                Clear
            </a>
        @endif
    </form>

    {{-- ── Table ───────────────────────────────────────────────────── --}}
    <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Swipe right to see all columns</p>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-100 bg-gray-50">
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Ref No</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Student</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Class</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Shift</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Referred On</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Status</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Tracking</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $ref)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                <span class="font-mono text-xs font-semibold text-blue-700">{{ $ref->reference_no }}</span>
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <p class="font-medium text-gray-800">{{ $ref->student_name ?? '—' }}</p>
                                @if ($ref->father_name)
                                    <p class="text-xs text-gray-400">S/O {{ $ref->father_name }}</p>
                                @endif
                                @if ($ref->notes)
                                    <p class="text-xs text-blue-500 mt-0.5 italic">{{ Str::limit($ref->notes, 50) }}</p>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ $ref->classModel?->name ?? '—' }}
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell capitalize">
                                {{ $ref->shift }}
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                <p class="text-gray-600">{{ $ref->created_at->format('d M Y') }}</p>
                                <p class="text-gray-400 text-xs">{{ $ref->created_at->diffForHumans() }}</p>
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <span
                                    class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $ref->statusBadgeClass() }}">
                                    {{ $ref->statusLabel() }}
                                </span>
                                @if ($ref->isRejected() && $ref->rejection_reason)
                                    <p class="text-xs text-red-500 mt-1 max-w-xs mx-auto truncate"
                                        title="{{ $ref->rejection_reason }}">
                                        {{ $ref->rejection_reason }}
                                    </p>
                                @endif
                                @if ($ref->isAccepted() && $ref->accepted_at)
                                    <p class="text-xs text-green-600 mt-1">{{ $ref->accepted_at->format('d M Y') }}</p>
                                @endif
                            </td>

                            {{-- Tracking status (only meaningful after acceptance) --}}
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell">
                                @if ($ref->isAccepted())
                                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $ref->trackingBadgeClass() }}">
                                        {{ $ref->trackingStatusLabel() }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <div class="flex items-center justify-start gap-2">
                                    @if ($ref->isPending())
                                        <button type="button"
                                            onclick="openAcceptModal({{ $ref->id }}, '{{ $ref->reference_no }}', '{{ $ref->gender }}', '{{ $ref->shift }}')"
                                            class="text-xs px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700 transition font-medium">
                                            ✅ Accept
                                        </button>
                                        <button type="button"
                                            onclick="openRejectModal({{ $ref->id }}, '{{ $ref->reference_no }}')"
                                            class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                                            ❌ Reject
                                        </button>
                                    @elseif ($ref->isAccepted())
                                        <a href="{{ route('hoi.referrals.show', $ref) }}"
                                            class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                            📋 Track
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No action needed</span>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400 text-sm">
                                No {{ ($status ?? 'all') !== 'all' ? $status : '' }} referrals for your school.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($referrals->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $referrals->withQueryString()->links() }}
            </div>
        @endif
    </div>


    {{-- ══════════════════════════════════════════════════════════════
         ACCEPT MODAL
    ══════════════════════════════════════════════════════════════ --}}
    <div id="acceptModal" class="fixed inset-0 bg-black/40 z-50 items-center justify-center p-4" style="display:none">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">✅ Accept Referral</h3>
                <button onclick="closeAcceptModal()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
            </div>

            <form id="acceptForm" method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="px-6 py-5 space-y-4">

                    <p class="text-sm text-gray-600">
                        Accepting referral <strong id="acceptRefNo"></strong> will add
                        <strong>1 student</strong> to today's daily admission count for your school.
                    </p>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Shift <span class="text-red-500">*</span>
                        </label>
                        <select name="shift" id="acceptShift" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-green-400">
                            <option value="morning">🌅 Morning</option>
                            <option value="evening">🌆 Evening</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Gender <span class="text-red-500">*</span>
                        </label>
                        <select name="gender" id="acceptGender" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-green-400">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>

                    <div class="bg-green-50 border border-green-100 rounded-lg px-4 py-3 text-xs text-green-700">
                        ℹ️ This will increment today's
                        <strong id="acceptPreview">morning boys</strong> count by 1 and mark as verified.
                    </div>

                </div>

                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition">
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


    {{-- ══════════════════════════════════════════════════════════════
         REJECT MODAL
    ══════════════════════════════════════════════════════════════ --}}
    <div id="rejectModal" class="fixed inset-0 bg-black/40 z-50 items-center justify-center p-4" style="display:none">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">❌ Reject Referral</h3>
                <button onclick="closeRejectModal()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
            </div>

            <form id="rejectForm" method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="px-6 py-5 space-y-4">

                    <p class="text-sm text-gray-600">
                        Rejecting referral <strong id="rejectRefNo"></strong>.
                        Provide a reason — the FDE Cell can re-refer this student to another school.
                    </p>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Reason for Rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea name="rejection_reason" required minlength="10" maxlength="500" rows="4"
                            placeholder="e.g. No seats available in this class, school is single-gender…"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                                         focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
                        <p class="text-xs text-gray-400 mt-1">Minimum 10 characters.</p>
                    </div>

                </div>

                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
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

@endsection

@push('scripts')
    <script>
        const referralBaseUrl = "{{ url('hoi/referrals') }}";

        function openAcceptModal(id, refNo, gender, shift) {
            document.getElementById('acceptRefNo').textContent = refNo;
            document.getElementById('acceptForm').action = `${referralBaseUrl}/${id}/accept`;
            if (gender) document.getElementById('acceptGender').value = gender;
            if (shift) document.getElementById('acceptShift').value = shift;
            updatePreview();
            document.getElementById('acceptModal').style.display = 'flex';
        }

        function closeAcceptModal() {
            document.getElementById('acceptModal').style.display = 'none';
        }

        function openRejectModal(id, refNo) {
            document.getElementById('rejectRefNo').textContent = refNo;
            document.getElementById('rejectForm').action = `${referralBaseUrl}/${id}/reject`;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        function updatePreview() {
            const shift = document.getElementById('acceptShift').value;
            const gender = document.getElementById('acceptGender').value;
            document.getElementById('acceptPreview').textContent =
                `${shift} ${gender === 'male' ? 'boys' : 'girls'}`;
        }

        document.getElementById('acceptShift')?.addEventListener('change', updatePreview);
        document.getElementById('acceptGender')?.addEventListener('change', updatePreview);

        // Close modals on backdrop click
        ['acceptModal', 'rejectModal'].forEach(id => {
            document.getElementById(id).addEventListener('click', function(e) {
                if (e.target === this) this.style.display = 'none';
            });
        });
    </script>
@endpush
