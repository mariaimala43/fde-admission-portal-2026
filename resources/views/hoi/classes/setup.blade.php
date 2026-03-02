@extends('layouts.app')
@section('title', 'Class & Section Setup')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Class & Section Setup</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->name }} &mdash; {{ $institution->type }}
            </p>
        </div>
        @if ($institution->classes_configured)
            <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                ✓ Configured
            </span>
        @endif
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('hoi.classes.save') }}" x-data="classSetup()">
        @csrf

        {{-- ── ECE Section ──────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">ECE Center</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Does your school have an Early Childhood Education center?
                    </p>
                </div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="has_ece" value="0" />
                    <input type="checkbox" name="has_ece" value="1" x-model="hasEce"
                        {{ $institution->has_ece ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded" />
                    <span class="text-sm font-medium text-gray-700" x-text="hasEce ? 'Yes — ECE Center' : 'No ECE Center'">
                    </span>
                </label>
            </div>

            {{-- ECE Classes --}}
            <div x-show="hasEce" x-cloak class="mt-5 border-t border-gray-100 pt-5">
                <p class="text-sm font-medium text-gray-600 mb-3">ECE Classes</p>
                <div class="space-y-3">
                    @foreach ($eceClasses as $eceClass)
                        @php
                            $conf = $configured[$eceClass->id] ?? null;
                            $secList = collect($sections[$eceClass->id] ?? [])
                                ->pluck('name')
                                ->join(', ');
                        @endphp
                        <div class="grid grid-cols-12 gap-4 items-center bg-gray-50 rounded-lg px-4 py-3">
                            <div class="col-span-3">
                                <span class="text-sm font-semibold text-purple-700">
                                    {{ $eceClass->name }}
                                </span>
                            </div>
                            <input type="hidden" name="classes[{{ $loop->index + 1000 }}][class_id]"
                                value="{{ $eceClass->id }}" />
                            <div class="col-span-3">
                                <label class="text-xs text-gray-500 mb-1 block">Total Seats</label>
                                <input type="number" min="0" max="9999"
                                    name="classes[{{ $loop->index + 1000 }}][total_seats]"
                                    value="{{ $conf?->total_seats ?? 0 }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="col-span-5">
                                <label class="text-xs text-gray-500 mb-1 block">
                                    Sections <span class="text-gray-400">(comma separated: A,B,C — defaults to A if empty)</span>
                                </label>
                                <input type="text" placeholder="e.g. A,B (leave empty for 1 section 'A')"
                                    name="classes[{{ $loop->index + 1000 }}][sections]" value="{{ $secList }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Regular Classes ──────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-800 mb-1">
                Classes for {{ $institution->type }} School
            </h3>
            <p class="text-sm text-gray-500 mb-5">
                Enter total authorized seats and section names for each class.
            </p>

            {{-- Column Headers --}}
            <div class="grid grid-cols-12 gap-4 px-4 mb-2">
                <div class="col-span-2 text-xs font-semibold text-gray-400 uppercase">Class</div>
                <div class="col-span-1 text-xs font-semibold text-gray-400 uppercase text-center">Active</div>
                <div class="col-span-3 text-xs font-semibold text-gray-400 uppercase">Total Seats</div>
                <div class="col-span-5 text-xs font-semibold text-gray-400 uppercase">
                    Sections <span class="normal-case font-normal">(comma separated — defaults to A if empty)</span>
                </div>
            </div>

            <div class="space-y-2">
                @foreach ($classes as $class)
                    @php
                        $conf = $configured[$class->id] ?? null;
                        $secList = collect($sections[$class->id] ?? [])
                            ->pluck('name')
                            ->join(', ');
                        $active = $conf !== null;
                    @endphp
                    <div class="grid grid-cols-12 gap-4 items-center rounded-lg px-4 py-3 border border-gray-100 hover:bg-gray-50 transition"
                        x-data="{ active: {{ $active ? 'true' : 'false' }} }" :class="active ? 'bg-blue-50 border-blue-200' : 'bg-white'">

                        {{-- Class Name --}}
                        <div class="col-span-2">
                            <span class="text-sm font-semibold text-gray-800">{{ $class->name }}</span>
                        </div>

                        {{-- Active Toggle --}}
                        <div class="col-span-1 flex justify-center">
                            <input type="checkbox" x-model="active" class="w-5 h-5 text-blue-600 rounded cursor-pointer" />
                        </div>

                        {{-- Total Seats --}}
                        <div class="col-span-3">
                            <input type="number" min="0" max="9999"
                                name="classes[{{ $class->id }}][total_seats]" value="{{ $conf?->total_seats ?? 0 }}"
                                :disabled="!active"
                                :class="!active ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : ''"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50" />
                            <input type="hidden" name="classes[{{ $class->id }}][class_id]"
                                value="{{ $class->id }}" />
                        </div>

                        {{-- Sections --}}
                        <div class="col-span-5">
                            <input type="text" placeholder="e.g. A,B,C (leave empty for 1 section 'A')" name="classes[{{ $class->id }}][sections]"
                                value="{{ $secList }}" :disabled="!active"
                                :class="!active ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : ''"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50" />
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex gap-4">
            <button type="submit"
                class="bg-blue-900 text-white px-8 py-3 rounded-lg font-semibold text-sm hover:bg-blue-800 transition">
                Save Classes & Sections
            </button>
            <a href="{{ route('dashboard') }}"
                class="px-8 py-3 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                Back to Dashboard
            </a>
        </div>

    </form>

    <script>
        function classSetup() {
            return {
                hasEce: {{ $institution->has_ece ? 'true' : 'false' }},
            }
        }
    </script>

@endsection
