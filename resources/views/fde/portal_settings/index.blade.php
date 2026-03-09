{{-- SAVE AS: resources/views/fde/portal_settings/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Portal Settings')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Portal Settings</h2>
            <p class="text-sm text-gray-500 mt-1">Configure the public admission portal appearance and content</p>
        </div>
        <a href="{{ route('portal.index') }}" target="_blank"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">
            🌐 Preview Portal ↗
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $e)
                <p>{{ $e }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('fde.portal-settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- ── Portal Identity ──────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-700 text-sm mb-4 border-b border-gray-100 pb-2">🌐 Portal Identity</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Portal Title <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="portal_title" required maxlength="120"
                            value="{{ old('portal_title', $settings['portal_title'] ?? '') }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Subtitle</label>
                        <input type="text" name="portal_subtitle" maxlength="200"
                            value="{{ old('portal_subtitle', $settings['portal_subtitle'] ?? '') }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Results Per Page</label>
                        <select name="max_results_per_page"
                            class="w-32 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ([10, 20, 30, 50, 100] as $n)
                                <option value="{{ $n }}"
                                    {{ ($settings['max_results_per_page'] ?? 20) == $n ? 'selected' : '' }}>
                                    {{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── Visibility Toggles ───────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-700 text-sm mb-4 border-b border-gray-100 pb-2">👁 Visibility</h3>
                <div class="space-y-3">
                    @php
                        $toggles = [
                            'portal_enabled' => [
                                'Portal is Live (public can access)',
                                'Disable to take the portal offline immediately',
                            ],
                            'show_vacancy' => ['Show Available Seats', 'Show remaining seat count per school'],
                            'show_oosc' => ['Show OOSC Admissions', 'Show out-of-school children counts'],
                            'show_p2p' => ['Show P2P Admissions', 'Show private-to-public transfer counts'],
                            'show_contact' => ['Show Contact Info', 'Show school phone numbers on portal'],
                            'show_sector_filter' => ['Show Sector Filter', 'Allow filtering schools by sector'],
                            'show_school_map' => ['Show Map Links', 'Show map location links for schools'],
                        ];
                    @endphp
                    @foreach ($toggles as $key => [$label, $hint])
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1"
                                {{ old($key, $settings[$key] ?? false) ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <div>
                                <span
                                    class="text-sm font-semibold text-gray-700 group-hover:text-blue-700 transition">{{ $label }}</span>
                                <p class="text-xs text-gray-400">{{ $hint }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ── Banner ───────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2" x-data="{ removing: false }">
                <div class="flex justify-between items-center border-b border-gray-100 pb-2 mb-4">
                    <h3 class="font-bold text-gray-700 text-sm">📢 Admission Banner</h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="banner_enabled" value="0">
                        <input type="checkbox" name="banner_enabled" value="1"
                            {{ old('banner_enabled', $settings['banner_enabled'] ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-semibold text-gray-600">Show Banner</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    {{-- Current / Upload image --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Banner Image
                            <span class="font-normal text-gray-400 text-xs ml-1">JPG/PNG/WebP · max 4MB · displayed
                                full-width at top of portal</span>
                        </label>

                        @if (!empty($settings['banner_image']))
                            <div class="mb-3 rounded-xl overflow-hidden border border-gray-200 relative">
                                <img src="{{ asset('storage/' . $settings['banner_image']) }}"
                                    class="w-full object-cover max-h-36" alt="Current banner">
                                <div class="absolute top-2 right-2">
                                    <label
                                        class="flex items-center gap-1.5 bg-red-600 text-white text-xs px-3 py-1.5 rounded-lg cursor-pointer hover:bg-red-700 transition">
                                        <input type="checkbox" name="remove_banner" value="1" class="hidden"
                                            x-model="removing" @change="">
                                        🗑 Remove Banner
                                    </label>
                                </div>
                                <div x-show="removing"
                                    class="absolute inset-0 bg-red-900/60 flex items-center justify-center rounded-xl">
                                    <p class="text-white text-sm font-bold">Banner will be removed on save</p>
                                </div>
                            </div>
                        @else
                            <div
                                class="mb-3 rounded-xl border-2 border-dashed border-gray-200 p-6 text-center text-gray-400 text-sm">
                                No banner image uploaded yet
                            </div>
                        @endif

                        <input type="file" name="banner_image" accept="image/jpeg,image/png,image/webp"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-600
                               file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0
                               file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700
                               hover:file:bg-blue-100 focus:outline-none">
                        <p class="text-xs text-gray-400 mt-1.5">Uploading a new image replaces the current one.</p>
                    </div>

                    {{-- Fallback text --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Banner Text
                            <span class="font-normal text-gray-400 text-xs ml-1">shown when no image is uploaded</span>
                        </label>
                        <textarea name="banner_text" rows="5" maxlength="500"
                            placeholder="e.g. Admissions are now open for Academic Year 2026-27. Free quality education from ECE to Class XII. Apply today at your nearest FDE school."
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('banner_text', $settings['banner_text'] ?? '') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">If both image and text are set, the image takes priority.</p>
                    </div>
                </div>
            </div>

            {{-- ── Messages & Notices ───────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
                <h3 class="font-bold text-gray-700 text-sm mb-4 border-b border-gray-100 pb-2">💬 Messages & Notices</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Admission Message
                            <span class="font-normal text-gray-400 text-xs ml-1">shown in info section</span>
                        </label>
                        <textarea name="admission_message" rows="4" maxlength="1000"
                            placeholder="e.g. Admissions are open for 2026-27. Apply now at your nearest FDE school."
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('admission_message', $settings['admission_message'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Portal Notice
                            <span class="font-normal text-gray-400 text-xs ml-1">shown as a top alert banner (leave blank
                                to hide)</span>
                        </label>
                        <textarea name="portal_notice" rows="4" maxlength="500"
                            placeholder="e.g. Portal will be under maintenance on Friday 8pm–10pm."
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('portal_notice', $settings['portal_notice'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-5 flex gap-3">
            <button type="submit"
                class="px-8 py-3 bg-blue-900 text-white text-sm font-bold rounded-xl hover:bg-blue-800 transition shadow-sm">
                💾 Save Portal Settings
            </button>
            <a href="{{ route('fde.dashboard') }}"
                class="px-5 py-3 text-sm text-gray-400 hover:text-gray-600 transition">Cancel</a>
        </div>
    </form>

@endsection
