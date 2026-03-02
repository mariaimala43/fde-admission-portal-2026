@extends('layouts.app')
@section('title', 'Add User')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Add User</h2>
        <p class="text-sm text-gray-500 mt-1">
            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline">Users</a> / Add New
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-2xl" x-data="{ role: '{{ old('role') }}' }">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required />
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="e.g. 0300-1234567" />
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required />
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required />
                </div>
            </div>

            {{-- Role --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" x-model="role" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select Role —</option>
                    <option value="hoi" {{ old('role') == 'hoi' ? 'selected' : '' }}>HoI (Principal)</option>
                    <option value="aeo" {{ old('role') == 'aeo' ? 'selected' : '' }}>AEO</option>
                    <option value="fde_cell" {{ old('role') == 'fde_cell' ? 'selected' : '' }}>FDE Cell</option>
                    <option value="director" {{ old('role') == 'director' ? 'selected' : '' }}>Director</option>
                </select>
                @error('role')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Institution (HoI only) --}}
            <div class="mb-5" x-show="role === 'hoi'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Assign Institution
                    <span class="text-red-500">*</span>
                </label>
                <select name="institution_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select Institution —</option>
                    @foreach ($institutions as $inst)
                        <option value="{{ $inst->id }}" {{ old('institution_id') == $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }}
                        </option>
                    @endforeach
                </select>
                @error('institution_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sectors (AEO only) --}}
            <div class="mb-5" x-show="role === 'aeo'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Assign Sectors
                    <span class="text-gray-400 font-normal">(select one or more)</span>
                </label>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    @foreach ($sectors as $sector)
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="sector_ids[]" value="{{ $sector->id }}"
                                {{ in_array($sector->id, old('sector_ids', [])) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded" />
                            {{ $sector->name }} ({{ $sector->code }})
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
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
