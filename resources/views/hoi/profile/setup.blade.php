<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Setup — FDE Admission Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 sm:p-6">

    <div class="bg-white rounded-2xl shadow-lg p-5 sm:p-10 w-full max-w-xl">

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-blue-900">Welcome to FDE Admission Portal</h1>
            <p class="text-gray-500 text-sm mt-2">
                Please set up your school profile to continue.
            </p>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('hoi.profile.save') }}" id="setupForm">
            @csrf

            {{-- Step 1: UC --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Step 1 — Select Union Council
                </label>
                <select name="uc_id" id="uc_id" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select UC —</option>
                    @foreach ($ucs as $uc)
                        <option value="{{ $uc->id }}" {{ old('uc_id') == $uc->id ? 'selected' : '' }}>
                            {{ $uc->name }}
                        </option>
                    @endforeach
                </select>
                @error('uc_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Step 2: Sector --}}
            <div class="mb-5" id="sector_div" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Step 2 — Select Sector
                </label>
                <select name="sector_id" id="sector_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select Sector —</option>
                </select>
                @error('sector_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Step 3: School --}}
            <div class="mb-5" id="institution_div" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Step 3 — Select Your School
                </label>
                <select name="institution_id" id="institution_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select School —</option>
                </select>
                @error('institution_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- School Type (auto-filled, read only) --}}
            <div class="mb-5" id="type_div" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    School Level
                    <span class="text-gray-400 font-normal">(auto-filled)</span>
                </label>
                <input type="text" id="school_type" readonly
                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-600 cursor-not-allowed" />
            </div>

            {{-- Step 4: Gender --}}
            <div class="mb-5" id="gender_div" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Step 4 — School Gender
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <label
                        class="flex items-center justify-center gap-2 border border-gray-300 rounded-lg p-3 cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition">
                        <input type="radio" name="gender" value="boys" class="text-blue-600"
                            {{ old('gender') == 'boys' ? 'checked' : '' }} />
                        <span class="text-sm font-medium">Boys</span>
                    </label>
                    <label
                        class="flex items-center justify-center gap-2 border border-gray-300 rounded-lg p-3 cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition">
                        <input type="radio" name="gender" value="girls" class="text-blue-600"
                            {{ old('gender') == 'girls' ? 'checked' : '' }} />
                        <span class="text-sm font-medium">Girls</span>
                    </label>
                    <label
                        class="flex items-center justify-center gap-2 border border-gray-300 rounded-lg p-3 cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition">
                        <input type="radio" name="gender" value="co_education" class="text-blue-600"
                            {{ old('gender') == 'co_education' ? 'checked' : '' }} />
                        <span class="text-sm font-medium">Co-Edu</span>
                    </label>
                </div>
                @error('gender')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Step 5: Shift --}}
            <div class="mb-8" id="shift_div" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Step 5 — School Shift
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <label
                        class="flex items-center justify-center gap-2 border border-gray-300 rounded-lg p-3 cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition">
                        <input type="radio" name="shift" value="morning" class="text-blue-600"
                            {{ old('shift') == 'morning' ? 'checked' : '' }} />
                        <span class="text-sm font-medium">Morning</span>
                    </label>
                    <label
                        class="flex items-center justify-center gap-2 border border-gray-300 rounded-lg p-3 cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition">
                        <input type="radio" name="shift" value="evening" class="text-blue-600"
                            {{ old('shift') == 'evening' ? 'checked' : '' }} />
                        <span class="text-sm font-medium">Evening</span>
                    </label>
                    <label
                        class="flex items-center justify-center gap-2 border border-gray-300 rounded-lg p-3 cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition">
                        <input type="radio" name="shift" value="both" class="text-blue-600"
                            {{ old('shift') == 'both' ? 'checked' : '' }} />
                        <span class="text-sm font-medium">Both</span>
                    </label>
                </div>
                @error('shift')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div id="submit_div" style="display:none">
                <button type="submit"
                    class="w-full bg-blue-900 text-white py-3 rounded-lg font-semibold text-sm hover:bg-blue-800 transition">
                    Complete Setup & Continue
                </button>
            </div>

        </form>
    </div>

    <script>
        const ajaxSectors = "{{ route('ajax.sectors') }}";
        const ajaxInstitutions = "{{ route('ajax.institutions') }}";

        // UC → load sectors
        document.getElementById('uc_id').addEventListener('change', function() {
            const ucId = this.value;

            // Hide all downstream
            hide(['sector_div', 'institution_div', 'type_div', 'gender_div', 'shift_div', 'submit_div']);
            reset(['sector_id', 'institution_id', 'school_type']);

            if (!ucId) return;

            fetch(ajaxSectors + '?uc_id=' + ucId)
                .then(r => r.json())
                .then(sectors => {
                    const sel = document.getElementById('sector_id');
                    sel.innerHTML = '<option value="">— Select Sector —</option>';
                    sectors.forEach(s => {
                        sel.innerHTML += `<option value="${s.id}">${s.name} (${s.code})</option>`;
                    });
                    show('sector_div');
                });
        });

        // Sector → load institutions
        document.getElementById('sector_id').addEventListener('change', function() {
            const sectorId = this.value;

            hide(['institution_div', 'type_div', 'gender_div', 'shift_div', 'submit_div']);
            reset(['institution_id', 'school_type']);

            if (!sectorId) return;

            fetch(ajaxInstitutions + '?sector_id=' + sectorId)
                .then(r => r.json())
                .then(institutions => {
                    const sel = document.getElementById('institution_id');
                    sel.innerHTML = '<option value="">— Select School —</option>';
                    institutions.forEach(i => {
                        sel.innerHTML +=
                            `<option value="${i.id}" data-type="${i.type}">${i.name}</option>`;
                    });
                    show('institution_div');
                });
        });

        // School → show type + gender + shift
        document.getElementById('institution_id').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];

            hide(['type_div', 'gender_div', 'shift_div', 'submit_div']);

            if (!this.value) return;

            document.getElementById('school_type').value = opt.dataset.type || '';
            show('type_div');
            show('gender_div');
            show('shift_div');
            show('submit_div');
        });

        function show(id) {
            document.getElementById(id).style.display = 'block';
        }

        function hide(ids) {
            ids.forEach(id => document.getElementById(id).style.display = 'none');
        }

        function reset(ids) {
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (el.tagName === 'SELECT') el.value = '';
                if (el.tagName === 'INPUT') el.value = '';
            });
        }
    </script>

</body>

</html>
