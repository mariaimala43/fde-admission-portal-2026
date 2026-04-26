@extends('layouts.app')
@section('title', 'Grant Edit Permission')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Grant Edit Permission</h2>
            <p class="text-sm text-gray-500 mt-1">Allow a school to edit post-cutoff admission records</p>
        </div>
        <a href="{{ route('fde.admission-grants.index') }}"
           class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">
            ← Back to Grants
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $error)
                <p>✕ {{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Info Banner --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 mb-6 text-sm text-blue-800">
        <p class="font-semibold mb-1">ℹ️ About Edit Permission Grants</p>
        <ul class="list-disc list-inside space-y-1 text-blue-700">
            <li>The school's HOI will be able to edit daily admission records for the selected date range.</li>
            <li>Permission is <strong>single-use</strong> — it expires after the first successful save.</li>
            <li>The grant also expires automatically at the <strong>Expires At</strong> time you set below.</li>
            <li>All changes made under this grant are <strong>fully audit-logged</strong>.</li>
            <li>You can revoke this grant at any time from the grants list.</li>
        </ul>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

            <form action="{{ route('fde.admission-grants.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Institution --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        School <span class="text-red-500">*</span>
                    </label>
                    <select name="institution_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('institution_id') border-red-400 @enderror">
                        <option value="">— Select a school —</option>
                        @foreach ($institutions as $inst)
                            <option value="{{ $inst->id }}"
                                {{ old('institution_id') == $inst->id ? 'selected' : '' }}>
                                {{ $inst->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('institution_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Date Range --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Allow Editing From <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_from" value="{{ old('date_from') }}" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('date_from') border-red-400 @enderror">
                        @error('date_from')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Allow Editing To <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_to" value="{{ old('date_to') }}" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('date_to') border-red-400 @enderror">
                        @error('date_to')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Expires At --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Grant Expires At <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="expires_at"
                        value="{{ old('expires_at', $defaultExpiry) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('expires_at') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-1">Pakistan time (PKT). Default is +24 hours from now.</p>
                    @error('expires_at')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Reason / Notes <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" rows="3" required maxlength="1000"
                        placeholder="Reason for granting post-lock edit permission…"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('reason') border-red-400 @enderror">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-4 pt-2">
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                        Grant Permission
                    </button>
                    <a href="{{ route('fde.admission-grants.index') }}"
                       class="text-sm text-gray-400 hover:text-gray-600 transition">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

@endsection
