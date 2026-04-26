{{-- SAVE AS: resources/views/hoi/transfers/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Transfer Request #' . $transfer->id)

@section('content')

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Transfer Request #{{ $transfer->id }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $transfer->fromInstitution->name }} → {{ $transfer->toInstitution->name }}
            </p>
        </div>
        <a href="{{ route('hoi.transfers.index') }}"
            class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg px-4 py-2 transition">
            ← Back to Transfers
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Transfer Details Card ─────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-start mb-5">
                    <h3 class="text-sm font-bold text-gray-800">Transfer Details</h3>
                    <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $transfer->statusBadgeClass() }}">
                        {{ $transfer->statusLabel() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">From School</p>
                        <p class="font-semibold text-gray-800">{{ $transfer->fromInstitution->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">To School</p>
                        <p class="font-semibold text-gray-800">{{ $transfer->toInstitution->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Class</p>
                        <p class="font-semibold text-gray-800">{{ $transfer->classModel->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Initiated By</p>
                        <p class="font-semibold text-gray-800">{{ $transfer->initiatedBy->name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $transfer->initiated_by_role === 'fde_cell' ? 'FDE Cell' : 'HOI' }} ·
                            {{ $transfer->created_at->format('d M Y') }}</p>
                    </div>

                    @if ($transfer->student_name)
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Student Name</p>
                            <p class="font-semibold text-gray-800">{{ $transfer->student_name }}</p>
                        </div>
                    @endif
                    @if ($transfer->father_name)
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Father's Name</p>
                            <p class="font-semibold text-gray-800">{{ $transfer->father_name }}</p>
                        </div>
                    @endif
                </div>

                @if ($transfer->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Notes</p>
                        <p class="text-sm text-gray-700">{{ $transfer->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- ── Status Timeline ──────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Activity</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                        <div>
                            <p class="font-medium text-gray-800">Request submitted</p>
                            <p class="text-xs text-gray-400">{{ $transfer->created_at->format('d M Y, g:i A') }} by
                                {{ $transfer->initiatedBy->name }}</p>
                        </div>
                    </div>

                    @if ($transfer->isInfoRequested())
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-blue-400 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium text-gray-800">More info requested</p>
                                <p class="text-xs text-gray-400">
                                    {{ $transfer->info_requested_at?->format('d M Y, g:i A') }} by
                                    {{ $transfer->actionedBy?->name }}</p>
                                <p class="text-sm text-blue-700 mt-1 bg-blue-50 rounded px-2 py-1">
                                    {{ $transfer->info_request_note }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($transfer->isAccepted())
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium text-green-700">Transfer accepted ✅</p>
                                <p class="text-xs text-gray-400">{{ $transfer->accepted_at?->format('d M Y, g:i A') }} by
                                    {{ $transfer->actionedBy?->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">Enrollment counts have been updated.</p>
                            </div>
                        </div>
                    @endif

                    @if ($transfer->isRejected())
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium text-red-700">Transfer rejected</p>
                                <p class="text-xs text-gray-400">{{ $transfer->rejected_at?->format('d M Y, g:i A') }} by
                                    {{ $transfer->actionedBy?->name }}</p>
                                <p class="text-sm text-red-700 mt-1 bg-red-50 rounded px-2 py-1">
                                    {{ $transfer->rejection_reason }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($transfer->isCancelled())
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-gray-400 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium text-gray-600">Request cancelled</p>
                                <p class="text-xs text-gray-400">{{ $transfer->cancelled_at?->format('d M Y, g:i A') }} by
                                    {{ $transfer->actionedBy?->name }}</p>
                                @if ($transfer->cancellation_reason)
                                    <p class="text-sm text-gray-600 mt-1 bg-gray-50 rounded px-2 py-1">
                                        {{ $transfer->cancellation_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── Action Panel ──────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Receiving HOI actions --}}
            @if ($isReceiving && $transfer->isActionable())

                {{-- Accept --}}
                <div class="bg-white rounded-xl shadow-sm border border-green-100 p-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-2">✅ Accept Transfer</h4>
                    <p class="text-xs text-gray-500 mb-3">Enrollment will update automatically. This cannot be undone.</p>
                    <form method="POST" action="{{ route('hoi.transfers.accept', $transfer) }}"
                        onsubmit="return confirm('Accept this transfer? Enrollment counts will be updated immediately.')">
                        @csrf
                        <button type="submit"
                            class="w-full py-2.5 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 transition">
                            Accept Transfer
                        </button>
                    </form>
                </div>

                {{-- Request Info --}}
                @if ($transfer->isPending())
                    <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5">
                        <h4 class="text-sm font-bold text-gray-800 mb-2">ℹ️ Request More Info</h4>
                        <form method="POST" action="{{ route('hoi.transfers.request-info', $transfer) }}">
                            @csrf
                            <textarea name="info_request_note" rows="3" required
                                placeholder="What information do you need from the sending school?"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 mb-3"></textarea>
                            <button type="submit"
                                class="w-full py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                                Send Info Request
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Reject --}}
                <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-2">❌ Reject Transfer</h4>
                    <form method="POST" action="{{ route('hoi.transfers.reject', $transfer) }}">
                        @csrf
                        <textarea name="rejection_reason" rows="3" required placeholder="Reason for rejection (required)..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 mb-3"></textarea>
                        <button type="submit" onclick="return confirm('Reject this transfer request?')"
                            class="w-full py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition">
                            Reject Transfer
                        </button>
                    </form>
                </div>

            @endif

            {{-- Sending HOI can cancel --}}
            @if ($isSending && $transfer->isActionable())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-2">🚫 Cancel Request</h4>
                    <form method="POST" action="{{ route('hoi.transfers.cancel', $transfer) }}">
                        @csrf
                        <textarea name="cancellation_reason" rows="2" placeholder="Reason for cancellation (optional)..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 mb-3"></textarea>
                        <button type="submit" onclick="return confirm('Cancel this transfer request?')"
                            class="w-full py-2.5 bg-gray-600 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition">
                            Cancel Request
                        </button>
                    </form>
                </div>
            @endif

            {{-- No actions available --}}
            @if (!$transfer->isActionable())
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-5 text-center text-sm text-gray-400">
                    No actions available.<br>This request is {{ strtolower($transfer->statusLabel()) }}.
                </div>
            @endif

        </div>
    </div>

@endsection
