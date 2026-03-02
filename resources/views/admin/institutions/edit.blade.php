@extends('layouts.app')
@section('title', 'Edit Institution')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Institution</h2>
        <p class="text-sm text-gray-500 mt-1">
            <a href="{{ route('admin.institutions.index') }}" class="text-blue-600 hover:underline">Institutions</a>
            / Edit
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.institutions.update', $institution) }}">
            @csrf
            @method('PUT')

            {{-- Basic Info --}}
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                Basic Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Institution Name</label>
                    <input type="text" name="name" value="{{ old('name', $institution->name) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required />
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Code</label>
                    <input type="text" name="code" value="{{ old('code', $institution->code) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Location --}}
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 mt-6">
                Location
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Union Council</label>
                    <select name="uc_id" id="uc_id" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Select UC —</option>
                        @foreach ($ucs as $uc)
                            <option value="{{ $uc->id }}"
                                {{ old('uc_id', $institution->uc_id) == $uc->id ? 'selected' : '' }}>
                                {{ $uc->name }} ({{ $uc->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('uc_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sector</label>
                    <select name="sector_id" id="sector_id" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Select Sector —</option>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector->id }}" data-uc="{{ $sector->uc_id }}"
                                {{ old('sector_id', $institution->sector_id) == $sector->id ? 'selected' : '' }}>
                                {{ $sector->name }} ({{ $sector->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('sector_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="address" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address', $institution->address) }}</textarea>
            </div>

            {{-- School Details --}}
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 mt-6">
                School Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Type</label>
                    <select name="type" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach (['I-V', 'I-VIII', 'I-X', 'I-XII', 'VI-VIII', 'VI-X', 'VI-XII', 'Model College'] as $type)
                            <option value="{{ $type }}"
                                {{ old('type', $institution->type) == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <select name="gender" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="boys" {{ old('gender', $institution->gender) == 'boys' ? 'selected' : '' }}>Boys
                        </option>
                        <option value="girls" {{ old('gender', $institution->gender) == 'girls' ? 'selected' : '' }}>Girls
                        </option>
                        <option value="co_education"
                            {{ old('gender', $institution->gender) == 'co_education' ? 'selected' : '' }}>Co-Education
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                    <select name="shift" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="morning" {{ old('shift', $institution->shift) == 'morning' ? 'selected' : '' }}>
                            Morning</option>
                        <option value="evening" {{ old('shift', $institution->shift) == 'evening' ? 'selected' : '' }}>
                            Evening</option>
                        <option value="both" {{ old('shift', $institution->shift) == 'both' ? 'selected' : '' }}>Both
                        </option>
                    </select>
                </div>
            </div>

            {{-- Facilities --}}
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 mt-6">
                Facilities
            </h3>

            {{-- Cambridge notice --}}
            <div class="bg-purple-50 border border-purple-200 rounded-lg px-4 py-3 mb-4 text-sm text-purple-800">
                <strong>Cambridge Classes:</strong>
                {{ $institution->is_cambridge ? 'YES — System locked (eligible institution)' : 'NO — Not eligible' }}
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                @foreach ([
            'has_matric_tech' => 'Matric Tech',
            'has_transport' => 'Transport',
            'has_meal_program' => 'Meal Program',
            'has_evening_classes' => 'Evening Classes',
        ] as $field => $label)
                    <label
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="{{ $field }}" value="1"
                            {{ old($field, $institution->$field) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 rounded" />
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Update Institution
                </button>
                <a href="{{ route('admin.institutions.index') }}"
                    class="px-6 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>

        </form>
    </div>

    <script>
        document.getElementById('uc_id').addEventListener('change', function() {
            const ucId = this.value;
            const selector = document.getElementById('sector_id');
            const options = selector.querySelectorAll('option');

            options.forEach(function(opt) {
                if (!opt.value) return;
                opt.style.display = (!ucId || opt.dataset.uc === ucId) ? '' : 'none';
            });

            selector.value = '';
        });
    </script>

@endsection
