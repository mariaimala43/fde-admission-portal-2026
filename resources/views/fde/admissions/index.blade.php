{{-- SAVE AS: resources/views/fde/admissions/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Daily Admissions Management')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Daily Admissions</h2>
            <p class="text-sm text-gray-500 mt-1">Override or return HOI admission entries ·
                {{ $academicYear?->name ?? 'Active Year' }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">❌ {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-gray-700">{{ number_format($stats['total']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Total Entries</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-gray-400">{{ number_format($stats['draft']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Draft</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-green-600">{{ number_format($stats['verified']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Verified / Locked</p>
        </div>
        <div class="bg-white rounded-xl border border-red-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-red-500">{{ number_format($stats['returned']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Returned</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-orange-500">{{ number_format($stats['overridden']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Overridden by FDE</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5">
        <form method="GET" class="flex flex-wrap gap-3 items-end">

            <div>
                <label class="block text-xs text-gray-500 mb-1">Sector</label>
                <select name="sector_id" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Sectors</option>
                    @foreach ($sectors as $s)
                        <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Institution</label>
                <select name="institution_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Schools</option>
                    @foreach ($institutions as $inst)
                        <option value="{{ $inst->id }}"
                            {{ request('institution_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>Locked</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Date</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <button type="submit"
                class="px-5 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                Filter
            </button>
            @if (request()->hasAny(['sector_id', 'institution_id', 'status', 'date', 'date_from', 'date_to']))
                <a href="{{ route('fde.admissions.index') }}"
                    class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead
                    class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">School</th>
                        <th class="px-4 py-3 text-left">Sector</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-center">Morning</th>
                        <th class="px-4 py-3 text-center">Evening</th>
                        <th class="px-4 py-3 text-center">OOSC</th>
                        <th class="px-4 py-3 text-center">P2P</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($admissions as $entry)
                        @php
                            $badge = match ($entry->status) {
                                'draft' => 'bg-gray-100 text-gray-600',
                                'submitted' => 'bg-yellow-100 text-yellow-700',
                                'verified' => 'bg-green-100 text-green-700',
                                'returned' => 'bg-red-100 text-red-600',
                                'locked' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-500',
                            };
                            $canOverride = in_array($entry->status, ['verified', 'locked']);
                            $canReturn = $entry->status !== 'locked';
                            $rowId = 'row-' . $entry->id;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors" id="{{ $rowId }}">

                            {{-- Date --}}
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $entry->admission_date->format('d M Y') }}
                                @if ($entry->overridden_by)
                                    <span class="block text-xs text-orange-500">⚡ FDE override</span>
                                @endif
                            </td>

                            {{-- School --}}
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800 text-xs leading-tight">{{ $entry->institution?->name }}
                                </p>
                            </td>

                            {{-- Sector --}}
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $entry->institution?->sector?->name ?? '—' }}
                            </td>

                            {{-- Class --}}
                            <td class="px-4 py-3 font-semibold text-gray-700">
                                {{ $entry->classModel?->name }}
                            </td>

                            {{-- Morning --}}
                            <td class="px-4 py-3 text-center text-gray-700">
                                {{ $entry->morningTotal() }}
                                <span class="text-xs text-gray-400 block">
                                    B:{{ $entry->morning_boys }} G:{{ $entry->morning_girls }}
                                </span>
                            </td>

                            {{-- Evening --}}
                            <td class="px-4 py-3 text-center text-gray-700">
                                {{ $entry->eveningTotal() }}
                                <span class="text-xs text-gray-400 block">
                                    B:{{ $entry->evening_boys }} G:{{ $entry->evening_girls }}
                                </span>
                            </td>

                            {{-- OOSC --}}
                            <td class="px-4 py-3 text-center text-purple-600">{{ $entry->ooscTotal() }}</td>

                            {{-- P2P --}}
                            <td class="px-4 py-3 text-center text-teal-600">{{ $entry->p2pTotal() }}</td>

                            {{-- Status --}}
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $badge }}">
                                    {{ $entry->statusLabel() }}
                                </span>
                                @if ($entry->return_reason)
                                    <p class="text-xs text-red-400 mt-1 max-w-[120px] truncate"
                                        title="{{ $entry->return_reason }}">
                                        "{{ $entry->return_reason }}"
                                    </p>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1.5 items-center" x-data="{ showOverride: false, showReturn: false }">

                                    {{-- Override button --}}
                                    @if ($canOverride)
                                        <button @click="showOverride = !showOverride; showReturn = false"
                                            class="text-xs px-3 py-1.5 rounded-lg bg-orange-50 text-orange-700
                                                   hover:bg-orange-100 transition whitespace-nowrap w-full text-center">
                                            🔓 Override
                                        </button>
                                    @endif

                                    {{-- Return button --}}
                                    @if ($canReturn)
                                        <button @click="showReturn = !showReturn; showOverride = false"
                                            class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-700
                                                   hover:bg-red-100 transition whitespace-nowrap w-full text-center">
                                            ↩ Return
                                        </button>
                                    @endif

                                    @if (!$canOverride && !$canReturn)
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif

                                    {{-- Override inline form --}}
                                    <div x-show="showOverride" x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                                        @click.self="showOverride = false">
                                        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
                                            <h4 class="font-bold text-gray-800 mb-1">Override Entry</h4>
                                            <p class="text-xs text-gray-500 mb-4">
                                                {{ $entry->institution?->name }} · {{ $entry->classModel?->name }} ·
                                                {{ $entry->admission_date->format('d M Y') }}
                                            </p>
                                            <div class="bg-orange-50 rounded-xl p-3 mb-4 text-xs text-orange-700">
                                                This will unlock the entry. HOI can then correct and re-submit.
                                            </div>
                                            <form method="POST" action="{{ route('fde.admissions.override', $entry) }}">
                                                @csrf
                                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                                    Reason for Override <span class="text-red-500">*</span>
                                                </label>
                                                <textarea name="override_reason" rows="3" required minlength="10"
                                                    placeholder="e.g. HOI reported incorrect figures…"
                                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                                                       focus:outline-none focus:ring-2 focus:ring-orange-400 mb-4"></textarea>
                                                <div class="flex gap-3">
                                                    <button type="submit"
                                                        class="flex-1 py-2.5 bg-orange-500 text-white rounded-xl text-sm font-semibold hover:bg-orange-600">
                                                        🔓 Confirm Override
                                                    </button>
                                                    <button type="button" @click="showOverride = false"
                                                        class="px-4 py-2.5 text-sm text-gray-500 rounded-xl hover:bg-gray-100">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Return inline form --}}
                                    <div x-show="showReturn" x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                                        @click.self="showReturn = false">
                                        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
                                            <h4 class="font-bold text-gray-800 mb-1">Return Entry to HOI</h4>
                                            <p class="text-xs text-gray-500 mb-4">
                                                {{ $entry->institution?->name }} · {{ $entry->classModel?->name }} ·
                                                {{ $entry->admission_date->format('d M Y') }}
                                            </p>
                                            <div class="bg-red-50 rounded-xl p-3 mb-4 text-xs text-red-700">
                                                HOI will see this entry as Returned with your reason and must correct and
                                                re-submit.
                                            </div>
                                            <form method="POST" action="{{ route('fde.admissions.return', $entry) }}">
                                                @csrf
                                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                                    Reason for Returning <span class="text-red-500">*</span>
                                                </label>
                                                <textarea name="return_reason" rows="3" required minlength="10"
                                                    placeholder="e.g. Figures appear inconsistent with previous days…"
                                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                                                       focus:outline-none focus:ring-2 focus:ring-red-400 mb-4"></textarea>
                                                <div class="flex gap-3">
                                                    <button type="submit"
                                                        class="flex-1 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700">
                                                        ↩ Confirm Return
                                                    </button>
                                                    <button type="button" @click="showReturn = false"
                                                        class="px-4 py-2.5 text-sm text-gray-500 rounded-xl hover:bg-gray-100">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No admission entries found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($admissions->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $admissions->links() }}</div>
        @endif
    </div>

@endsection
