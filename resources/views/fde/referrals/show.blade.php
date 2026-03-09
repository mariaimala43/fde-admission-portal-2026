{{-- SAVE AS: resources/views/fde/referrals/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Referral ' . $referral->reference_no)

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-xs text-gray-400 mb-1">
                <a href="{{ route('fde.referrals.index') }}" class="hover:underline">Referrals</a> →
                {{ $referral->reference_no }}
            </p>
            <h2 class="text-2xl font-bold text-gray-800">Referral {{ $referral->reference_no }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $referral->institution->name }}
                @if ($referral->institution->sector)
                    · {{ $referral->institution->sector->name }}
                @endif
            </p>
        </div>
        <a href="{{ route('fde.referrals.index') }}"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">
            ← Back to Referrals
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">❌ {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Main Details ─────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Status + Student card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-start mb-5">
                    <h3 class="text-sm font-bold text-gray-800">Referral Details</h3>
                    <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $referral->statusBadgeClass() }}">
                        {{ $referral->statusLabel() }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Reference No</p>
                        <p class="font-mono font-bold text-blue-700">{{ $referral->reference_no }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Referred To</p>
                        <p class="font-semibold text-gray-800">{{ $referral->institution->name }}</p>
                        <p class="text-xs text-gray-400">{{ $referral->institution->sector?->name }}</p>
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
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Referred By</p>
                        <p class="font-semibold text-gray-800">{{ $referral->referredBy?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $referral->created_at->format('d M Y') }}</p>
                    </div>
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

            {{-- Timeline --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Activity Timeline</h3>
                <div class="space-y-3 text-sm">

                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                        <div>
                            <p class="font-medium text-gray-800">Referral created</p>
                            <p class="text-xs text-gray-400">
                                {{ $referral->created_at->format('d M Y, g:i A') }}
                                by {{ $referral->referredBy?->name ?? 'FDE Cell' }}
                            </p>
                        </div>
                    </div>

                    @if ($referral->parentReferral)
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-orange-400 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium text-gray-800">Re-referred from rejected referral</p>
                                <p class="text-xs text-gray-400">
                                    Original: <span class="font-mono">{{ $referral->parentReferral->reference_no }}</span>
                                    was sent to {{ $referral->parentReferral->institution?->name }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($referral->status === 'accepted')
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium text-green-700">Accepted by HOI ✅</p>
                                <p class="text-xs text-gray-400">
                                    {{ $referral->actionedBy?->name }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($referral->isRejected())
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium text-red-700">Rejected by HOI</p>
                                @if ($referral->rejection_reason)
                                    <p class="text-xs text-red-600 mt-1 bg-red-50 rounded px-2 py-1">
                                        {{ $referral->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($referral->status === 're_referred' && $referral->reReferredTo)
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-orange-500 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium text-orange-700">Re-referred to different school</p>
                                <p class="text-xs text-gray-400">
                                    New referral <span class="font-mono">{{ $referral->reReferredTo->reference_no }}</span>
                                    sent to {{ $referral->reReferredTo->institution?->name }}
                                </p>
                                <a href="{{ route('fde.referrals.show', $referral->reReferredTo) }}"
                                    class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                                    View new referral →
                                </a>
                            </div>
                        </div>
                    @endif

                    @if ($referral->status === 'closed')
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-gray-400 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium text-gray-600">Cancelled / Closed</p>
                                @if ($referral->closed_at)
                                    <p class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($referral->closed_at)->format('d M Y, g:i A') }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ── Action Panel ─────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Edit (pending only) --}}
            @if ($referral->isPending())
                <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-1">✏️ Edit Referral</h4>
                    <p class="text-xs text-gray-500 mb-3">Change school, class, or student details before HOI acts.</p>
                    <a href="{{ route('fde.referrals.edit', $referral) }}"
                        class="block w-full py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition text-center">
                        Edit Referral
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-1">🚫 Cancel Referral</h4>
                    <p class="text-xs text-gray-500 mb-3">Cancel this referral before the HOI responds.</p>
                    <form method="POST" action="{{ route('fde.referrals.cancel', $referral) }}"
                        onsubmit="return confirm('Cancel referral {{ $referral->reference_no }}?')">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="w-full py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition">
                            Cancel Referral
                        </button>
                    </form>
                </div>
            @endif

            {{-- Re-refer (rejected only) --}}
            @if ($referral->isRejected())
                <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-1">🔄 Re-refer to Different School</h4>
                    <p class="text-xs text-gray-500 mb-3">
                        This referral was rejected. Send the student to a different school.
                        Student details will be pre-filled.
                    </p>
                    <a href="{{ route('fde.referrals.re-refer', $referral) }}"
                        class="block w-full py-2.5 bg-orange-500 text-white text-sm font-semibold rounded-lg hover:bg-orange-600 transition text-center">
                        🔄 Re-refer Student
                    </a>
                </div>
            @endif

            {{-- Already re-referred --}}
            @if ($referral->status === 're_referred' && $referral->reReferredTo)
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 text-sm">
                    <p class="font-semibold text-orange-700 mb-1">Already Re-referred</p>
                    <p class="text-xs text-gray-500 mb-2">
                        This student has been re-referred to:
                        <strong>{{ $referral->reReferredTo->institution?->name }}</strong>
                    </p>
                    <a href="{{ route('fde.referrals.show', $referral->reReferredTo) }}"
                        class="text-xs text-blue-600 hover:underline">
                        View new referral ({{ $referral->reReferredTo->reference_no }}) →
                    </a>
                </div>
            @endif

            {{-- No actions available --}}
            @if (!$referral->isPending() && !$referral->isRejected() && $referral->status !== 're_referred')
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-5 text-center text-sm text-gray-400">
                    No actions available.<br>
                    This referral is <strong>{{ strtolower($referral->statusLabel()) }}</strong>.
                </div>
            @endif

        </div>
    </div>

@endsection
