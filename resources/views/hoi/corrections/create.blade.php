{{-- SAVE AS: resources/views/hoi/corrections/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Request Correction')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Request Correction</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $entry->classModel?->name }} —
                {{ $entry->admission_date->format('l, d M Y') }}
            </p>
        </div>
        <a href="{{ route('hoi.corrections.index') }}"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">
            ← Back
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <div class="bg-yellow-50 border border-yellow-100 rounded-lg px-4 py-3 mb-6 text-sm text-yellow-700">
            ⚠️ Edit only the fields that are <strong>incorrect</strong>. All other fields are pre-filled with the current
            values.
            Your request will be sent to <strong>FDE Cell</strong> for approval.
        </div>

        <form method="POST" action="{{ route('hoi.corrections.store') }}">
            @csrf
            <input type="hidden" name="admission_date" value="{{ $entry->admission_date->toDateString() }}">
            <input type="hidden" name="class_id" value="{{ $entry->class_id }}">

            {{-- Comparison: existing vs corrected side by side ──────────── --}}
            <div class="overflow-x-auto mb-6">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-4 py-2.5 text-left border border-gray-100">Field</th>
                            <th class="px-4 py-2.5 text-center border border-gray-100 bg-orange-50 text-orange-600">Current
                                Value</th>
                            <th class="px-4 py-2.5 text-center border border-gray-100 bg-blue-50 text-blue-600">Corrected
                                Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $fields = [
                                ['label' => '🌅 Morning Regular — Boys', 'key' => 'morning_boys', 'color' => 'blue'],
                                ['label' => '🌅 Morning Regular — Girls', 'key' => 'morning_girls', 'color' => 'pink'],
                                [
                                    'label' => '🌅 Morning OOSC — Boys',
                                    'key' => 'morning_oosc_boys',
                                    'color' => 'purple',
                                ],
                                [
                                    'label' => '🌅 Morning OOSC — Girls',
                                    'key' => 'morning_oosc_girls',
                                    'color' => 'purple',
                                ],
                                ['label' => '🌅 Morning P2G — Boys', 'key' => 'morning_p2p_boys', 'color' => 'orange'],
                                [
                                    'label' => '🌅 Morning P2G — Girls',
                                    'key' => 'morning_p2p_girls',
                                    'color' => 'orange',
                                ],
                            ];
                            if ($hasEvening) {
                                $fields = array_merge($fields, [
                                    [
                                        'label' => '🌆 Evening Regular — Boys',
                                        'key' => 'evening_boys',
                                        'color' => 'indigo',
                                    ],
                                    [
                                        'label' => '🌆 Evening Regular — Girls',
                                        'key' => 'evening_girls',
                                        'color' => 'pink',
                                    ],
                                    [
                                        'label' => '🌆 Evening OOSC — Boys',
                                        'key' => 'evening_oosc_boys',
                                        'color' => 'purple',
                                    ],
                                    [
                                        'label' => '🌆 Evening OOSC — Girls',
                                        'key' => 'evening_oosc_girls',
                                        'color' => 'purple',
                                    ],
                                    [
                                        'label' => '🌆 Evening P2G — Boys',
                                        'key' => 'evening_p2p_boys',
                                        'color' => 'orange',
                                    ],
                                    [
                                        'label' => '🌆 Evening P2G — Girls',
                                        'key' => 'evening_p2p_girls',
                                        'color' => 'orange',
                                    ],
                                ]);
                            }
                        @endphp

                        @foreach ($fields as $field)
                            @php $oldVal = (int) $entry->{$field['key']}; @endphp
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-gray-700 border border-gray-100 font-medium">
                                    {{ $field['label'] }}
                                </td>
                                <td class="px-4 py-2.5 text-center border border-gray-100 bg-orange-50">
                                    <span class="font-bold text-orange-700">{{ $oldVal }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-center border border-gray-100 bg-blue-50">
                                    <input type="number" name="new_{{ $field['key'] }}"
                                        value="{{ old('new_' . $field['key'], $oldVal) }}" min="0" max="9999"
                                        class="w-20 border border-blue-200 rounded-lg px-2 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-400">
                                </td>
                            </tr>
                        @endforeach

                        {{-- Totals row --}}
                        @php
                            $oldTotal =
                                $entry->morning_boys +
                                $entry->morning_girls +
                                $entry->evening_boys +
                                $entry->evening_girls +
                                $entry->morning_oosc_boys +
                                $entry->morning_oosc_girls +
                                $entry->morning_p2p_boys +
                                $entry->morning_p2p_girls +
                                $entry->evening_oosc_boys +
                                $entry->evening_oosc_girls +
                                $entry->evening_p2p_boys +
                                $entry->evening_p2p_girls;
                        @endphp
                        <tr class="bg-gray-50 font-bold text-sm">
                            <td class="px-4 py-2.5 border border-gray-100">Total</td>
                            <td class="px-4 py-2.5 text-center border border-gray-100 bg-orange-100 text-orange-700">
                                {{ $oldTotal }}</td>
                            <td class="px-4 py-2.5 text-center border border-gray-100 bg-blue-100 text-blue-700">—</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Reason ───────────────────────────────────────────────────── --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Reason for Correction <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" rows="3" required maxlength="1000"
                    placeholder="Explain what was wrong and what the correct numbers should be..."
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('reason') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-900 text-white text-sm font-bold rounded-xl hover:bg-blue-800 transition">
                    Submit Correction Request
                </button>
                <a href="{{ route('hoi.corrections.index') }}"
                    class="px-5 py-2.5 text-sm text-gray-400 hover:text-gray-600 transition">Cancel</a>
            </div>

        </form>
    </div>

@endsection
