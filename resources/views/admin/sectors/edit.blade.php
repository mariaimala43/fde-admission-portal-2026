@extends('layouts.app')
@section('title', 'Edit Sector')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Sector</h2>
        <p class="text-sm text-gray-500 mt-1">
            <a href="{{ route('admin.sectors.index') }}" class="text-blue-600 hover:underline">Sectors</a>
            / Edit
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-lg">
        <form method="POST" action="{{ route('admin.sectors.update', $sector) }}">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Union Council</label>
                <select name="uc_id" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select UC —</option>
                    @foreach ($ucs as $uc)
                        <option value="{{ $uc->id }}" {{ old('uc_id', $sector->uc_id) == $uc->id ? 'selected' : '' }}>
                            {{ $uc->name }} ({{ $uc->code }})
                        </option>
                    @endforeach
                </select>
                @error('uc_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sector Name</label>
                <input type="text" name="name" value="{{ old('name', $sector->name) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sector Code</label>
                <input type="text" name="code" value="{{ old('code', $sector->code) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
                @error('code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Update Sector
                </button>
                <a href="{{ route('admin.sectors.index') }}"
                    class="px-6 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>

        </form>
    </div>

@endsection
