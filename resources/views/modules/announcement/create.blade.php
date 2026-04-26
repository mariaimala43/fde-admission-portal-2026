@extends('layouts.app')

@section('title', 'New Announcement')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <a href="{{ route('announcements.index') }}" class="hover:text-blue-600 transition-colors">Announcements</a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-700 font-medium">New</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">New Announcement</h1>
        <p class="mt-1 text-sm text-gray-500">Create a system announcement visible to selected roles.</p>
    </div>

    <form action="{{ route('announcements.store') }}" method="POST"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf

        {{-- Title --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Title <span class="text-red-500">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Body --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Message <span class="text-red-500">*</span>
            </label>
            <textarea name="body" rows="4" required maxlength="2000"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('body') }}</textarea>
            @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Type + Priority --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Type <span class="text-red-500">*</span>
                </label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @foreach(['info' => 'Info (Blue)', 'warning' => 'Warning (Yellow)', 'success' => 'Success (Green)', 'danger' => 'Danger (Red)'] as $val => $label)
                        <option value="{{ $val }}" {{ old('type', 'info') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Priority <span class="text-red-500">*</span>
                </label>
                <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @foreach(['normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $val => $label)
                        <option value="{{ $val }}" {{ old('priority', 'normal') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Target Roles --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Target Roles</label>
            <p class="text-xs text-gray-500 mb-2">Leave all unchecked to show to every role.</p>
            <div class="flex flex-wrap gap-4">
                @foreach(['hoi' => 'HOI (Principals)', 'aeo' => 'AEO', 'fde_cell' => 'FDE Cell', 'director' => 'Director'] as $role => $label)
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="target_roles[]" value="{{ $role }}"
                               {{ in_array($role, old('target_roles', [])) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Publish + Expiry dates --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Publish At</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">Blank = publish immediately.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">Blank = never expires.</p>
            </div>
        </div>

        {{-- Flags --}}
        <div class="flex items-center gap-6 pt-1">
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', '1') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600">
                Active (visible to users)
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" name="is_pinned" value="1"
                       {{ old('is_pinned') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-yellow-500">
                📌 Pin to top
            </label>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
            <a href="{{ route('announcements.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 transition-colors">← Cancel</a>
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                Create Announcement
            </button>
        </div>
    </form>

</div>
@endsection
