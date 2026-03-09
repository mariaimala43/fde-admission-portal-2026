@extends('layouts.app')
@section('title', 'Baseline Enrollment')

@section('content')

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h2 class="page-title">Baseline Enrollment</h2>
            <p class="page-sub">
                {{ $institution->name }} — Enter how many students are currently enrolled in each class.
            </p>
        </div>
        @if ($allSubmitted)
            <span class="badge badge-green" style="font-size:13px;padding:6px 16px;">✅ All Submitted</span>
        @endif
    </div>

    {{-- ── Over-Capacity Warning Banner ────────────────────────────── --}}
    @if ($isOverCapacity)
        <div class="capacity-warning">
            <svg class="capacity-warning__icon" width="20" height="20" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
            <div>
                <p class="capacity-warning__title">⚠️ Intake Capacity Exceeded</p>
                <p class="capacity-warning__body">
                    Total enrollment <strong>({{ number_format($totalEnrollment) }})</strong> exceeds
                    intake capacity <strong>({{ number_format($totalSeats) }})</strong> by
                    <strong>{{ number_format($overBy) }} student(s)</strong>.
                    No further admissions can be entered until capacity is reviewed by FDE.
                </p>
            </div>
        </div>
    @endif

    {{-- ── Stats Cards ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">

        {{-- Intake Capacity --}}
        <div class="stat-card stat-card--green">
            <p class="stat-label">Intake Capacity</p>
            <p class="stat-num stat-num--green">{{ number_format($totalSeats) }}</p>
        </div>

        {{-- Existing Enrollment --}}
        <div class="stat-card stat-card--orange">
            <p class="stat-label">Existing Enrollment</p>
            <p class="stat-num stat-num--orange">{{ number_format($totalEnrolled) }}</p>
        </div>

        {{-- Seats Available --}}
        <div class="stat-card {{ $totalAvailable > 0 ? 'stat-card--green' : 'stat-card--red' }}">
            <p class="stat-label">Seats Available</p>
            <p class="stat-num {{ $totalAvailable > 0 ? 'stat-num--green' : 'stat-num--red' }}">
                {{ number_format($totalAvailable) }}
            </p>
            @if ($isOverCapacity)
                <p class="stat-sub" style="color:#f87171;">FULL</p>
            @endif
        </div>

        {{-- Newly Admitted --}}
        <div class="stat-card stat-card--blue">
            <p class="stat-label">Newly Admitted</p>
            <p class="stat-num stat-num--blue">{{ number_format($totalNewAdmit) }}</p>
        </div>

        {{-- Total Enrollment — spans 2 cols, hero card --}}
        <div class="stat-card md:col-span-2 {{ $isOverCapacity ? 'stat-card--hero-red' : 'stat-card--hero-green' }}">
            <p class="stat-label">Total Enrollment</p>
            <p class="stat-num stat-num--white">{{ number_format($totalEnrollment) }}</p>
            @if ($isOverCapacity)
                <p class="stat-sub">▲ {{ number_format($overBy) }} over capacity</p>
            @else
                <p class="stat-sub">{{ number_format($totalAvailable) }} seats remaining</p>
            @endif
        </div>

    </div>

    {{-- ── Enrollment Form ───────────────────────────────────────────── --}}
    <form method="POST" action="{{ route('hoi.enrollment.save') }}">
        @csrf

        <div class="fde-table-wrap mb-6">
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th class="text-left">Class</th>
                            <th class="text-center">Sections</th>
                            <th class="text-center col-orange">
                                Existing Enrollment
                            </th>
                            <th class="text-center col-green">
                                Intake Capacity
                            </th>
                            <th class="text-center col-green">
                                Seats Available
                                <span class="th-sub">(Auto-Calculated)</span>
                            </th>
                            <th class="text-center col-blue">
                                Newly Admitted
                                <span class="th-sub">(Daily Updates)</span>
                            </th>
                            <th class="text-center col-accent">
                                Total Enrollment
                                <span class="th-sub">(Auto-Calculated)</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($classes as $index => $ic)
                            @php
                                $editable = $ic->isEnrollmentEditable();
                                $secs = ($sections[$ic->class_id] ?? collect())->pluck('name')->join(', ') ?: '-';
                                $secCount = ($sections[$ic->class_id] ?? collect())->count();
                                $admitted = $newlyAdmitted[$ic->class_id] ?? 0;
                                $seatsAvail = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                                $totalEnrl = $ic->existing_enrollment + $admitted;
                            @endphp

                            <tr x-data="{ seats: {{ $ic->total_seats }}, enrolled: {{ $ic->existing_enrollment }}, admitted: {{ $admitted }} }">

                                {{-- Class Name --}}
                                <td>
                                    <span class="font-semibold">{{ $ic->classModel?->name }}</span>
                                    @if ($ic->classModel?->is_ece)
                                        <span class="badge badge-purple" style="margin-left:6px;">ECE</span>
                                    @endif
                                    <input type="hidden" name="enrollment[{{ $index }}][class_id]"
                                        value="{{ $ic->class_id }}" />
                                </td>

                                {{-- Sections --}}
                                <td class="text-center">
                                    <span class="font-medium">{{ max(1, $secCount) }}</span>
                                    @if ($secs !== '-')
                                        <div class="th-sub" style="font-size:11px;">{{ $secs }}</div>
                                    @endif
                                </td>

                                {{-- Existing Enrollment --}}
                                <td class="text-center">
                                    @if ($editable)
                                        <input type="number" name="enrollment[{{ $index }}][existing]"
                                            class="input-compact" x-model.number="enrolled" :max="seats"
                                            min="0" />
                                    @else
                                        <span class="font-bold stat-num--orange" style="font-size:17px;">
                                            {{ number_format($ic->existing_enrollment) }}
                                        </span>
                                        <input type="hidden" name="enrollment[{{ $index }}][existing]"
                                            value="{{ $ic->existing_enrollment }}" />
                                    @endif
                                </td>

                                {{-- Intake Capacity --}}
                                <td class="text-center">
                                    <span class="font-bold stat-num--green" style="font-size:17px;">
                                        {{ number_format($ic->total_seats) }}
                                    </span>
                                </td>

                                {{-- Seats Available (reactive) --}}
                                <td class="text-center">
                                    @if ($editable)
                                        <span class="font-bold" style="font-size:17px;"
                                            :class="(seats - enrolled - admitted) > 0 ? 'stat-num--green' : 'stat-num--red'"
                                            x-text="Math.max(0, seats - enrolled - admitted)">
                                        </span>
                                    @else
                                        <span class="font-bold {{ $seatsAvail > 0 ? 'stat-num--green' : 'stat-num--red' }}"
                                            style="font-size:17px;">
                                            {{ number_format($seatsAvail) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Newly Admitted --}}
                                <td class="text-center">
                                    <span class="font-bold stat-num--blue" style="font-size:17px;">
                                        {{ number_format($admitted) }}
                                    </span>
                                </td>

                                {{-- Total Enrollment (reactive) --}}
                                <td class="text-center td-accent">
                                    @if ($editable)
                                        <span class="font-bold stat-num--green" style="font-size:17px;"
                                            x-text="enrolled + admitted">
                                        </span>
                                    @else
                                        <span class="font-bold stat-num--green" style="font-size:17px;">
                                            {{ number_format($totalEnrl) }}
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Totals Footer --}}
                    <tfoot>
                        <tr>
                            <td>TOTAL</td>
                            <td class="text-center">
                                {{ $classes->sum(fn($ic) => max(1, ($sections[$ic->class_id] ?? collect())->count())) }}
                            </td>
                            <td class="text-center stat-num--orange">
                                {{ number_format($totalEnrolled) }}
                            </td>
                            <td class="text-center stat-num--green">
                                {{ number_format($totalSeats) }}
                            </td>
                            <td class="text-center {{ $totalAvailable > 0 ? 'stat-num--green' : 'stat-num--red' }}">
                                {{ number_format($totalAvailable) }}
                            </td>
                            <td class="text-center stat-num--blue">
                                {{ number_format($totalNewAdmit) }}
                            </td>
                            <td class="text-center td-accent">
                                {{ number_format($totalEnrolled + $totalNewAdmit) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ── Action Buttons ────────────────────────────────────────── --}}
        @if (!$allSubmitted)
            <div class="flex flex-wrap gap-3 items-center">
                <button type="submit" name="action" value="save" class="btn btn-secondary">
                    💾 Save Draft
                </button>
                <button type="submit" name="action" value="submit" class="btn btn-primary"
                    onclick="return confirm('Submit enrollment? This cannot be edited after submission.')">
                    ✅ Submit Enrollment
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">← Back</a>
            </div>
        @else
            <div class="fde-alert fde-alert-success">
                ✅ Enrollment has been submitted. Contact FDE Cell if changes are needed.
            </div>
        @endif

    </form>

@endsection
