{{-- SAVE AS: resources/views/fde/enrollment/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Enrollment Override — ' . $institution->name)

@section('content')

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-xs text-gray-400 mb-1">
                <a href="{{ route('fde.schools.index') }}" class="hover:underline">All Schools</a>
                → <a href="{{ route('fde.schools.show', $institution) }}" class="hover:underline">{{ $institution->name }}</a>
                → Enrollment Override
            </p>
            <h2 class="text-2xl font-bold text-gray-800">Enrollment Override</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $institution->name }} · {{ $institution->sector?->name }}</p>
        </div>
        <a href="{{ route('fde.schools.show', $institution) }}"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
            ← Back to School
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

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-blue-900">{{ number_format($totalSeats) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Intake Capacity</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-orange-600">{{ number_format($totalEnrolled) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Existing Enrollment</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-blue-600">{{ number_format($totalAdmitted) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Verified Admissions</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold {{ $totalAvail > 0 ? 'text-green-600' : 'text-red-500' }}">
                {{ number_format($totalAvail) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Available Seats</p>
        </div>
    </div>

    {{-- Enrollment Status Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-700 text-sm">Current Enrollment Status by Class</h3>
            <div class="flex gap-2">
                @foreach ($classes->groupBy('enrollment_status') as $status => $group)
                    @php
                        $badge = match ($status) {
                            'draft' => 'bg-gray-100 text-gray-600',
                            'submitted' => 'bg-yellow-100 text-yellow-700',
                            'verified' => 'bg-green-100 text-green-700',
                            'locked' => 'bg-blue-100 text-blue-800',
                            'returned' => 'bg-red-100 text-red-600',
                            default => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $badge }}">
                        {{ ucfirst($status) }}: {{ $group->count() }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead
                    class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-center">Sections</th>
                        <th class="px-4 py-3 text-center">Intake Capacity</th>
                        <th class="px-4 py-3 text-center">Existing Enrollment</th>
                        <th class="px-4 py-3 text-center">Verified Admissions</th>
                        <th class="px-4 py-3 text-center">Available Seats</th>
                        <th class="px-4 py-3 text-center">Enrollment Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($classes as $ic)
                        @php
                            $admitted = $cumulativeAdmissions[$ic->class_id] ?? 0;
                            $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                            $badge = match ($ic->enrollment_status) {
                                'draft' => 'bg-gray-100 text-gray-600',
                                'submitted' => 'bg-yellow-100 text-yellow-700',
                                'verified' => 'bg-green-100 text-green-700',
                                'locked' => 'bg-blue-100 text-blue-800',
                                'returned' => 'bg-red-100 text-red-600',
                                default => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $ic->classModel?->name }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $sections[$ic->class_id] ?? 1 }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-blue-900">
                                {{ number_format($ic->total_seats) }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-orange-600">
                                {{ number_format($ic->existing_enrollment) }}</td>
                            <td class="px-4 py-3 text-center text-blue-700">{{ number_format($admitted) }}</td>
                            <td
                                class="px-4 py-3 text-center font-semibold {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ number_format($available) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $badge }}">
                                    {{ $ic->enrollmentStatusLabel() }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── OPTION A: Unlock for HOI ───────────────────────────────────── --}}
    @if ($anyLocked)
        <div class="bg-white rounded-2xl border border-orange-200 shadow-sm overflow-hidden mb-5" x-data="{ open: false }">
            <button @click="open = !open"
                class="w-full px-5 py-4 flex justify-between items-center text-left hover:bg-orange-50 transition">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🔓</span>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">Option A — Unlock Enrollment for HOI</p>
                        <p class="text-xs text-gray-500">Reset all classes back to Draft so the HOI can correct and
                            re-submit.</p>
                    </div>
                </div>
                <span class="text-gray-400 text-xs" x-text="open ? '▲ Hide' : '▼ Show'"></span>
            </button>

            <div x-show="open" x-transition class="px-5 pb-5 border-t border-orange-100">
                <div class="bg-orange-50 rounded-xl p-4 mt-4 mb-4 text-xs text-orange-800">
                    ⚠️ This will reset <strong>all classes</strong> to Draft status. The HOI will be able to edit and
                    re-submit. All existing enrollment figures are preserved.
                </div>

                <form method="POST" action="{{ route('fde.enrollment.unlock', $institution) }}"
                    onsubmit="return confirm('Unlock enrollment for {{ $institution->name }}? HOI will be able to re-edit.')">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                            Reason for Unlocking <span class="text-red-500">*</span>
                        </label>
                        <textarea name="override_reason" rows="3" required minlength="10"
                            placeholder="e.g. HOI reported incorrect class figures — unlocked for correction"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                           focus:outline-none focus:ring-2 focus:ring-orange-400
                           @error('override_reason') border-red-400 @enderror">{{ old('override_reason') }}</textarea>
                        @error('override_reason')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="px-6 py-2.5 bg-orange-500 text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition">
                        🔓 Unlock for HOI
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- ── OPTION B: Direct Edit by FDE ──────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-blue-200 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open"
            class="w-full px-5 py-4 flex justify-between items-center text-left hover:bg-blue-50 transition">
            <div class="flex items-center gap-3">
                <span class="text-xl">✏️</span>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">Option B — Edit Enrollment Directly</p>
                    <p class="text-xs text-gray-500">FDE Cell edits figures directly. Status set to Verified immediately —
                        no HOI re-submission needed.</p>
                </div>
            </div>
            <span class="text-gray-400 text-xs" x-text="open ? '▲ Hide' : '▼ Show'"></span>
        </button>

        <div x-show="open" x-transition class="px-5 pb-5 border-t border-blue-100">
            <div class="bg-blue-50 rounded-xl p-4 mt-4 mb-4 text-xs text-blue-800">
                ℹ️ Changes take effect immediately and are marked as FDE override. The HOI will not receive any
                re-submission request.
            </div>

            <form method="POST" action="{{ route('fde.enrollment.update', $institution) }}"
                onsubmit="return confirm('Save these enrollment figures directly for {{ $institution->name }}?')">
                @csrf
                @method('PUT')

                {{-- Reason --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Reason for Direct Edit <span class="text-red-500">*</span>
                    </label>
                    <textarea name="override_reason" rows="2" required minlength="10"
                        placeholder="e.g. Correcting data entry error on behalf of HOI per phone verification"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                           focus:outline-none focus:ring-2 focus:ring-blue-400
                           @error('override_reason') border-red-400 @enderror">{{ old('override_reason') }}</textarea>
                    @error('override_reason')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Enrollment table --}}
                <div class="overflow-x-auto rounded-xl border border-gray-100 mb-5">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left">Class</th>
                                <th class="px-4 py-3 text-center">Intake Capacity</th>
                                <th class="px-4 py-3 text-center">Current Enrollment</th>
                                <th class="px-4 py-3 text-center text-blue-700">New Enrollment <span
                                        class="normal-case font-normal">(editable)</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($classes as $index => $ic)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        {{ $ic->classModel?->name }}
                                        <input type="hidden" name="enrollment[{{ $index }}][class_id]"
                                            value="{{ $ic->class_id }}">
                                    </td>
                                    <td class="px-4 py-3 text-center text-blue-900 font-semibold">
                                        {{ number_format($ic->total_seats) }}</td>
                                    <td class="px-4 py-3 text-center text-orange-600 font-semibold">
                                        {{ number_format($ic->existing_enrollment) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="number" name="enrollment[{{ $index }}][existing]"
                                            value="{{ old("enrollment.{$index}.existing", $ic->existing_enrollment) }}"
                                            min="0" max="{{ $ic->total_seats }}"
                                            class="w-24 border-2 border-blue-300 rounded-lg px-3 py-1.5 text-sm text-center
                                                  font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit"
                    class="px-6 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">
                    💾 Save Enrollment Override
                </button>
            </form>
        </div>
    </div>

@endsection
