@extends('layouts.app')
@section('title', 'Baseline Enrollment')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Baseline Enrollment</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->name }} — Enter how many students are currently enrolled in each class.
            </p>
        </div>
        @if ($allSubmitted)
            <span class="bg-green-100 text-green-700 text-sm font-semibold px-4 py-2 rounded-full">
                All Submitted
            </span>
        @endif
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Intake Capacity</p>
            <p class="text-2xl font-bold text-blue-900">{{ number_format($totalSeats) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Existing Enrollment</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($totalEnrolled) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Seats Available</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalAvailable) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Newly Admitted</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($totalNewAdmit) }}</p>
        </div>
        <div class="bg-blue-900 rounded-xl shadow-sm p-5 text-center">
            <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Total Enrollment</p>
            <p class="text-2xl font-bold text-white">{{ number_format($totalEnrolled + $totalNewAdmit) }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('hoi.enrollment.save') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Class</th>
                            <th class="px-4 py-3 text-center">No. of Sections</th>
                            <th class="px-4 py-3 text-center">Existing Enrollment</th>
                            <th class="px-4 py-3 text-center">Intake Capacity</th>
                            <th class="px-4 py-3 text-center text-green-600">Seats Available<br><span class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                            <th class="px-4 py-3 text-center text-blue-600">Newly Admitted<br><span class="normal-case font-normal text-gray-400">(Daily Updates)</span></th>
                            <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Enrollment<br><span class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($classes as $index => $ic)
                            @php
                                $editable   = $ic->isEnrollmentEditable();
                                $secs       = ($sections[$ic->class_id] ?? collect())->pluck('name')->join(', ') ?: '-';
                                $secCount   = ($sections[$ic->class_id] ?? collect())->count();
                                $admitted   = $newlyAdmitted[$ic->class_id] ?? 0;
                                $seatsAvail = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                                $totalEnrl  = $ic->existing_enrollment + $admitted;
                            @endphp

                            <tr class="hover:bg-gray-50"
                                x-data="{ seats: {{ $ic->total_seats }}, enrolled: {{ $ic->existing_enrollment }}, admitted: {{ $admitted }} }">

                                {{-- 1. Class --}}
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800">{{ $ic->classModel?->name }}</span>
                                    @if ($ic->classModel?->is_ece)
                                        <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">ECE</span>
                                    @endif
                                    <input type="hidden" name="enrollment[{{ $index }}][class_id]" value="{{ $ic->class_id }}" />
                                </td>

                                {{-- 2. Number of Sections --}}
                                <td class="px-4 py-3 text-center text-gray-600">
                                    <span class="font-medium">{{ max(1, $secCount) }}</span>
                                    @if ($secs !== '-')
                                        <div class="text-xs text-gray-400">({{ $secs }})</div>
                                    @endif
                                </td>

                                {{-- 3. Existing Enrollment --}}
                                <td class="px-4 py-3 text-center">
                                    @if ($editable)
                                        <input type="number" name="enrollment[{{ $index }}][existing]"
                                            x-model.number="enrolled" :max="seats" min="0"
                                            class="w-24 border-2 border-blue-300 rounded-lg px-3 py-2 text-sm text-center
                                                   font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    @else
                                        <span class="text-lg font-bold text-orange-600">
                                            {{ number_format($ic->existing_enrollment) }}
                                        </span>
                                        <input type="hidden" name="enrollment[{{ $index }}][existing]" value="{{ $ic->existing_enrollment }}" />
                                    @endif
                                </td>

                                {{-- 4. Intake Capacity --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="text-lg font-bold text-blue-900">{{ number_format($ic->total_seats) }}</span>
                                </td>

                                {{-- 5. Seats Available (Auto) --}}
                                <td class="px-4 py-3 text-center">
                                    @if ($editable)
                                        <span class="text-lg font-bold"
                                            :class="(seats - enrolled - admitted) > 0 ? 'text-green-600' : 'text-red-500'"
                                            x-text="Math.max(0, seats - enrolled - admitted)">
                                        </span>
                                    @else
                                        <span class="text-lg font-bold {{ $seatsAvail > 0 ? 'text-green-600' : 'text-red-500' }}">
                                            {{ number_format($seatsAvail) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- 6. Newly Admitted (from daily) --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="text-lg font-bold text-blue-700">{{ number_format($admitted) }}</span>
                                </td>

                                {{-- 7. Total Enrollment After Admissions (Auto) --}}
                                <td class="px-4 py-3 text-center bg-blue-50">
                                    @if ($editable)
                                        <span class="text-lg font-bold text-blue-900"
                                            x-text="enrolled + admitted">
                                        </span>
                                    @else
                                        <span class="text-lg font-bold text-blue-900">{{ number_format($totalEnrl) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Totals Footer --}}
                    <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                        <tr>
                            <td class="px-4 py-3 text-gray-700">TOTAL</td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ $classes->sum(fn($ic) => max(1, ($sections[$ic->class_id] ?? collect())->count())) }}
                            </td>
                            <td class="px-4 py-3 text-center text-orange-600">
                                {{ number_format($totalEnrolled) }}
                            </td>
                            <td class="px-4 py-3 text-center text-blue-900">
                                {{ number_format($totalSeats) }}
                            </td>
                            <td class="px-4 py-3 text-center {{ $totalAvailable > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ number_format($totalAvailable) }}
                            </td>
                            <td class="px-4 py-3 text-center text-blue-700">
                                {{ number_format($totalNewAdmit) }}
                            </td>
                            <td class="px-4 py-3 text-center text-blue-900 bg-blue-100">
                                {{ number_format($totalEnrolled + $totalNewAdmit) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

        {{-- Actions --}}
        @if (!$allSubmitted)
            <div class="flex gap-4">
                <button type="submit" name="action" value="save"
                    class="px-8 py-3 rounded-lg text-sm font-medium border border-gray-300
                           text-gray-700 hover:bg-gray-50 transition">
                    Save Draft
                </button>
                <button type="submit" name="action" value="submit"
                    class="bg-blue-900 text-white px-8 py-3 rounded-lg font-semibold text-sm
                           hover:bg-blue-800 transition"
                    onclick="return confirm('Submit enrollment? This cannot be edited after submission.')">
                    Submit Enrollment
                </button>
                <a href="{{ route('dashboard') }}"
                    class="px-8 py-3 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-700">
                    Back
                </a>
            </div>
        @else
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                Enrollment has been submitted. Contact FDE Cell if changes are needed.
            </div>
        @endif

    </form>

@endsection
