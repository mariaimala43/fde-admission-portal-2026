{{-- SAVE AS: resources/views/fde/referrals/create.blade.php --}}
{{-- Also used for re-referral when $parent is set --}}
@extends('layouts.app')
@section('title', isset($parent) ? 'Re-refer Student' : 'New Referral')

@section('content')

    <div class="max-w-2xl mx-auto">

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                {{ isset($parent) ? '↩️ Re-refer Student' : '+ New Admission Referral' }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ isset($parent) ? 'Referring to a different school after rejection from ' . $parent->institution->name : 'Refer a student to a specific school for admission' }}
            </p>
        </div>

        {{-- Parent referral info banner --}}
        @if (isset($parent))
            <div class="bg-orange-50 border border-orange-200 rounded-xl px-5 py-3 mb-5 text-sm text-orange-800">
                <strong>Original referral {{ $parent->reference_no }}</strong> was rejected by
                {{ $parent->institution->name }}.
                @if ($parent->rejection_reason)
                    <br>Reason: <em>{{ $parent->rejection_reason }}</em>
                @endif
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
                @foreach ($errors->all() as $e)
                    <p>{{ $e }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('fde.referrals.store') }}"
            class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
            @csrf

            @if (isset($parent))
                <input type="hidden" name="parent_referral_id" value="{{ $parent->id }}">
            @endif

            {{-- ── Section 1: School ────────────────────────────────────── --}}
            <div class="px-6 py-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">📍 Referred School</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Sector</label>
                        <select id="sector_select"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">— Select Sector —</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            School <span class="text-red-500">*</span>
                        </label>
                        <select name="institution_id" id="institution_select" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">— Select School —</option>
                            @foreach ($sectors as $sector)
                                @foreach ($sector->institutions as $inst)
                                    <option value="{{ $inst->id }}" data-sector="{{ $sector->id }}"
                                        {{ old('institution_id', $parent?->institution_id) == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        @error('institution_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ── Section 2: Student Info (optional) ─────────────────────── --}}
            <div class="px-6 py-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-1">👤 Student Information
                    <span class="text-gray-400 font-normal text-xs ml-1">(optional — for tracking only)</span>
                </h3>
                <p class="text-xs text-gray-400 mb-4">No student records are stored. Fill what's available.</p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Student Name</label>
                        <input type="text" name="student_name" value="{{ old('student_name', $parent?->student_name) }}"
                            placeholder="e.g. Ahmed Khan"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Father Name</label>
                        <input type="text" name="father_name" value="{{ old('father_name', $parent?->father_name) }}"
                            placeholder="e.g. Mohammad Khan"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                </div>
            </div>

            {{-- ── Section 3: Class + Shift + Gender ──────────────────────── --}}
            <div class="px-6 py-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">📚 Admission Details</h3>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Class</label>
                        <select name="class_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">— Select Class —</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}"
                                    {{ old('class_id', $parent?->class_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Shift <span class="text-red-500">*</span>
                        </label>
                        <select name="shift" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="morning"
                                {{ old('shift', $parent?->shift ?? 'morning') === 'morning' ? 'selected' : '' }}>
                                🌅 Morning
                            </option>
                            <option value="evening" {{ old('shift', $parent?->shift) === 'evening' ? 'selected' : '' }}>
                                🌆 Evening
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Gender</label>
                        <select name="gender"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">— Not Specified —</option>
                            <option value="male" {{ old('gender', $parent?->gender) === 'male' ? 'selected' : '' }}>
                                Male</option>
                            <option value="female" {{ old('gender', $parent?->gender) === 'female' ? 'selected' : '' }}>
                                Female</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── Section 4: Notes ─────────────────────────────────────── --}}
            <div class="px-6 py-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📝 Notes for Principal
                    <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span>
                </h3>
                <textarea name="notes" rows="3" placeholder="Any additional context for the principal…"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                             focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('notes', $parent?->notes) }}</textarea>
            </div>

            {{-- ── Actions ──────────────────────────────────────────────── --}}
            <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex items-center gap-3">
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-semibold
                           hover:bg-blue-800 transition shadow-sm">
                    {{ isset($parent) ? '↩️ Submit Re-referral' : '📤 Send Referral' }}
                </button>
                <a href="{{ route('fde.referrals.index') }}"
                    class="px-4 py-2.5 text-sm text-gray-500 hover:text-gray-700 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    <script>
        // Filter school dropdown by selected sector
        const sectorSelect = document.getElementById('sector_select');
        const instSelect = document.getElementById('institution_select');
        const allOptions = Array.from(instSelect.querySelectorAll('option[data-sector]'));

        sectorSelect.addEventListener('change', function() {
            const sectorId = this.value;
            allOptions.forEach(opt => {
                opt.style.display = (!sectorId || opt.dataset.sector === sectorId) ? '' : 'none';
            });
            instSelect.value = '';
        });
    </script>
@endpush
