{{-- SAVE AS: resources/views/fde/referrals/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Referrals — FDE Cell')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Referrals</h2>
            <p class="text-sm text-gray-500 mt-1">Manage and track all student referrals sent to schools</p>
        </div>
        <a href="{{ route('fde.referrals.create') }}"
            class="px-5 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition shadow-sm">
            + New Referral
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- ── Stats Cards ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach ([['label' => 'Total', 'value' => $stats->total, 'color' => 'gray'], ['label' => 'Pending', 'value' => $stats->pending, 'color' => 'yellow'], ['label' => 'Accepted', 'value' => $stats->accepted, 'color' => 'green'], ['label' => 'Rejected', 'value' => $stats->rejected, 'color' => 'red'], ['label' => 'Closed', 'value' => $stats->closed, 'color' => 'gray']] as $card)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
                <p class="text-2xl font-bold text-{{ $card['color'] }}-600">{{ $card['value'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('fde.referrals.index') }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5 flex flex-wrap gap-3 items-end">

        <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Statuses</option>
                @foreach (['pending', 'accepted', 'rejected', 're_referred', 'closed'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}
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
                        {{ $inst->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, father name, ref no…"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <button type="submit"
            class="px-5 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            Filter
        </button>
        <a href="{{ route('fde.referrals.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
    </form>

    {{-- ── Table ────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ref No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">School</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Class</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $ref)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">

                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-semibold text-blue-700">{{ $ref->reference_no }}</span>
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $ref->student_name ?? '—' }}</p>
                                @if ($ref->father_name)
                                    <p class="text-xs text-gray-400">S/O {{ $ref->father_name }}</p>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-700">{{ $ref->institution->name }}</p>
                                <p class="text-xs text-gray-400">{{ $ref->institution->sector?->name }}</p>
                            </td>

                            <td class="px-4 py-3 text-gray-600">
                                {{ $ref->classModel?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $ref->created_at->format('d M Y') }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span
                                    class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $ref->statusBadgeClass() }}">
                                    {{ $ref->statusLabel() }}
                                </span>
                                @if ($ref->isRejected())
                                    <p class="text-xs text-red-500 mt-1 max-w-32 mx-auto truncate"
                                        title="{{ $ref->rejection_reason }}">
                                        {{ $ref->rejection_reason }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- View --}}
                                    <a href="{{ route('fde.referrals.show', $ref) }}"
                                        class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                                        View
                                    </a>

                                    {{-- Edit (pending only) --}}
                                    @if ($ref->isPending())
                                        <a href="{{ route('fde.referrals.edit', $ref) }}"
                                            class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('fde.referrals.cancel', $ref) }}"
                                            onsubmit="return confirm('Cancel this referral?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Re-refer (rejected only) --}}
                                    @if ($ref->isRejected())
                                        <a href="{{ route('fde.referrals.re-refer', $ref) }}"
                                            class="text-xs px-3 py-1.5 rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 transition">
                                            Re-refer
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">
                                No referrals found.
                                <a href="{{ route('fde.referrals.create') }}" class="text-blue-600 hover:underline ml-1">
                                    Create the first one →
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($referrals->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $referrals->links() }}
            </div>
        @endif
    </div>

@endsection
