@extends('layouts.app')
@section('title', 'Facility Settings')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Facility Settings</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->name }} &mdash; {{ $institution->type }}
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('hoi.facilities.save') }}" x-data="facilitySettings()">
        @csrf

        <div class="space-y-4 mb-8">

            {{-- Transport --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🚌</span>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">School Transport</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Does your school provide a transport service for students?</p>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="has_transport" value="0" />
                        <input type="checkbox" name="has_transport" value="1" x-model="hasTransport"
                            {{ $institution->has_transport ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded" />
                        <span class="text-sm font-medium text-gray-700"
                            x-text="hasTransport ? 'Yes — Transport Available' : 'No Transport'">
                        </span>
                    </label>
                </div>
            </div>

            {{-- Meal Program --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🍱</span>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Meal Program</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Does your school offer a subsidized meal or nutrition program?</p>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="has_meal_program" value="0" />
                        <input type="checkbox" name="has_meal_program" value="1" x-model="hasMeal"
                            {{ $institution->has_meal_program ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded" />
                        <span class="text-sm font-medium text-gray-700"
                            x-text="hasMeal ? 'Yes — Meal Program Active' : 'No Meal Program'">
                        </span>
                    </label>
                </div>
            </div>

            {{-- Matric Tech --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⚙️</span>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Matric Tech Program</h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Technical subjects group for Classes 9 &amp; 10 (Electrician, Plumbing, Beautician, etc.)
                            </p>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="has_matric_tech" value="0" />
                        <input type="checkbox" name="has_matric_tech" value="1" x-model="hasMatricTech"
                            {{ $institution->has_matric_tech ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded" />
                        <span class="text-sm font-medium text-gray-700"
                            x-text="hasMatricTech ? 'Yes — Matric Tech Active' : 'No Matric Tech'">
                        </span>
                    </label>
                </div>
            </div>

            {{-- Cambridge (read-only, system-set) --}}
            @if ($institution->is_cambridge)
                <div class="bg-blue-50 rounded-xl border border-blue-200 p-5">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🎓</span>
                        <div>
                            <h3 class="text-sm font-semibold text-blue-800">Cambridge Programme</h3>
                            <p class="text-xs text-blue-600 mt-0.5">
                                This school is an FDE Cambridge institution. This status is system-assigned and cannot be changed here.
                            </p>
                        </div>
                        <span class="ml-auto bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">
                            ✓ Active
                        </span>
                    </div>
                </div>
            @endif

            {{-- ECE note --}}
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🌱</span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">ECE Center</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Early Childhood Education setting is managed in
                            <a href="{{ route('hoi.classes.setup') }}" class="text-blue-600 hover:underline">Class &amp; Section Setup</a>.
                        </p>
                    </div>
                    <span class="ml-auto text-xs text-gray-500">
                        Currently: <strong>{{ $institution->has_ece ? 'Yes' : 'No' }}</strong>
                    </span>
                </div>
            </div>

            {{-- Evening Classes --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🌙</span>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Evening Shift</h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Does your school run an evening shift? Enabling this shows the Evening Shift tab in Daily Admissions.
                            </p>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="has_evening_classes" value="0" />
                        <input type="checkbox" name="has_evening_classes" value="1" x-model="hasEvening"
                            {{ $institution->has_evening_classes ? 'checked' : '' }}
                            class="w-5 h-5 text-indigo-600 rounded" />
                        <span class="text-sm font-medium text-gray-700"
                            x-text="hasEvening ? 'Yes — Evening Shift Active' : 'No Evening Shift'">
                        </span>
                    </label>
                </div>
            </div>

        </div>

        {{-- Submit --}}
        <div class="flex gap-4">
            <button type="submit"
                class="bg-blue-900 text-white px-8 py-3 rounded-lg font-semibold text-sm hover:bg-blue-800 transition">
                Save Facility Settings
            </button>
            <a href="{{ route('dashboard') }}"
                class="px-8 py-3 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                Back to Dashboard
            </a>
        </div>

    </form>

    <script>
        function facilitySettings() {
            return {
                hasTransport:  {{ $institution->has_transport    ? 'true' : 'false' }},
                hasMeal:       {{ $institution->has_meal_program  ? 'true' : 'false' }},
                hasMatricTech: {{ $institution->has_matric_tech   ? 'true' : 'false' }},
                hasEvening:    {{ $institution->has_evening_classes ? 'true' : 'false' }},
            }
        }
    </script>

@endsection
