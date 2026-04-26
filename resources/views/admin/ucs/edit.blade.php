@extends('layouts.app')
@section('title', 'Edit Union Council')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Union Council</h2>
        <p class="text-sm text-gray-500 mt-1">
            <a href="{{ route('admin.ucs.index') }}" class="text-blue-600 hover:underline">Union Councils</a>
            / Edit
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-lg">
        <form method="POST" action="{{ route('admin.ucs.update', $unionCouncil) }}">
            @csrf
            @method('PUT')

            {{-- Sector — required --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sector <span class="text-red-500">*</span>
                </label>
                <select name="sector_id" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select Sector —</option>
                    @foreach ($sectors as $sector)
                        <option value="{{ $sector->id }}"
                            {{ old('sector_id', $unionCouncil->sector_id) == $sector->id ? 'selected' : '' }}>
                            {{ $sector->name }} ({{ $sector->code }})
                        </option>
                    @endforeach
                </select>
                @error('sector_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @if (!$unionCouncil->sector_id)
                    <p class="text-amber-600 text-xs mt-1">⚠ This UC has no sector assigned — please select one.</p>
                @endif
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    UC Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $unionCouncil->name) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    UC Code <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code" value="{{ old('code', $unionCouncil->code) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
                @error('code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Institutions linked to this UC (info only) --}}
            @php $schoolCount = $unionCouncil->institutions()->count(); @endphp
            @if ($schoolCount)
                <div class="mb-6 bg-amber-50 border border-amber-100 rounded-lg px-4 py-3 text-sm text-amber-800">
                    ⚠ This UC has <strong>{{ $schoolCount }} linked institution(s)</strong>.
                    Changing its sector will automatically update all those institutions' sector too.
                </div>
            @endif

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Update UC
                </button>
                <a href="{{ route('admin.ucs.index') }}"
                    class="px-6 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>

        </form>
    </div>

@endsection
