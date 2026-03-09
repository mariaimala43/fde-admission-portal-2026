{{-- SAVE AS: resources/views/hoi/rooms/index.blade.php --}}
@extends('layouts.app')
@section('title', 'New Construction Rooms — ' . $institution->name)

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">New Construction Rooms</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $institution->name }}</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div
            class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div
            class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $e)
                <p>{{ $e }}</p>
            @endforeach
        </div>
    @endif

    @if (!$construction)
        {{-- No new rooms for this school ─────────────────────────────────── --}}
        <div class="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-xl p-8 text-center">
            <p class="text-4xl mb-3">🏗️</p>
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-2">No New Construction Rooms</h3>
            <p class="text-sm text-blue-700 dark:text-blue-400">
                Your school does not have any newly constructed rooms recorded in the FDE database.<br>
                If you believe this is incorrect, please contact the FDE Cell.
            </p>
        </div>
    @else
        {{-- ── Summary Cards ────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-5 py-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total New Rooms</p>
                <p class="text-3xl font-bold text-blue-900 dark:text-blue-300">{{ $construction->rooms_total }}</p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-5 py-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Allocated</p>
                <p class="text-3xl font-bold text-orange-500">{{ $construction->rooms_allocated }}</p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-{{ $construction->roomsRemaining() > 0 ? 'green' : 'gray' }}-100 dark:border-gray-700 shadow-sm px-5 py-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Remaining</p>
                <p
                    class="text-3xl font-bold {{ $construction->roomsRemaining() > 0 ? 'text-green-600' : 'text-gray-400' }}">
                    {{ $construction->roomsRemaining() }}
                </p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-5 py-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Status</p>
                @php $color = $construction->statusColor(); @endphp
                <span
                    class="inline-block mt-1 text-sm font-semibold px-3 py-1 rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-950 dark:text-{{ $color }}-300">
                    {{ $construction->statusLabel() }}
                </span>
            </div>
        </div>

        {{-- Progress bar --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-5 py-4 mb-6">
            <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                <span>Allocation progress</span>
                <span>{{ $construction->rooms_allocated }} / {{ $construction->rooms_total }} rooms</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                @php $pct = $construction->rooms_total > 0 ? round(($construction->rooms_allocated / $construction->rooms_total) * 100) : 0; @endphp
                <div class="h-3 rounded-full bg-blue-600 transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1.5">
                {{ $pct }}% allocated &mdash; {{ $construction->roomsRemaining() }} room(s) still unassigned
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ── Allocate New Room Form ────────────────────────────────────── --}}
            @if ($construction->roomsRemaining() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-5">
                        Allocate Rooms to a Class
                    </h3>

                    <form method="POST" action="{{ route('hoi.rooms.store') }}" class="space-y-4">
                        @csrf

                        {{-- Class dropdown — only classes configured at this school --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">
                                Class <span class="text-red-500">*</span>
                            </label>
                            <select name="class_id" required
                                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">Select a class…</option>
                                @foreach ($classes as $class)
                                    @if (!isset($allocated[$class->id]))
                                        <option value="{{ $class->id }}"
                                            {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                            @if ($class->is_ece)
                                                (ECE)
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">
                                Only classes already set up for your school are shown.
                            </p>
                        </div>

                        {{-- Number of rooms --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">
                                Rooms to Assign <span class="text-red-500">*</span>
                                <span class="font-normal text-gray-400 ml-1">(max:
                                    {{ $construction->roomsRemaining() }})</span>
                            </label>
                            <input type="number" name="rooms_assigned" min="1"
                                max="{{ $construction->roomsRemaining() }}" value="{{ old('rooms_assigned', 1) }}"
                                required
                                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>

                        {{-- Purpose --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">
                                Room Purpose <span class="text-red-500">*</span>
                            </label>
                            <select name="purpose" required
                                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                @foreach ([
            'classroom' => '🏫 Classroom (teaching)',
            'lab' => '🔬 Computer / Science Lab',
            'library' => '📚 Library',
            'office' => '🗂️ Administrative Office',
            'other' => '📦 Other',
        ] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('purpose', 'classroom') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Optional note --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">
                                Note <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <textarea name="hoi_note" rows="2" maxlength="500" placeholder="Any details about this allocation…"
                                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('hoi_note') }}</textarea>
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                            Submit Allocation for FDE Review
                        </button>
                    </form>
                </div>
            @else
                <div
                    class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-xl p-6 flex items-center gap-4">
                    <span class="text-3xl">✅</span>
                    <div>
                        <p class="font-semibold text-green-800 dark:text-green-200">All rooms allocated</p>
                        <p class="text-sm text-green-700 dark:text-green-400 mt-0.5">
                            All {{ $construction->rooms_total }} rooms have been assigned to classes.
                        </p>
                    </div>
                </div>
            @endif

            {{-- ── Current Allocations ────────────────────────────────────────── --}}
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-3">
                    Current Allocations
                    <span class="text-gray-400 font-normal">({{ $construction->allocations->count() }})</span>
                </h3>

                @if ($construction->allocations->isEmpty())
                    <div
                        class="bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-700 p-6 text-center text-sm text-gray-400">
                        No rooms allocated yet. Use the form to get started.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($construction->allocations->sortBy(fn($a) => $a->classModel?->order) as $alloc)
                            <div
                                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-5 py-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">
                                            {{ $alloc->classModel?->name }}
                                            <span class="text-xs text-gray-400 font-normal ml-1">
                                                {{ ucfirst($alloc->purpose) }}
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $alloc->rooms_assigned }} room(s) assigned
                                        </p>
                                        @if ($alloc->hoi_note)
                                            <p class="text-xs text-gray-400 mt-1 italic">{{ $alloc->hoi_note }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 ml-3">
                                        <span
                                            class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $alloc->statusBadge() }}">
                                            {{ ucfirst($alloc->status) }}
                                        </span>

                                        @if ($alloc->isPending())
                                            {{-- Edit button (inline swap) --}}
                                            <button onclick="toggleEdit({{ $alloc->id }})"
                                                class="text-xs px-2.5 py-1 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                                Edit
                                            </button>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('hoi.rooms.destroy', $alloc) }}"
                                                onsubmit="return confirm('Remove this allocation?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-xs px-2.5 py-1 border border-red-200 text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-950 transition">
                                                    Remove
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                @if ($alloc->review_note)
                                    <div
                                        class="mt-2 p-2 bg-{{ $alloc->isApproved() ? 'green' : 'red' }}-50 dark:bg-opacity-20 rounded text-xs text-gray-600 dark:text-gray-300">
                                        <span class="font-semibold">FDE Note:</span> {{ $alloc->review_note }}
                                    </div>
                                @endif

                                {{-- Inline edit form --}}
                                @if ($alloc->isPending())
                                    <div id="edit-{{ $alloc->id }}"
                                        class="hidden mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                        <form method="POST" action="{{ route('hoi.rooms.update', $alloc) }}"
                                            class="space-y-3">
                                            @csrf @method('PUT')
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Rooms</label>
                                                    <input type="number" name="rooms_assigned"
                                                        value="{{ $alloc->rooms_assigned }}" min="1"
                                                        max="{{ $construction->roomsRemaining() + $alloc->rooms_assigned }}"
                                                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Purpose</label>
                                                    <select name="purpose"
                                                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                                        @foreach (['classroom', 'lab', 'library', 'office', 'other'] as $p)
                                                            <option value="{{ $p }}"
                                                                {{ $alloc->purpose === $p ? 'selected' : '' }}>
                                                                {{ ucfirst($p) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <input type="text" name="hoi_note" value="{{ $alloc->hoi_note }}"
                                                placeholder="Note (optional)"
                                                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                            <div class="flex gap-2">
                                                <button type="submit"
                                                    class="px-4 py-1.5 bg-blue-900 text-white text-xs rounded-lg hover:bg-blue-800">
                                                    Save
                                                </button>
                                                <button type="button" onclick="toggleEdit({{ $alloc->id }})"
                                                    class="px-4 py-1.5 border border-gray-200 text-gray-500 text-xs rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- Source info --}}
        <div
            class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-xl text-xs text-gray-400 border border-gray-100 dark:border-gray-800">
            <span class="font-semibold text-gray-500">Source:</span>
            {{ $construction->source_document ?? 'FDE Construction Programme' }}
        </div>

    @endif

    <script>
        function toggleEdit(id) {
            const el = document.getElementById('edit-' + id);
            el.classList.toggle('hidden');
        }
    </script>

@endsection
