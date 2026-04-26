{{-- SAVE AS: resources/views/admin/users/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Add User')

@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Add User</h2>
        <p class="text-sm text-gray-500 mt-1">
            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline">Users</a> / Add New
        </p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $e)
                <p>❌ {{ $e }}</p>
            @endforeach
        </div>
    @endif

    @php
        $institutionsJson = $institutions->map(fn($i) => [
            'id'        => $i->id,
            'name'      => $i->name . ($i->code ? ' (' . $i->code . ')' : ''),
            'sector_id' => $i->sector_id,
        ])->values()->toJson();
    @endphp

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-2xl"
        x-data="{
            role: '{{ old('role') }}',
            hoiSector: '{{ old('hoi_sector') }}',
            allInstitutions: {{ $institutionsJson }},
            get filteredInstitutions() {
                if (!this.hoiSector) return [];
                return this.allInstitutions.filter(i => i.sector_id == this.hoiSector);
            }
        }">

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            {{-- Name + Phone --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="e.g. Mr. Tariq Mahmood"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500
                               @error('name') border-red-400 @enderror" />
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 0300-1234567"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@fde.edu.pk"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('email') border-red-400 @enderror" />
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required placeholder="Min 8 characters"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500
                               @error('password') border-red-400 @enderror" />
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>

            {{-- Role --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Role <span class="text-red-500">*</span>
                </label>
                <select name="role" x-model="role" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('role') border-red-400 @enderror">
                    <option value="">— Select Role —</option>
                    <option value="hoi" {{ old('role') === 'hoi' ? 'selected' : '' }}>HOI — Head of Institution
                        (Principal)</option>
                    <option value="aeo" {{ old('role') === 'aeo' ? 'selected' : '' }}>AEO — Area Education Officer
                    </option>
                    <option value="fde_cell" {{ old('role') === 'fde_cell' ? 'selected' : '' }}>FDE Cell</option>
                    <option value="director" {{ old('role') === 'director' ? 'selected' : '' }}>Director / DG / Secretary
                    </option>
                </select>
                @error('role')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sector filter — HOI only (step 1) --}}
            <div class="mb-5" x-show="role === 'hoi'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sector <span class="text-red-500">*</span>
                </label>
                <select name="hoi_sector" x-model="hoiSector"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select Sector —</option>
                    @foreach ($sectors as $sector)
                        <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Select a sector to filter institutions.</p>
            </div>

            {{-- Institution — HOI only (step 2, filtered by sector) --}}
            <div class="mb-5" x-show="role === 'hoi' && hoiSector" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Assign Institution <span class="text-red-500">*</span>
                </label>
                <select name="institution_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('institution_id') border-red-400 @enderror">
                    <option value="">— Select Institution —</option>
                    <template x-for="inst in filteredInstitutions" :key="inst.id">
                        <option :value="inst.id"
                            :selected="inst.id == {{ old('institution_id', 'null') }}"
                            x-text="inst.code + ' — ' + inst.name"></option>
                    </template>
                </select>
                @error('institution_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sector — AEO only (single dropdown, not checkboxes) --}}
            <div class="mb-5" x-show="role === 'aeo'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Assign Sector <span class="text-red-500">*</span>
                </label>
                <select name="sector_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('sector_id') border-red-400 @enderror">
                    <option value="">— Select Sector —</option>
                    @foreach ($sectors as $sector)
                        <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}
                            {{ $sector->has_aeo ? 'disabled' : '' }}>
                            {{ $sector->name }}
                            @if ($sector->has_aeo)
                                (AEO already assigned)
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-blue-600 mt-1">ℹ️ Each sector can only have one active AEO. Sectors already assigned
                    are disabled.</p>
                @error('sector_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="flex gap-3 mt-6 pt-6 border-t border-gray-100">
                <button type="submit"
                    class="bg-blue-900 text-white px-8 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Save User
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="px-6 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>

        </form>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

@endsection
