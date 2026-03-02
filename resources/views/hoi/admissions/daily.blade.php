@extends('layouts.app')
@section('title', 'Daily Admissions')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Daily Admissions</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->name }} &mdash;
                <span class="font-medium text-blue-900">{{ \Carbon\Carbon::parse($today)->format('d M Y') }}</span>
            </p>
        </div>
        @if ($isPastCutoff)
            <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-2 rounded-full">
                ⏰ Cutoff Passed — Read Only
            </span>
        @else
            <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-2 rounded-full">
                ✓ Open for Entry
            </span>
        @endif
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('hoi.admissions.save') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4">

            {{-- Section Headers --}}
            <div class="grid grid-cols-12 gap-0 border-b border-gray-200">
                <div
                    class="col-span-2 px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase border-r border-gray-200">
                    Class
                </div>
                <div
                    class="col-span-1 px-2 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase text-center border-r border-gray-200">
                    Available
                </div>
                {{-- Regular --}}
                <div
                    class="col-span-2 px-2 py-2 bg-blue-50 text-xs font-semibold text-blue-700 uppercase text-center border-r border-gray-200">
                    Regular New
                </div>
                {{-- OOSC --}}
                <div
                    class="col-span-3 px-2 py-2 bg-purple-50 text-xs font-semibold text-purple-700 uppercase text-center border-r border-gray-200">
                    OOSC Campaign
                </div>
                {{-- P2P --}}
                <div
                    class="col-span-3 px-2 py-2 bg-orange-50 text-xs font-semibold text-orange-700 uppercase text-center border-r border-gray-200">
                    Private → Public
                </div>
                <div class="col-span-1 px-2 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase text-center">
                    Total
                </div>
            </div>

            {{-- Sub Headers --}}
            <div class="grid grid-cols-12 gap-0 border-b border-gray-100 bg-gray-50 text-xs text-gray-400">
                <div class="col-span-2 px-4 py-1 border-r border-gray-200"></div>
                <div class="col-span-1 px-2 py-1 border-r border-gray-200"></div>
                <div class="col-span-1 px-2 py-1 text-center text-blue-500">Boys</div>
                <div class="col-span-1 px-2 py-1 text-center text-pink-500 border-r border-gray-200">Girls</div>
                <div class="col-span-1 px-2 py-1 text-center text-purple-500">Boys</div>
                <div class="col-span-1 px-2 py-1 text-center text-pink-500">Girls</div>
                <div class="col-span-1 px-2 py-1 text-center text-purple-400 border-r border-gray-200">Total</div>
                <div class="col-span-1 px-2 py-1 text-center text-orange-500">Boys</div>
                <div class="col-span-1 px-2 py-1 text-center text-pink-500">Girls</div>
                <div class="col-span-1 px-2 py-1 text-center text-orange-400 border-r border-gray-200">Total</div>
                <div class="col-span-1 px-2 py-1 text-center font-bold text-gray-600"></div>
            </div>

            {{-- Class Rows --}}
            @foreach ($classes as $index => $ic)
                @php
                    $entry = $todayEntries[$ic->class_id] ?? null;
                    $available = $ic->availableSeats();
                @endphp

                <div class="grid grid-cols-12 gap-0 border-b border-gray-50 hover:bg-gray-50 items-center"
                    x-data="{
                        boys: {{ $entry?->boys_count ?? 0 }},
                        girls: {{ $entry?->girls_count ?? 0 }},
                        ooscBoys: {{ $entry?->oosc_boys ?? 0 }},
                        ooscGirls: {{ $entry?->oosc_girls ?? 0 }},
                        p2pBoys: {{ $entry?->p2p_boys ?? 0 }},
                        p2pGirls: {{ $entry?->p2p_girls ?? 0 }},
                    }">

                    <input type="hidden" name="admissions[{{ $index }}][class_id]" value="{{ $ic->class_id }}" />

                    {{-- Class name --}}
                    <div class="col-span-2 px-4 py-3 border-r border-gray-100">
                        <p class="font-semibold text-gray-800 text-sm">{{ $ic->classModel?->name }}</p>
                        @if ($ic->classModel?->is_ece)
                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">ECE</span>
                        @endif
                    </div>

                    {{-- Available --}}
                    <div class="col-span-1 px-2 py-3 text-center border-r border-gray-100">
                        <span class="text-sm font-bold {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $available }}
                        </span>
                    </div>

                    {{-- Regular Boys --}}
                    <div class="col-span-1 px-1 py-2 text-center">
                        @if (!$isPastCutoff)
                            <input type="number" name="admissions[{{ $index }}][boys_count]" x-model.number="boys"
                                min="0" max="9999"
                                class="w-full border border-blue-200 rounded px-1 py-1.5 text-sm text-center
                          focus:outline-none focus:ring-1 focus:ring-blue-400" />
                        @else
                            <span class="text-sm font-bold text-blue-700">{{ $entry?->boys_count ?? 0 }}</span>
                            <input type="hidden" name="admissions[{{ $index }}][boys_count]"
                                value="{{ $entry?->boys_count ?? 0 }}" />
                        @endif
                    </div>

                    {{-- Regular Girls --}}
                    <div class="col-span-1 px-1 py-2 text-center border-r border-gray-100">
                        @if (!$isPastCutoff)
                            <input type="number" name="admissions[{{ $index }}][girls_count]"
                                x-model.number="girls" min="0" max="9999"
                                class="w-full border border-pink-200 rounded px-1 py-1.5 text-sm text-center
                          focus:outline-none focus:ring-1 focus:ring-pink-400" />
                        @else
                            <span class="text-sm font-bold text-pink-700">{{ $entry?->girls_count ?? 0 }}</span>
                            <input type="hidden" name="admissions[{{ $index }}][girls_count]"
                                value="{{ $entry?->girls_count ?? 0 }}" />
                        @endif
                    </div>

                    {{-- OOSC Boys --}}
                    <div class="col-span-1 px-1 py-2 text-center">
                        @if (!$isPastCutoff)
                            <input type="number" name="admissions[{{ $index }}][oosc_boys]"
                                x-model.number="ooscBoys" min="0" max="9999"
                                class="w-full border border-purple-200 rounded px-1 py-1.5 text-sm text-center
                          focus:outline-none focus:ring-1 focus:ring-purple-400" />
                        @else
                            <span class="text-sm font-bold text-purple-700">{{ $entry?->oosc_boys ?? 0 }}</span>
                            <input type="hidden" name="admissions[{{ $index }}][oosc_boys]"
                                value="{{ $entry?->oosc_boys ?? 0 }}" />
                        @endif
                    </div>

                    {{-- OOSC Girls --}}
                    <div class="col-span-1 px-1 py-2 text-center">
                        @if (!$isPastCutoff)
                            <input type="number" name="admissions[{{ $index }}][oosc_girls]"
                                x-model.number="ooscGirls" min="0" max="9999"
                                class="w-full border border-pink-200 rounded px-1 py-1.5 text-sm text-center
                          focus:outline-none focus:ring-1 focus:ring-pink-400" />
                        @else
                            <span class="text-sm font-bold text-pink-700">{{ $entry?->oosc_girls ?? 0 }}</span>
                            <input type="hidden" name="admissions[{{ $index }}][oosc_girls]"
                                value="{{ $entry?->oosc_girls ?? 0 }}" />
                        @endif
                    </div>

                    {{-- OOSC Total --}}
                    <div class="col-span-1 px-1 py-2 text-center border-r border-gray-100">
                        <span class="text-sm font-bold text-purple-600" x-text="ooscBoys + ooscGirls"></span>
                    </div>

                    {{-- P2P Boys --}}
                    <div class="col-span-1 px-1 py-2 text-center">
                        @if (!$isPastCutoff)
                            <input type="number" name="admissions[{{ $index }}][p2p_boys]" x-model.number="p2pBoys"
                                min="0" max="9999"
                                class="w-full border border-orange-200 rounded px-1 py-1.5 text-sm text-center
                          focus:outline-none focus:ring-1 focus:ring-orange-400" />
                        @else
                            <span class="text-sm font-bold text-orange-700">{{ $entry?->p2p_boys ?? 0 }}</span>
                            <input type="hidden" name="admissions[{{ $index }}][p2p_boys]"
                                value="{{ $entry?->p2p_boys ?? 0 }}" />
                        @endif
                    </div>

                    {{-- P2P Girls --}}
                    <div class="col-span-1 px-1 py-2 text-center">
                        @if (!$isPastCutoff)
                            <input type="number" name="admissions[{{ $index }}][p2p_girls]"
                                x-model.number="p2pGirls" min="0" max="9999"
                                class="w-full border border-pink-200 rounded px-1 py-1.5 text-sm text-center
                          focus:outline-none focus:ring-1 focus:ring-pink-400" />
                        @else
                            <span class="text-sm font-bold text-pink-700">{{ $entry?->p2p_girls ?? 0 }}</span>
                            <input type="hidden" name="admissions[{{ $index }}][p2p_girls]"
                                value="{{ $entry?->p2p_girls ?? 0 }}" />
                        @endif
                    </div>

                    {{-- P2P Total --}}
                    <div class="col-span-1 px-1 py-2 text-center border-r border-gray-100">
                        <span class="text-sm font-bold text-orange-600" x-text="p2pBoys + p2pGirls"></span>
                    </div>

                    {{-- Grand Total --}}
                    <div class="col-span-1 px-2 py-2 text-center">
                        <span class="text-sm font-bold text-gray-800"
                            x-text="boys + girls + ooscBoys + ooscGirls + p2pBoys + p2pGirls"></span>
                    </div>

                </div>
            @endforeach

            {{-- Totals Row --}}
            @php
                $tBoys = $todayEntries->sum('boys_count');
                $tGirls = $todayEntries->sum('girls_count');
                $tOoscBoys = $todayEntries->sum('oosc_boys');
                $tOoscGirls = $todayEntries->sum('oosc_girls');
                $tP2pBoys = $todayEntries->sum('p2p_boys');
                $tP2pGirls = $todayEntries->sum('p2p_girls');
                $grandTotal = $tBoys + $tGirls + $tOoscBoys + $tOoscGirls + $tP2pBoys + $tP2pGirls;
            @endphp
            <div
                class="grid grid-cols-12 gap-0 bg-blue-50 border-t-2 border-blue-100
                text-sm font-bold text-center">
                <div class="col-span-2 px-4 py-3 text-left text-gray-700 border-r border-gray-200">TOTAL</div>
                <div class="col-span-1 px-2 py-3 border-r border-gray-200"></div>
                <div class="col-span-1 px-2 py-3 text-blue-700">{{ $tBoys }}</div>
                <div class="col-span-1 px-2 py-3 text-pink-700 border-r border-gray-200">{{ $tGirls }}</div>
                <div class="col-span-1 px-2 py-3 text-purple-700">{{ $tOoscBoys }}</div>
                <div class="col-span-1 px-2 py-3 text-pink-700">{{ $tOoscGirls }}</div>
                <div class="col-span-1 px-2 py-3 text-purple-600 border-r border-gray-200">{{ $tOoscBoys + $tOoscGirls }}
                </div>
                <div class="col-span-1 px-2 py-3 text-orange-700">{{ $tP2pBoys }}</div>
                <div class="col-span-1 px-2 py-3 text-pink-700">{{ $tP2pGirls }}</div>
                <div class="col-span-1 px-2 py-3 text-orange-600 border-r border-gray-200">{{ $tP2pBoys + $tP2pGirls }}
                </div>
                <div class="col-span-1 px-2 py-3 text-gray-900">{{ $grandTotal }}</div>
            </div>

        </div>

        {{-- Save Button --}}
        @if (!$isPastCutoff)
            <div class="flex gap-4">
                <button type="submit"
                    class="bg-blue-900 text-white px-8 py-3 rounded-lg font-semibold text-sm
                   hover:bg-blue-800 transition">
                    Save Admissions
                </button>
                <a href="{{ route('dashboard') }}"
                    class="px-8 py-3 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-700">
                    Back
                </a>
            </div>
        @endif

    </form>

@endsection
