@extends('layouts.app')
@section('title', 'Admission Edit Grants')

@section('content')

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">Admission Edit Grants<x-info-tooltip position="bottom" text="FDE gives a school temporary permission to re-edit a daily admission entry after the daily deadline has passed." /></h2>
            @if ($activeCount > 0)
                <p class="text-sm text-blue-600 mt-1 font-semibold">
                    🔓 {{ $activeCount }} active {{ Str::plural('grant', $activeCount) }}
                </p>
            @endif
        </div>
        <a href="{{ route('fde.admission-grants.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
            + New Grant
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✕ {{ session('error') }}
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4 mb-5">
        @php $activeFilters = collect(request()->except(['page','_token']))->filter(fn($v) => $v !== '' && $v !== null)->count(); @endphp
        <form method="GET" class="flex flex-wrap gap-3 items-end">

            {{-- Sector --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sector</label>
                <select name="sector_id" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Sectors</option>
                    @foreach ($sectors as $s)
                        <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- School --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">School</label>
                <select name="institution_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[180px]">
                    <option value="">All Schools</option>
                    @foreach ($institutions as $inst)
                        <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
                <select name="status"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="active"  {{ request('status') === 'active'  ? 'selected' : '' }}>🔓 Active</option>
                    <option value="used"    {{ request('status') === 'used'    ? 'selected' : '' }}>✓ Used</option>
                    <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>✕ Revoked</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>⏰ Expired</option>
                </select>
            </div>

            {{-- Expiring Soon --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Expiring</label>
                <select name="expiring_soon"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Any</option>
                    <option value="1" {{ request('expiring_soon') ? 'selected' : '' }}>Expiring Soon (≤6 hrs)</option>
                </select>
            </div>

            {{-- Date From --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Date To --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                    Filter @if ($activeFilters > 0)<span class="ml-1 inline-flex items-center justify-center w-5 h-5 bg-white text-blue-900 rounded-full text-xs font-bold">{{ $activeFilters }}</span>@endif
                </button>
                <a href="{{ route('fde.admission-grants.index') }}"
                    class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600 border border-gray-200 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Results count --}}
    <p class="text-xs text-gray-400 mb-3">
        Showing {{ $grants->firstItem() ?? 0 }}–{{ $grants->lastItem() ?? 0 }}
        of {{ number_format($grants->total()) }} grants
    </p>

    <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        Swipe right to see all columns
    </p>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ revokeModal: null }">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">School</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Date Range</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Granted By</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Expires At (PKT)</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden lg:table-cell">Granted On</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Status</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grants as $grant)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors
                            {{ $grant->isActive() ? 'bg-blue-50' : '' }}">

                            {{-- School --}}
                            <td class="px-3 py-3 max-w-[128px] sm:max-w-none">
                                <div class="truncate font-medium text-gray-900 max-w-[120px] sm:max-w-none"
                                    title="{{ $grant->institution->name }}">{{ $grant->institution->name ?? '—' }}</div>
                            </td>

                            {{-- Date Range --}}
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ $grant->date_from->format('d M Y') }}
                                <span class="text-gray-400">–</span>
                                {{ $grant->date_to->format('d M Y') }}
                            </td>

                            {{-- Granted By --}}
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell">
                                {{ $grant->grantedBy?->name ?? '—' }}
                            </td>

                            {{-- Expires At --}}
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                @php
                                    $expiryPkt = $grant->expires_at->timezone('Asia/Karachi');
                                    $isExpiringSoon = $grant->isActive() && $expiryPkt->diffInHours(now()) < 2;
                                @endphp
                                <span class="{{ $isExpiringSoon ? 'text-orange-600 font-semibold' : 'text-gray-600' }} text-xs">
                                    {{ $expiryPkt->format('d M Y') }}<br>
                                    {{ $expiryPkt->format('g:i A') }}
                                </span>
                            </td>

                            {{-- Granted On --}}
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden lg:table-cell">
                                {{ $grant->created_at->timezone('Asia/Karachi')->format('d M Y') }}<br>
                                <span class="text-xs text-gray-400">{{ $grant->created_at->timezone('Asia/Karachi')->format('g:i A') }}</span>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $grant->statusBadgeClass() }}">
                                    {{ $grant->statusLabel() }}
                                </span>
                                @if ($grant->isRevoked())
                                    <p class="text-xs text-gray-400 mt-1 whitespace-nowrap hidden sm:block">
                                        by {{ $grant->revokedBy?->name ?? '—' }}
                                        on {{ $grant->revoked_at?->timezone('Asia/Karachi')->format('d M') }}
                                    </p>
                                    @if ($grant->revoke_reason)
                                        <p class="text-xs text-red-400 mt-0.5 max-w-[140px] truncate hidden sm:block" title="{{ $grant->revoke_reason }}">
                                            {{ Str::limit($grant->revoke_reason, 40) }}
                                        </p>
                                    @endif
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                @if ($grant->isActive())
                                    <button
                                        @click="revokeModal = {{ $grant->id }}"
                                        class="px-2 py-1.5 sm:px-3 text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 rounded-lg transition whitespace-nowrap">
                                        Revoke
                                    </button>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Inline revoke modal (Alpine) --}}
                        @if ($grant->isActive())
                        <template x-teleport="body">
                            <div
                                x-show="revokeModal === {{ $grant->id }}"
                                x-cloak
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
                                @keydown.escape.window="revokeModal = null">

                                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6"
                                     @click.outside="revokeModal = null">

                                    <h3 class="text-lg font-bold text-gray-800 mb-1">Revoke Edit Grant</h3>
                                    <p class="text-sm text-gray-500 mb-4">
                                        Revoking the grant for
                                        <strong>{{ $grant->institution->name }}</strong>
                                        will immediately prevent post-lock edits.
                                    </p>

                                    <form action="{{ route('fde.admission-grants.revoke', $grant) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">
                                                Reason for Revocation <span class="text-red-500">*</span>
                                            </label>
                                            <textarea
                                                name="revoke_reason"
                                                rows="3"
                                                minlength="10"
                                                maxlength="500"
                                                required
                                                placeholder="Minimum 10 characters…"
                                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                                        </div>
                                        <div class="flex gap-3 justify-end">
                                            <button type="button" @click="revokeModal = null"
                                                class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="px-4 py-2 text-sm font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                                Revoke Grant
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">
                                @if (request()->hasAny(['sector_id', 'institution_id', 'status', 'expiring_soon', 'date_from', 'date_to']))
                                    No grants match your filters.
                                @else
                                    No edit grants issued yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($grants->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Page {{ $grants->currentPage() }} of {{ $grants->lastPage() }}
                </p>
                {{ $grants->links() }}
            </div>
        @endif
    </div>

@endsection
