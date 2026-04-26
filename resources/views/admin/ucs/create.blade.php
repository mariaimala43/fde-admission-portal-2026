@extends('layouts.app')
@section('title', 'Add Union Council')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Add Union Council</h2>
        <p class="text-sm text-gray-500 mt-1">
            <a href="{{ route('admin.ucs.index') }}" class="text-blue-600 hover:underline">Union Councils</a>
            / Add New
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-lg">
        <form method="POST" action="{{ route('admin.ucs.store') }}">
            @csrf

            {{-- Sector — required, UC cannot exist without one --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sector <span class="text-red-500">*</span>
                </label>
                <select name="sector_id" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select Sector —</option>
                    @foreach ($sectors as $sector)
                        <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>
                            {{ $sector->name }} ({{ $sector->code }})
                        </option>
                    @endforeach
                </select>
                @error('sector_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    UC Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g. UC-26 Sector G-6/1" required />
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    UC Code <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code" value="{{ old('code') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g. UC-26" required />
                <p class="text-xs text-gray-400 mt-1">Must be unique. Will be uppercased automatically.</p>
                @error('code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Save UC
                </button>
                <a href="{{ route('admin.ucs.index') }}"
                    class="px-6 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>

        </form>
    </div>

@endsection
