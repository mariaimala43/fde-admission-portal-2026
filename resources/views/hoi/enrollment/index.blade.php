@extends('layouts.app')
@section('title', 'Baseline Enrollment')

@section('content')

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h2 class="page-title">Baseline Enrollment</h2>
            <p class="page-sub">
                {{ $institution->name }} — Enter promoted and failed students per class.
            </p>
        </div>
        @if ($allSubmitted)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                ✅ Enrollment Submitted
            </span>
        @endif
    </div>

    {{-- ── Validation error ───────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-5 text-sm flex items-start gap-2">
            <span class="mt-0.5 shrink-0">⚠️</span>
            <div>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Alpine data prep ──────────────────────────────────────── --}}
    @php
        $enrollmentRows = $classes->values()->map(fn($ic) => [
            // Combined (non-evening schools)
            'promoted'        => (int) $ic->promoted_count,
            'failed'          => (int) $ic->failed_count,
            'cap'             => (int) $ic->existing_enrollment,
            // Per-shift (evening schools)
            'morning_promoted'=> (int) $ic->morning_promoted,
            'morning_failed'  => (int) $ic->morning_failed,
            'morning_cap'     => (int) $ic->morning_existing,
            'evening_promoted'=> (int) $ic->evening_promoted,
            'evening_failed'  => (int) $ic->evening_failed,
            'evening_cap'     => (int) $ic->evening_existing,
        ]);
    @endphp

    {{-- ── All in one Alpine scope ──────────────────────────────────── --}}
    <div x-data="enrollmentPage()">

        {{-- ── Stats Cards ──────────────────────────────────────────── --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="stat-card stat-card--orange">
                <p class="stat-label">Total Existing Students</p>
                <p class="stat-num stat-num--orange" x-text="totalExisting()"></p>
            </div>
            <div class="stat-card stat-card--green">
                <p class="stat-label">Total Promoted</p>
                <p class="stat-num stat-num--green" x-text="totalPromoted()"></p>
            </div>
            <div class="stat-card stat-card--red">
                <p class="stat-label">Total Failed</p>
                <p class="stat-num stat-num--red" x-text="totalFailed()"></p>
            </div>
        </div>

        {{-- ── Enrollment Form ───────────────────────────────────────── --}}
        <form method="POST" action="{{ route('hoi.enrollment.save') }}">
            @csrf

            <div class="fde-table-wrap mb-6">
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th class="text-left">Class</th>

                                @if (!$hasEvening)
                                    {{-- ── Morning-only columns ── --}}
                                    <th class="text-center col-green">
                                        Promoted
                                        <span class="th-sub">(Passed from previous class)</span>
                                    </th>
                                    <th class="text-center" style="color:#f87171;">
                                        Failed
                                        <span class="th-sub">(Repeating same class)</span>
                                    </th>
                                    <th class="text-center col-orange">
                                        Total Existing
                                        <span class="th-sub">(Promoted + Failed)</span>
                                    </th>
                                    <th class="text-center" style="color:#94a3b8;">
                                        Existing Students
                                        <span class="th-sub">(cap from Class Setup)</span>
                                    </th>
                                @else
                                    {{-- ── Evening school columns ── --}}
                                    <th class="text-center" style="color:#3b82f6;">
                                        🌅 Morning Promoted
                                        <span class="th-sub">(Passed)</span>
                                    </th>
                                    <th class="text-center" style="color:#f87171;">
                                        🌅 Morning Failed
                                        <span class="th-sub">(Repeating)</span>
                                    </th>
                                    <th class="text-center" style="color:#f59e0b;">
                                        Morning Total
                                    </th>
                                    <th class="text-center" style="color:#94a3b8;">
                                        Morn. Cap
                                    </th>
                                    <th class="text-center" style="color:#6366f1;">
                                        🌆 Evening Promoted
                                        <span class="th-sub">(Passed)</span>
                                    </th>
                                    <th class="text-center" style="color:#f87171;">
                                        🌆 Evening Failed
                                        <span class="th-sub">(Repeating)</span>
                                    </th>
                                    <th class="text-center" style="color:#f59e0b;">
                                        Evening Total
                                    </th>
                                    <th class="text-center" style="color:#94a3b8;">
                                        Even. Cap
                                    </th>
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($classes as $index => $ic)
                                <tr>
                                    {{-- Class Name --}}
                                    <td>
                                        <span class="font-semibold">{{ $ic->classModel?->name }}</span>
                                        @if ($ic->classModel?->is_ece)
                                            <span class="badge badge-purple" style="margin-left:6px;">ECE</span>
                                        @endif
                                        <input type="hidden" name="enrollment[{{ $index }}][class_id]"
                                            value="{{ $ic->class_id }}" />
                                    </td>

                                    @if (!$hasEvening)
                                        {{-- ── Morning-only inputs ── --}}

                                        {{-- Promoted --}}
                                        <td class="text-center">
                                            <input type="number" min="0" max="99999"
                                                x-model.number="rows[{{ $index }}].promoted"
                                                name="enrollment[{{ $index }}][promoted]"
                                                class="input-compact" />
                                        </td>

                                        {{-- Failed --}}
                                        <td class="text-center">
                                            <input type="number" min="0" max="99999"
                                                x-model.number="rows[{{ $index }}].failed"
                                                name="enrollment[{{ $index }}][failed]"
                                                class="input-compact" />
                                        </td>

                                        {{-- Total (reactive, red if over) --}}
                                        <td class="text-center">
                                            <span class="font-bold" style="font-size:17px;"
                                                :class="isOverLimit({{ $index }}) ? 'text-red-500' : 'stat-num--orange'"
                                                x-text="rows[{{ $index }}].promoted + rows[{{ $index }}].failed"></span>
                                            <p x-show="isOverLimit({{ $index }})"
                                               class="text-xs text-red-500 mt-0.5 font-medium">Exceeds cap!</p>
                                            <input type="hidden" name="enrollment[{{ $index }}][existing]"
                                                :value="rows[{{ $index }}].promoted + rows[{{ $index }}].failed" />
                                        </td>

                                        {{-- Cap (read-only) --}}
                                        <td class="text-center">
                                            <span class="font-semibold text-gray-400" style="font-size:15px;">
                                                {{ $ic->existing_enrollment > 0 ? number_format($ic->existing_enrollment) : '—' }}
                                            </span>
                                        </td>

                                    @else
                                        {{-- ── Evening school inputs ── --}}

                                        {{-- Morning Promoted --}}
                                        <td class="text-center" style="background:#eff6ff;">
                                            <input type="number" min="0" max="99999"
                                                x-model.number="rows[{{ $index }}].morning_promoted"
                                                name="enrollment[{{ $index }}][morning_promoted]"
                                                class="input-compact" style="border-color:#bfdbfe;" />
                                        </td>

                                        {{-- Morning Failed --}}
                                        <td class="text-center" style="background:#eff6ff;">
                                            <input type="number" min="0" max="99999"
                                                x-model.number="rows[{{ $index }}].morning_failed"
                                                name="enrollment[{{ $index }}][morning_failed]"
                                                class="input-compact" style="border-color:#bfdbfe;" />
                                        </td>

                                        {{-- Morning Total --}}
                                        <td class="text-center" style="background:#eff6ff;">
                                            <span class="font-bold" style="font-size:16px;"
                                                :class="isOverLimitMorning({{ $index }}) ? 'text-red-500' : 'stat-num--orange'"
                                                x-text="rows[{{ $index }}].morning_promoted + rows[{{ $index }}].morning_failed"></span>
                                            <p x-show="isOverLimitMorning({{ $index }})"
                                               class="text-xs text-red-500 mt-0.5">Exceeds cap!</p>
                                        </td>

                                        {{-- Morning Cap --}}
                                        <td class="text-center" style="color:#94a3b8;background:#f8fafc;">
                                            <span class="font-semibold" style="font-size:14px;">
                                                {{ $ic->morning_existing > 0 ? number_format($ic->morning_existing) : '—' }}
                                            </span>
                                        </td>

                                        {{-- Evening Promoted --}}
                                        <td class="text-center" style="background:#eef2ff;">
                                            <input type="number" min="0" max="99999"
                                                x-model.number="rows[{{ $index }}].evening_promoted"
                                                name="enrollment[{{ $index }}][evening_promoted]"
                                                class="input-compact" style="border-color:#c7d2fe;" />
                                        </td>

                                        {{-- Evening Failed --}}
                                        <td class="text-center" style="background:#eef2ff;">
                                            <input type="number" min="0" max="99999"
                                                x-model.number="rows[{{ $index }}].evening_failed"
                                                name="enrollment[{{ $index }}][evening_failed]"
                                                class="input-compact" style="border-color:#c7d2fe;" />
                                        </td>

                                        {{-- Evening Total --}}
                                        <td class="text-center" style="background:#eef2ff;">
                                            <span class="font-bold" style="font-size:16px;"
                                                :class="isOverLimitEvening({{ $index }}) ? 'text-red-500' : 'stat-num--orange'"
                                                x-text="rows[{{ $index }}].evening_promoted + rows[{{ $index }}].evening_failed"></span>
                                            <p x-show="isOverLimitEvening({{ $index }})"
                                               class="text-xs text-red-500 mt-0.5">Exceeds cap!</p>
                                        </td>

                                        {{-- Evening Cap --}}
                                        <td class="text-center" style="color:#94a3b8;background:#f8fafc;">
                                            <span class="font-semibold" style="font-size:14px;">
                                                {{ $ic->evening_existing > 0 ? number_format($ic->evening_existing) : '—' }}
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <td>TOTAL</td>
                                @if (!$hasEvening)
                                    <td class="text-center stat-num--green" x-text="totalPromoted()">—</td>
                                    <td class="text-center" style="color:#f87171;" x-text="totalFailed()">—</td>
                                    <td class="text-center stat-num--orange" x-text="totalExisting()">
                                        {{ number_format($totalEnrolled) }}
                                    </td>
                                    <td class="text-center" style="color:#94a3b8;">
                                        {{ number_format($classes->sum('existing_enrollment')) }}
                                    </td>
                                @else
                                    <td class="text-center" style="color:#3b82f6;" x-text="totalMorningPromoted()">—</td>
                                    <td class="text-center" style="color:#f87171;" x-text="totalMorningFailed()">—</td>
                                    <td class="text-center stat-num--orange" x-text="totalMorningExisting()">—</td>
                                    <td class="text-center" style="color:#94a3b8;">{{ number_format($classes->sum('morning_existing')) }}</td>
                                    <td class="text-center" style="color:#6366f1;" x-text="totalEveningPromoted()">—</td>
                                    <td class="text-center" style="color:#f87171;" x-text="totalEveningFailed()">—</td>
                                    <td class="text-center stat-num--orange" x-text="totalEveningExisting()">—</td>
                                    <td class="text-center" style="color:#94a3b8;">{{ number_format($classes->sum('evening_existing')) }}</td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- ── Action Buttons ─────────────────────────────────────── --}}
            <div class="flex flex-wrap gap-3 items-center">
                <button type="submit" name="action" value="save" class="btn btn-secondary"
                    :disabled="hasAnyOverLimit()"
                    :class="hasAnyOverLimit() ? 'opacity-50 cursor-not-allowed' : ''">
                    💾 Save Draft
                </button>
                <button type="submit" name="action" value="submit" class="btn btn-primary"
                    :disabled="hasAnyOverLimit()"
                    :class="hasAnyOverLimit() ? 'opacity-50 cursor-not-allowed' : ''"
                    onclick="return !hasAnyOverLimit() && confirm('Submit enrollment? This records the baseline for the academic year. You can still edit it later if needed.')">
                    ✅ Submit Enrollment
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">← Back</a>
                <p x-show="hasAnyOverLimit()" class="text-sm text-red-600 font-medium">
                    ⚠️ Fix highlighted classes before saving.
                </p>
            </div>

        </form>
    </div>

    <script>
        function enrollmentPage() {
            return {
                rows: @json($enrollmentRows),

                // ── Combined totals (non-evening or overall) ──
                totalPromoted() { return this.rows.reduce((s, r) => s + (r.promoted || 0), 0); },
                totalFailed()   { return this.rows.reduce((s, r) => s + (r.failed   || 0), 0); },
                totalExisting() { return this.rows.reduce((s, r) => s + (r.promoted || 0) + (r.failed || 0), 0); },

                // ── Per-shift totals (evening schools) ──
                totalMorningPromoted() { return this.rows.reduce((s, r) => s + (r.morning_promoted || 0), 0); },
                totalMorningFailed()   { return this.rows.reduce((s, r) => s + (r.morning_failed   || 0), 0); },
                totalMorningExisting() { return this.rows.reduce((s, r) => s + (r.morning_promoted || 0) + (r.morning_failed || 0), 0); },
                totalEveningPromoted() { return this.rows.reduce((s, r) => s + (r.evening_promoted || 0), 0); },
                totalEveningFailed()   { return this.rows.reduce((s, r) => s + (r.evening_failed   || 0), 0); },
                totalEveningExisting() { return this.rows.reduce((s, r) => s + (r.evening_promoted || 0) + (r.evening_failed || 0), 0); },

                // ── Over-limit checks ──
                isOverLimit(i) {
                    const r = this.rows[i];
                    if (!r || r.cap <= 0) return false;
                    // For evening classes use the live shift inputs (not stale DB promoted_count)
                    const total = (r.morning_cap > 0 || r.evening_cap > 0)
                        ? (r.morning_promoted + r.morning_failed) + (r.evening_promoted + r.evening_failed)
                        : (r.promoted + r.failed);
                    return total > r.cap;
                },
                isOverLimitMorning(i) {
                    const r = this.rows[i];
                    if (!r || r.morning_cap <= 0) return false;
                    return (r.morning_promoted + r.morning_failed) > r.morning_cap;
                },
                isOverLimitEvening(i) {
                    const r = this.rows[i];
                    if (!r || r.evening_cap <= 0) return false;
                    return (r.evening_promoted + r.evening_failed) > r.evening_cap;
                },
                hasAnyOverLimit() {
                    return this.rows.some((r, i) =>
                        this.isOverLimit(i) || this.isOverLimitMorning(i) || this.isOverLimitEvening(i)
                    );
                },
            }
        }
    </script>

@endsection
