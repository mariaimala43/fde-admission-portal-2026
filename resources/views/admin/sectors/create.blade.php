@extends('layouts.app')
@section('title', 'Add Sector')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Add Sector</h2>
        <p class="text-sm text-gray-500 mt-1">
            <a href="{{ route('admin.sectors.index') }}" class="text-blue-600 hover:underline">Sectors</a>
            / Add New
        </p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $e)
                <p>❌ {{ $e }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-lg">
        <form method="POST" action="{{ route('admin.sectors.store') }}">
            @csrf

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sector Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('name') border-red-400 @enderror"
                    placeholder="e.g. Urban-I" required />
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sector Code <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code" value="{{ old('code') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('code') border-red-400 @enderror"
                    placeholder="e.g. URB-1" required />
                <p class="text-xs text-gray-400 mt-1">Unique short code — auto-uppercased on save.</p>
                @error('code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Save Sector
                </button>
                <a href="{{ route('admin.sectors.index') }}"
                    class="px-6 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>

        </form>
    </div>

@endsection
