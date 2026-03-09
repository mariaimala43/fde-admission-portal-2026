{{-- SAVE AS: resources/views/hoi/corrections/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Correction Requests')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Correction Requests</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $institution->name }} — request corrections for past verified submissions</p>
    </div>
</div>

@if (session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅ {{ session('success') }}</div>
@endif
@if (session('warning'))
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl px-4 py-3 mb-5 text-sm">⚠️ {{ session('warning') }}</div>
@endif

@if ($submissions->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <p class="text-gray-500 font-semibold mb-1">No verified submissions found</p>
        <p class="text-gray-400 text-sm">Submit and verify daily admissions first before requesting corrections.</p>
        <a href="{{ route('hoi.admissions.daily') }}"
            class="inline-block mt-4 px-5 py-2.5 bg-blue-900 text-white text-sm font-bold rounded-xl hover:bg-blue-800 transition">
            Go to Daily Admissions →
        </a>
    </div>
@else

    <div class="bg-blue-50 border border-blue-100 rounded-xl px-5 py-3 mb-5 text-sm text-blue-700">
        ℹ️ Click <strong>Request Correction</strong> on any row to submit corrected numbers to FDE Cell for approval.
        Corrections are applied only after FDE approves them.
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-center">Morning<br><span class="font-normal normal-case text-gray-400">Boys / Girls</span></th>
                        <th class="px-4 py-3 text-center">OOSC</th>
                        <th class="px-4 py-3 text-center">P2P</th>
                        <th class="px-4 py-3 text-center">Total</th>
                        <th class="px-4 py-3 text-center">Correction Status</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($submissions as $entry)
                        @php
                            $key        = $entry->admission_date->toDateString() . '_' . $entry->class_id;
                            $correction = $corrections[$key] ?? null;
                            $total      = $entry->morning_boys + $entry->morning_girls
                                        + $entry->evening_boys + $entry->evening_girls
                                        + $entry->morning_oosc_boys + $entry->morning_oosc_girls
                                        + $entry->morning_p2p_boys + $entry->morning_p2p_girls
                                        + $entry->evening_oosc_boys + $entry->evening_oosc_girls
                                        + $entry->evening_p2p_boys + $entry->evening_p2p_girls;
                        @endphp
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-700 whitespace-nowrap">
                                {{ $entry->admission_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $entry->classModel?->name ?? "Class {$entry->class_id}" }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ $entry->morning_boys }} / {{ $entry->morning_girls }}
                            </td>
                            <td class="px-4 py-3 text-center text-purple-700">
                                {{ $entry->morning_oosc_boys + $entry->morning_oosc_girls + $entry->evening_oosc_boys + $entry->evening_oosc_girls }}
                            </td>
                            <td class="px-4 py-3 text-center text-orange-700">
                                {{ $entry->morning_p2p_boys + $entry->morning_p2p_girls + $entry->evening_p2p_boys + $entry->evening_p2p_girls }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-blue-900">{{ $total }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($correction)
                                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $correction->statusBadgeClass() }}">
                                        {{ $correction->statusLabel() }}
                                    </span>
                                    @if ($correction->isApproved())
                                        <p class="text-xs text-green-600 mt-1">Applied ✓</p>
                                    @elseif ($correction->isRejected() && $correction->fde_note)
                                        <p class="text-xs text-red-500 mt-1 max-w-xs">{{ $correction->fde_note }}</p>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if (!$correction || $correction->isRejected())
                                    <a href="{{ route('hoi.corrections.create', ['date' => $entry->admission_date->toDateString(), 'class_id' => $entry->class_id]) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition whitespace-nowrap">
                                        ✏️ Request Correction
                                    </a>
                                @elseif ($correction->isPending())
                                    <span class="text-xs text-yellow-600 font-medium">⏳ Awaiting FDE</span>
                                @elseif ($correction->isApproved())
                                    <span class="text-xs text-green-600 font-medium">✅ Applied</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
