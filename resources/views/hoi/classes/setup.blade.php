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
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 text-sm flex items-start gap-2">
            <span class="mt-0.5 shrink-0">⚠️</span>
            <div>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
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
                    <span class="text-sm font-medium text-gray-700"
                        x-text="hasEce ? 'Yes — ECE Center' : 'No ECE Center'"></span>
                </label>
            </div>

            {{-- ECE Classes --}}
            <div x-show="hasEce" x-cloak class="mt-5 border-t border-gray-100 pt-5">
                <p class="text-sm font-medium text-gray-600 mb-3">ECE Classes</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-400 uppercase">Class</th>
                                @if (!$hasEvening)
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-orange-500 uppercase">
                                        Existing Students</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Total
                                        Seats</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-green-600 uppercase">
                                        Available Seats</th>
                                @else
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-blue-600 uppercase">
                                        Morning<br>Existing</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-indigo-600 uppercase">
                                        Evening<br>Existing</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-blue-400 uppercase">
                                        Morning<br>Seats</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-indigo-400 uppercase">
                                        Evening<br>Seats</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-green-600 uppercase">
                                        Morn.<br>Available</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-teal-600 uppercase">
                                        Even.<br>Available</th>
                                @endif
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-400 uppercase">
                                    Sections <span class="normal-case font-normal text-gray-300">(comma-sep, defaults to
                                        A)</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($eceClasses as $eceClass)
                                @php
                                    $conf = $configured[$eceClass->id] ?? null;
                                    $secList = collect($sections[$eceClass->id] ?? [])
                                        ->pluck('name')
                                        ->join(', ');
                                @endphp

                                @if (!$hasEvening)
                                    {{-- ── Unified (non-evening) ECE row ── --}}
                                    <tr class="bg-gray-50 hover:bg-purple-50 transition" x-data="{ existing: {{ $conf?->existing_enrollment ?? 0 }}, total: {{ $conf?->total_seats ?? 0 }} }">
                                        <td class="px-3 py-3">
                                            <span class="text-sm font-semibold text-purple-700">{{ $eceClass->name }}</span>
                                            @if ($eceClass->name === 'ECE-I')
                                                <div class="text-xs text-gray-400 mt-0.5">Age: 3–4 yrs+</div>
                                            @elseif(str_contains($eceClass->name, 'ECE-II') || str_contains($eceClass->name, 'Prep'))
                                                <div class="text-xs text-gray-400 mt-0.5">Age: 4–5 yrs</div>
                                            @endif
                                            <input type="hidden" name="classes[{{ $loop->index + 1000 }}][class_id]"
                                                value="{{ $eceClass->id }}">
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <input type="number" min="0" max="99999"
                                                name="classes[{{ $loop->index + 1000 }}][existing_enrollment]"
                                                x-model.number="existing"
                                                class="w-28 text-center border border-orange-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" />
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <input type="number" min="0" max="9999"
                                                name="classes[{{ $loop->index + 1000 }}][total_seats]"
                                                x-model.number="total"
                                                :class="total > 0 && total < existing ?
                                                    'border-red-400 ring-2 ring-red-200 bg-red-50' : 'border-gray-300'"
                                                class="w-24 text-center rounded-lg px-2 py-1.5 text-sm border focus:outline-none focus:ring-2 focus:ring-blue-400" />
                                            <p x-show="total > 0 && total < existing"
                                                class="text-xs text-red-500 mt-1 font-medium">
                                                Must be ≥ existing (<span x-text="existing"></span>)
                                            </p>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="text-sm font-bold"
                                                :class="(total - existing) > 0 ? 'text-green-600' : 'text-red-500'"
                                                x-text="Math.max(0, total - existing)"></span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <input type="text" placeholder="e.g. A,B"
                                                name="classes[{{ $loop->index + 1000 }}][sections]"
                                                value="{{ $secList }}"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                                        </td>
                                    </tr>
                                @else
                                    {{-- ── Evening split ECE row ── --}}
                                    <tr class="bg-gray-50 hover:bg-purple-50 transition" x-data="{
                                        mExisting: {{ $conf?->morning_existing ?? 0 }},
                                        eExisting: {{ $conf?->evening_existing ?? 0 }},
                                        mSeats: {{ $conf?->morning_seats ?? 0 }},
                                        eSeats: {{ $conf?->evening_seats ?? 0 }}
                                    }">
                                        <td class="px-3 py-3">
                                            <span
                                                class="text-sm font-semibold text-purple-700">{{ $eceClass->name }}</span>
                                            @if ($eceClass->name === 'ECE-I')
                                                <div class="text-xs text-gray-400 mt-0.5">Age: 3–4 yrs+</div>
                                            @elseif(str_contains($eceClass->name, 'ECE-II') || str_contains($eceClass->name, 'Prep'))
                                                <div class="text-xs text-gray-400 mt-0.5">Age: 4–5 yrs</div>
                                            @endif
                                            <input type="hidden" name="classes[{{ $loop->index + 1000 }}][class_id]"
                                                value="{{ $eceClass->id }}">
                                            <input type="hidden" name="classes[{{ $loop->index + 1000 }}][active]"
                                                value="1">
                                        </td>
                                        {{-- Morning Existing --}}
                                        <td class="px-3 py-3 text-center bg-blue-50">
                                            <input type="number" min="0" max="99999"
                                                name="classes[{{ $loop->index + 1000 }}][morning_existing]"
                                                x-model.number="mExisting"
                                                class="w-24 text-center border border-blue-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                                        </td>
                                        {{-- Evening Existing --}}
                                        <td class="px-3 py-3 text-center bg-indigo-50">
                                            <input type="number" min="0" max="99999"
                                                name="classes[{{ $loop->index + 1000 }}][evening_existing]"
                                                x-model.number="eExisting"
                                                class="w-24 text-center border border-indigo-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                                        </td>
                                        {{-- Morning Seats --}}
                                        <td class="px-3 py-3 text-center bg-blue-50">
                                            <input type="number" min="0" max="9999"
                                                name="classes[{{ $loop->index + 1000 }}][morning_seats]"
                                                x-model.number="mSeats"
                                                class="w-24 text-center border border-blue-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                                        </td>
                                        {{-- Evening Seats --}}
                                        <td class="px-3 py-3 text-center bg-indigo-50">
                                            <input type="number" min="0" max="9999"
                                                name="classes[{{ $loop->index + 1000 }}][evening_seats]"
                                                x-model.number="eSeats"
                                                class="w-24 text-center border border-indigo-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                                        </td>
                                        {{-- Morning Available --}}
                                        <td class="px-3 py-3 text-center">
                                            <span class="text-sm font-bold"
                                                :class="(mSeats - mExisting) > 0 ? 'text-green-600' : 'text-red-500'"
                                                x-text="Math.max(0, mSeats - mExisting)"></span>
                                        </td>
                                        {{-- Evening Available --}}
                                        <td class="px-3 py-3 text-center">
                                            <span class="text-sm font-bold"
                                                :class="(eSeats - eExisting) > 0 ? 'text-green-600' : 'text-red-500'"
                                                x-text="Math.max(0, eSeats - eExisting)"></span>
                                        </td>
                                        {{-- Sections --}}
                                        <td class="px-3 py-3">
                                            <input type="text" placeholder="e.g. A,B"
                                                name="classes[{{ $loop->index + 1000 }}][sections]"
                                                value="{{ $secList }}"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Regular Classes ──────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">
                    Classes — {{ $institution->type }}
                </h3>
                <p class="text-sm text-gray-500 mt-0.5">
                    Tick a class to activate it. Enter existing students and total authorized seats; available seats are
                    calculated automatically.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase w-8">On</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Class</th>
                            @if (!$hasEvening)
                                <th class="px-4 py-3 text-center text-xs font-semibold text-orange-500 uppercase">
                                    Existing<br>Students</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Total
                                    Seats<br><span class="text-gray-300 font-normal normal-case">(authorized)</span></th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-green-600 uppercase">
                                    Available<br>Seats</th>
                            @else
                                <th class="px-4 py-3 text-center text-xs font-semibold text-blue-600 uppercase">
                                    Morning<br>Existing</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-indigo-600 uppercase">
                                    Evening<br>Existing</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-blue-400 uppercase">
                                    Morning<br>Seats</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-indigo-400 uppercase">
                                    Evening<br>Seats</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-green-600 uppercase">
                                    Morn.<br>Available</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-teal-600 uppercase">
                                    Even.<br>Available</th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">
                                Sections <span class="normal-case font-normal text-gray-300">(defaults to A)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">

                        @foreach ($classes as $class)
                            @php
                                $conf = $configured[$class->id] ?? null;
                                $secList = collect($sections[$class->id] ?? [])
                                    ->pluck('name')
                                    ->join(', ');
                                $active = $conf !== null && ($conf->is_active ?? true);
                            @endphp

                            @if (!$hasEvening)
                                {{-- ── Morning-only school row ──────────────────────── --}}
                                @php $isMatricTechClass = $institution->has_matric_tech && in_array($class->order, [9, 10]); @endphp
                                <tr x-data="{
                                    active: {{ $active ? 'true' : 'false' }},
                                    existing: {{ $conf?->existing_enrollment ?? 0 }},
                                    total: {{ $conf?->total_seats ?? 0 }}
                                }" :class="active ? 'bg-blue-50' : 'bg-white opacity-60'">

                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" x-model="active"
                                            class="w-5 h-5 text-blue-600 rounded cursor-pointer" />
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        {{ $class->name }}
                                        <input type="hidden" name="classes[{{ $class->id }}][class_id]"
                                            value="{{ $class->id }}">
                                        <input type="hidden" name="classes[{{ $class->id }}][active]"
                                            :value="active ? '1' : '0'">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="number" min="0" max="99999"
                                            name="classes[{{ $class->id }}][existing_enrollment]"
                                            x-model.number="existing" :disabled="!active"
                                            class="w-28 text-center border border-orange-200 rounded-lg px-2 py-1.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-orange-400
                                               disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="number" min="0" max="9999"
                                            name="classes[{{ $class->id }}][total_seats]" x-model.number="total"
                                            :disabled="!active"
                                            :class="active && total < existing && total > 0 ?
                                                'border-red-400 ring-2 ring-red-200 bg-red-50' :
                                                'border-gray-300'"
                                            class="w-24 text-center rounded-lg px-2 py-1.5 text-sm border
                                               focus:outline-none focus:ring-2 focus:ring-blue-400
                                               disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                                        <p x-show="active && total > 0 && total < existing"
                                            class="text-xs text-red-500 mt-1 font-medium">
                                            Must be ≥ existing (<span x-text="existing"></span>)
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold"
                                            :class="active ? ((total - existing) > 0 ? 'text-green-600' : 'text-red-500') :
                                                'text-gray-400'"
                                            x-text="active ? Math.max(0, total - existing) : '—'"></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" placeholder="e.g. A,B,C"
                                            name="classes[{{ $class->id }}][sections]" value="{{ $secList }}"
                                            :disabled="!active"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-400
                                               disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                                    </td>
                                </tr>
                            @else
                                {{-- ── Evening school row (split morning/evening) ──────── --}}
                                @php $isMatricTechClass = $institution->has_matric_tech && in_array($class->order, [9, 10]); @endphp
                                <tr x-data="{
                                    active: {{ $active ? 'true' : 'false' }},
                                    mExisting: {{ $conf?->morning_existing ?? 0 }},
                                    eExisting: {{ $conf?->evening_existing ?? 0 }},
                                    mSeats: {{ $conf?->morning_seats ?? 0 }},
                                    eSeats: {{ $conf?->evening_seats ?? 0 }}
                                }" :class="active ? 'bg-blue-50' : 'bg-white opacity-60'">

                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" x-model="active"
                                            class="w-5 h-5 text-blue-600 rounded cursor-pointer" />
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        {{ $class->name }}
                                        <input type="hidden" name="classes[{{ $class->id }}][class_id]"
                                            value="{{ $class->id }}">
                                        <input type="hidden" name="classes[{{ $class->id }}][active]"
                                            :value="active ? '1' : '0'">
                                    </td>

                                    {{-- Morning Existing --}}
                                    <td class="px-3 py-3 text-center bg-blue-50">
                                        <input type="number" min="0" max="99999"
                                            name="classes[{{ $class->id }}][morning_existing]"
                                            x-model.number="mExisting" :disabled="!active"
                                            class="w-24 text-center border border-blue-200 rounded-lg px-2 py-1.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-400
                                               disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                                    </td>

                                    {{-- Evening Existing --}}
                                    <td class="px-3 py-3 text-center bg-indigo-50">
                                        <input type="number" min="0" max="99999"
                                            name="classes[{{ $class->id }}][evening_existing]"
                                            x-model.number="eExisting" :disabled="!active"
                                            class="w-24 text-center border border-indigo-200 rounded-lg px-2 py-1.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-indigo-400
                                               disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                                    </td>

                                    {{-- Morning Seats --}}
                                    <td class="px-3 py-3 text-center">
                                        <input type="number" min="0" max="9999"
                                            name="classes[{{ $class->id }}][morning_seats]" x-model.number="mSeats"
                                            :disabled="!active"
                                            :class="active && mSeats > 0 && mSeats < mExisting ?
                                                'border-red-400 ring-2 ring-red-200 bg-red-50' :
                                                'border-blue-200'"
                                            class="w-20 text-center rounded-lg px-2 py-1.5 text-sm border
                                               focus:outline-none focus:ring-2 focus:ring-blue-400
                                               disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                                        <p x-show="active && mSeats > 0 && mSeats < mExisting"
                                            class="text-xs text-red-500 mt-0.5">
                                            ≥ <span x-text="mExisting"></span>
                                        </p>
                                    </td>

                                    {{-- Evening Seats --}}
                                    <td class="px-3 py-3 text-center">
                                        <input type="number" min="0" max="9999"
                                            name="classes[{{ $class->id }}][evening_seats]" x-model.number="eSeats"
                                            :disabled="!active"
                                            :class="active && eSeats > 0 && eSeats < eExisting ?
                                                'border-red-400 ring-2 ring-red-200 bg-red-50' :
                                                'border-indigo-200'"
                                            class="w-20 text-center rounded-lg px-2 py-1.5 text-sm border
                                               focus:outline-none focus:ring-2 focus:ring-indigo-400
                                               disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                                        <p x-show="active && eSeats > 0 && eSeats < eExisting"
                                            class="text-xs text-red-500 mt-0.5">
                                            ≥ <span x-text="eExisting"></span>
                                        </p>
                                    </td>

                                    {{-- Morning Available --}}
                                    <td class="px-3 py-3 text-center bg-green-50">
                                        <span class="text-sm font-bold"
                                            :class="active ? (Math.max(0, mSeats - mExisting) > 0 ? 'text-green-600' :
                                                'text-red-500') : 'text-gray-400'"
                                            x-text="active ? Math.max(0, mSeats - mExisting) : '—'"></span>
                                    </td>

                                    {{-- Evening Available --}}
                                    <td class="px-3 py-3 text-center bg-teal-50">
                                        <span class="text-sm font-bold"
                                            :class="active ? (Math.max(0, eSeats - eExisting) > 0 ? 'text-teal-600' :
                                                'text-red-500') : 'text-gray-400'"
                                            x-text="active ? Math.max(0, eSeats - eExisting) : '—'"></span>
                                    </td>

                                    {{-- Sections --}}
                                    <td class="px-4 py-3">
                                        <input type="text" placeholder="e.g. A,B,C"
                                            name="classes[{{ $class->id }}][sections]" value="{{ $secList }}"
                                            :disabled="!active"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-400
                                               disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                        {{-- ── Totals Row ── --}}
                        @php $active = $configured->where('is_active', true); @endphp
                        @if (!$hasEvening)
                            <tr class="bg-blue-900 text-white font-semibold text-sm">
                                <td class="px-4 py-3" colspan="2">Totals</td>
                                <td class="px-4 py-3 text-center text-orange-200">
                                    {{ number_format($active->sum('existing_enrollment')) }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-300">
                                    {{ number_format($active->sum('total_seats')) }}
                                </td>
                                <td class="px-4 py-3 text-center text-green-300">
                                    {{ number_format($active->sum(fn($c) => max(0, $c->total_seats - $c->existing_enrollment))) }}
                                </td>
                                <td class="px-4 py-3 text-blue-300 text-xs font-normal">
                                    Available = Total Seats − Existing Students
                                </td>
                            </tr>
                        @else
                            <tr class="bg-blue-900 text-white font-semibold text-sm">
                                <td class="px-4 py-3" colspan="2">Totals</td>
                                <td class="px-4 py-3 text-center text-blue-200">
                                    {{ number_format($active->sum('morning_existing')) }}
                                </td>
                                <td class="px-4 py-3 text-center text-indigo-200">
                                    {{ number_format($active->sum('evening_existing')) }}
                                </td>
                                <td class="px-4 py-3 text-center text-blue-300">
                                    {{ number_format($active->sum('morning_seats')) }}
                                </td>
                                <td class="px-4 py-3 text-center text-indigo-300">
                                    {{ number_format($active->sum('evening_seats')) }}
                                </td>
                                <td class="px-4 py-3 text-center text-green-300">
                                    {{ number_format($active->sum(fn($c) => max(0, $c->morning_seats - $c->morning_existing))) }}
                                </td>
                                <td class="px-4 py-3 text-center text-teal-300">
                                    {{ number_format($active->sum(fn($c) => max(0, $c->evening_seats - $c->evening_existing))) }}
                                </td>
                                <td class="px-4 py-3 text-blue-300 text-xs font-normal">
                                    Available = Seats − Existing (per shift)
                                </td>
                            </tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Matric Tech Setup (separate table, like daily blade) ──── --}}
        @if ($institution->has_matric_tech)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 bg-teal-800">
                    <div class="flex justify-between items-center">
                        <span class="text-white font-bold text-base">⚙️ Matric Tech Program — Class Setup</span>
                        <span class="text-teal-200 text-xs hidden sm:inline">Only Class 9 &amp; 10 are eligible</span>
                    </div>
                    <p class="text-teal-200 text-xs mt-1">
                        Enter the existing Matric Tech enrollment for Class 9 &amp; 10. This must not exceed the total
                        existing students for that class.
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase w-8">On</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Class</th>
                                @if (!$hasEvening)
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-orange-500 uppercase">
                                        Existing Students<br><span class="normal-case font-normal text-gray-300">(from main
                                            table)</span></th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-teal-700 uppercase">Matric
                                        Tech<br>Existing</th>
                                @else
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-blue-600 uppercase">Morning
                                        Existing<br><span class="normal-case font-normal text-gray-300">(from main
                                            table)</span></th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-indigo-600 uppercase">
                                        Evening Existing<br><span class="normal-case font-normal text-gray-300">(from main
                                            table)</span></th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-teal-700 uppercase">Matric
                                        Tech<br>Existing</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($classes as $class)
                                @php
                                    $isMatricTechClass = in_array($class->order, [9, 10]);
                                    if (!$isMatricTechClass) {
                                        continue;
                                    }
                                    $conf = $configured[$class->id] ?? null;
                                    $active = $conf !== null && ($conf->is_active ?? true);
                                @endphp
                                @if (!$hasEvening)
                                    <tr x-data="{
                                        active: {{ $active ? 'true' : 'false' }},
                                        existing: {{ $conf?->existing_enrollment ?? 0 }},
                                        matricTech: {{ $conf?->matric_tech_existing ?? 0 }}
                                    }" :class="active ? 'bg-teal-50' : 'bg-white opacity-60'">
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-gray-300 text-sm">—</span>
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $class->name }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-sm font-semibold text-orange-700" x-text="existing"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center bg-teal-50">
                                            @if ($active)
                                                <input type="number" min="0" max="99999"
                                                    name="classes[{{ $class->id }}][matric_tech_existing]"
                                                    x-model.number="matricTech"
                                                    :class="matricTech > existing ?
                                                        'border-red-400 ring-2 ring-red-200 bg-red-50' :
                                                        'border-teal-300'"
                                                    class="w-24 text-center rounded-lg px-2 py-1.5 text-sm border focus:outline-none focus:ring-2 focus:ring-teal-400" />
                                                <p x-show="matricTech > existing"
                                                    class="text-xs text-red-500 mt-1 font-medium">
                                                    Max <span x-text="existing"></span>
                                                </p>
                                            @else
                                                <span class="text-gray-300 text-sm">— (class inactive)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @else
                                    <tr x-data="{
                                        active: {{ $active ? 'true' : 'false' }},
                                        mExisting: {{ $conf?->morning_existing ?? 0 }},
                                        eExisting: {{ $conf?->evening_existing ?? 0 }},
                                        matricTech: {{ $conf?->matric_tech_existing ?? 0 }}
                                    }" :class="active ? 'bg-teal-50' : 'bg-white opacity-60'">
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-gray-300 text-sm">—</span>
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $class->name }}</td>
                                        <td class="px-3 py-3 text-center bg-blue-50">
                                            <span class="text-sm font-semibold text-blue-700" x-text="mExisting"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center bg-indigo-50">
                                            <span class="text-sm font-semibold text-indigo-700" x-text="eExisting"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center bg-teal-50">
                                            @if ($active)
                                                <input type="number" min="0" max="99999"
                                                    name="classes[{{ $class->id }}][matric_tech_existing]"
                                                    x-model.number="matricTech"
                                                    :class="matricTech > (mExisting + eExisting) ?
                                                        'border-red-400 ring-2 ring-red-200 bg-red-50' :
                                                        'border-teal-300'"
                                                    class="w-24 text-center rounded-lg px-2 py-1.5 text-sm border focus:outline-none focus:ring-2 focus:ring-teal-400" />
                                                <p x-show="matricTech > (mExisting + eExisting)"
                                                    class="text-xs text-red-500 mt-1 font-medium">
                                                    Max <span x-text="mExisting + eExisting"></span>
                                                </p>
                                            @else
                                                <span class="text-gray-300 text-sm">— (class inactive)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-teal-800 text-white font-semibold text-sm">
                                <td class="px-4 py-3" colspan="2">Totals</td>
                                @if (!$hasEvening)
                                    <td class="px-4 py-3 text-center text-orange-200">
                                        {{ number_format($configured->whereIn('class_id', $classes->whereIn('order', [9, 10])->pluck('id'))->sum('existing_enrollment')) }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-teal-200">
                                        {{ number_format($configured->whereIn('class_id', $classes->whereIn('order', [9, 10])->pluck('id'))->sum('matric_tech_existing')) }}
                                    </td>
                                @else
                                    <td class="px-4 py-3 text-center text-blue-200">
                                        {{ number_format($configured->whereIn('class_id', $classes->whereIn('order', [9, 10])->pluck('id'))->sum('morning_existing')) }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-indigo-200">
                                        {{ number_format($configured->whereIn('class_id', $classes->whereIn('order', [9, 10])->pluck('id'))->sum('evening_existing')) }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-teal-200">
                                        {{ number_format($configured->whereIn('class_id', $classes->whereIn('order', [9, 10])->pluck('id'))->sum('matric_tech_existing')) }}
                                    </td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

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
