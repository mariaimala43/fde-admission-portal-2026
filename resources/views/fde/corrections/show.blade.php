{{-- SAVE AS: resources/views/fde/corrections/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Review Correction Request')

@section('content')

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Review Correction Request</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $correction->institution->name }} —
                {{ $correction->classModel?->name }} —
                {{ $correction->admission_date->format('d M Y') }}
            </p>
        </div>
        <a href="{{ route('fde.corrections.index') }}"
            class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg px-4 py-2 transition">
            ← Back
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left: Details + Comparison ──────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Meta info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-semibold mb-0.5">School</p>
                        <p class="font-semibold text-gray-800">{{ $correction->institution->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-semibold mb-0.5">Class</p>
                        <p class="font-semibold text-gray-800">{{ $correction->classModel?->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-semibold mb-0.5">Admission Date</p>
                        <p class="font-semibold text-gray-800">{{ $correction->admission_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-semibold mb-0.5">Requested By</p>
                        <p class="font-semibold text-gray-800">{{ $correction->requestedBy?->name }}</p>
                        <p class="text-xs text-gray-400">{{ $correction->created_at->format('d M Y, g:i A') }}</p>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <p class="text-xs text-gray-400 uppercase font-semibold mb-0.5">Reason</p>
                        <p class="text-gray-700 bg-yellow-50 border border-yellow-100 rounded-lg px-3 py-2">
                            {{ $correction->reason }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Old vs New comparison table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                    <h3 class="font-bold text-gray-700 text-sm">Old vs New Values</h3>
                </div>
                <p class="text-xs text-gray-400 px-5 pt-2 sm:hidden">Scroll horizontally to see all columns</p>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-semibold uppercase">
                                <th class="px-4 py-2.5 text-left text-gray-500 bg-gray-50">Field</th>
                                <th class="px-4 py-2.5 text-center bg-orange-50 text-orange-600">Current (Old)</th>
                                <th class="px-4 py-2.5 text-center bg-blue-50 text-blue-600">Requested (New)</th>
                                <th class="px-4 py-2.5 text-center bg-gray-50 text-gray-500">Diff</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $fields = [
                                    '🌅 Morning Regular — Boys' => ['morning_boys', 'text-blue-700'],
                                    '🌅 Morning Regular — Girls' => ['morning_girls', 'text-pink-700'],
                                    '🌅 Morning OOSC — Boys' => ['morning_oosc_boys', 'text-purple-700'],
                                    '🌅 Morning OOSC — Girls' => ['morning_oosc_girls', 'text-purple-600'],
                                    '🌅 Morning P2G — Boys' => ['morning_p2p_boys', 'text-orange-700'],
                                    '🌅 Morning P2G — Girls' => ['morning_p2p_girls', 'text-orange-600'],
                                ];
                                if ($hasEvening) {
                                    $fields += [
                                        '🌆 Evening Regular — Boys' => ['evening_boys', 'text-indigo-700'],
                                        '🌆 Evening Regular — Girls' => ['evening_girls', 'text-pink-700'],
                                        '🌆 Evening OOSC — Boys' => ['evening_oosc_boys', 'text-purple-700'],
                                        '🌆 Evening OOSC — Girls' => ['evening_oosc_girls', 'text-purple-600'],
                                        '🌆 Evening P2G — Boys' => ['evening_p2p_boys', 'text-orange-700'],
                                        '🌆 Evening P2G — Girls' => ['evening_p2p_girls', 'text-orange-600'],
                                    ];
                                }
                            @endphp
                            @foreach ($fields as $label => [$key, $color])
                                @php
                                    $old = (int) $correction->{'old_' . $key};
                                    $new = (int) $correction->{'new_' . $key};
                                    $diff = $new - $old;
                                    $changed = $diff !== 0;
                                @endphp
                                <tr class="border-b border-gray-50 {{ $changed ? 'bg-yellow-50' : '' }}">
                                    <td class="px-4 py-2 text-gray-700 {{ $changed ? 'font-semibold' : '' }}">
                                        {{ $label }}
                                        @if ($changed)
                                            <span class="ml-1 text-xs text-yellow-600">changed</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center bg-orange-50 text-orange-700 font-semibold">
                                        {{ $old }}</td>
                                    <td class="px-4 py-2 text-center bg-blue-50 {{ $color }} font-semibold">
                                        {{ $new }}</td>
                                    <td class="px-4 py-2 text-center font-bold">
                                        @if ($diff > 0)
                                            <span class="text-green-600">+{{ $diff }}</span>
                                        @elseif ($diff < 0)
                                            <span class="text-red-600">{{ $diff }}</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Totals row --}}
                            <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                                <td class="px-4 py-3 text-gray-700">TOTAL</td>
                                <td class="px-4 py-3 text-center bg-orange-100 text-orange-700">
                                    {{ $correction->oldTotal() }}</td>
                                <td class="px-4 py-3 text-center bg-blue-100 text-blue-700">{{ $correction->newTotal() }}
                                </td>
                                <td class="px-4 py-3 text-center text-lg">
                                    @php $net = $correction->netDiff(); @endphp
                                    <span
                                        class="{{ $net > 0 ? 'text-green-600' : ($net < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                        {{ $net > 0 ? '+' : '' }}{{ $net }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: Action Panel ─────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Status badge --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                <p class="text-xs text-gray-400 uppercase font-semibold mb-2">Status</p>
                <span class="text-sm px-4 py-1.5 rounded-full font-semibold {{ $correction->statusBadgeClass() }}">
                    {{ $correction->statusLabel() }}
                </span>
                @if ($correction->reviewed_at)
                    <p class="text-xs text-gray-400 mt-2">{{ $correction->reviewed_at->format('d M Y, g:i A') }}</p>
                    <p class="text-xs text-gray-500">by {{ $correction->reviewedBy?->name }}</p>
                @endif
                @if ($correction->fde_note)
                    <p class="text-xs text-gray-600 bg-gray-50 rounded-lg p-2 mt-3 text-left">
                        <strong>FDE Note:</strong> {{ $correction->fde_note }}
                    </p>
                @endif
            </div>

            @if ($correction->isPending())
                {{-- Approve --}}
                <div class="bg-white rounded-xl shadow-sm border border-green-100 p-5">
                    <h4 class="font-bold text-green-700 text-sm mb-3">✅ Approve Correction</h4>
                    <form method="POST" action="{{ route('fde.corrections.approve', $correction) }}">
                        @csrf
                        <textarea name="fde_note" rows="2" placeholder="Optional note to HOI..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 mb-3"></textarea>
                        <button type="submit"
                            onclick="return confirm('Approve this correction? The record will be updated and enrollment adjusted by {{ $correction->netDiff() >= 0 ? '+' : '' }}{{ $correction->netDiff() }}.')"
                            class="w-full px-4 py-2.5 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 transition">
                            ✅ Approve & Apply
                        </button>
                    </form>
                </div>

                {{-- Reject --}}
                <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
                    <h4 class="font-bold text-red-600 text-sm mb-3">✕ Reject Correction</h4>
                    <form method="POST" action="{{ route('fde.corrections.reject', $correction) }}">
                        @csrf
                        <textarea name="fde_note" rows="2" required placeholder="Reason for rejection (required)..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 mb-3"></textarea>
                        <button type="submit"
                            class="w-full px-4 py-2.5 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 transition">
                            ✕ Reject Request
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>

@endsection
