{{-- SAVE AS: resources/views/hoi/monitoring/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Monitoring Record #' . $monitoring->id)

@section('content')

    {{-- ── Header ──────────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Admission Monitoring</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $monitoring->classModel?->name }}
                &middot; {{ $monitoring->admission_date->format('d M Y') }}
                @if ($monitoring->total_admitted > 0)
                    &middot; <span class="font-semibold text-gray-700">{{ $monitoring->total_admitted }} students admitted</span>
                @endif
            </p>
        </div>
        <a href="{{ route('hoi.monitoring.index') }}"
            class="inline-flex items-center gap-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
            ← Back
        </a>
    </div>

    {{-- ── Flash Messages ──────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ℹ️ {{ session('info') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $e)
                <p>{{ $e }}</p>
            @endforeach
        </div>
    @endif

    {{-- ── Status Banners ──────────────────────────────────────────────────────── --}}
    @if ($monitoring->isBlocked())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3 mb-5 text-sm font-medium">
            🚫 This record is blocked. Please contact the FDE Cell for assistance.
        </div>
    @elseif ($monitoring->isFinalized())
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-3 mb-5 text-sm font-medium">
            ✅ All students finalized. No further action needed.
            @if ($monitoring->finalized_at)
                <span class="text-green-600 font-normal ml-1">
                    · {{ $monitoring->finalized_at->format('d M Y H:i') }}
                </span>
            @endif
        </div>
    @elseif ($monitoring->isPartiallyFinalized())
        <div class="bg-purple-50 border border-purple-200 text-purple-700 rounded-xl px-5 py-3 mb-5 text-sm font-medium">
            ⚡ Partial — {{ $monitoring->passed_count }} passed (finalized)
            · {{ $monitoring->failed_count }} failed (pending re-test via new daily admission entry).
        </div>
    @endif

    {{-- ── Pre-compute splits ───────────────────────────────────────────────────── --}}
    @php
        $passedSplit   = $monitoring->splits->firstWhere('split_type', 'passed');
        $exemptedSplit = $monitoring->splits->firstWhere('split_type', 'exempted');
        $failedSplit   = $monitoring->splits->firstWhere('split_type', 'failed');
        $hasSplits     = $monitoring->splits->isNotEmpty();
        $canEdit       = !$monitoring->isFinalized() && !$monitoring->isBlocked();

        // Progress steps
        $testDone  = $monitoring->hasTestCounts();
        $docsDone  = $exemptedSplit && $exemptedSplit->isFinalized();
        $docExists = (bool) $exemptedSplit;
        $allDone   = $monitoring->isFinalized();
    @endphp

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- 3-COLUMN GRID                                                              --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ╔══════════════════════════════════════════════════════════════════╗ --}}
        {{-- ║  LEFT COLUMN                                                    ║ --}}
        {{-- ╚══════════════════════════════════════════════════════════════════╝ --}}
        <div class="space-y-4">

            {{-- Progress Steps ──────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-4">Progress</h3>
                <div class="space-y-3">

                    {{-- Step 1: Test ──────────────────────────────────────────── --}}
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $testDone ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $testDone ? '✓' : '1' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold {{ $testDone ? 'text-green-700' : 'text-gray-800' }}">
                                Test Results
                            </p>
                            @if ($testDone)
                                <p class="text-xs text-green-600 mt-0.5">
                                    @if ($monitoring->test_status === 'not_required')
                                        Test not required
                                    @else
                                        {{ $monitoring->passed_count ?? 0 }} passed
                                        @if (($monitoring->exempted_count ?? 0) > 0)
                                            · {{ $monitoring->exempted_count }} exempted
                                        @endif
                                        @if (($monitoring->failed_count ?? 0) > 0)
                                            · {{ $monitoring->failed_count }} failed
                                        @endif
                                    @endif
                                    · 🔒 Locked
                                </p>
                            @else
                                <p class="text-xs text-blue-600 mt-0.5">Enter test results to continue</p>
                            @endif
                        </div>
                    </div>

                    {{-- Connector line ────────────────────────────────────────── --}}
                    @if ($docExists || !$testDone)
                        <div class="ml-3.5 w-px h-3 bg-gray-200"></div>

                        {{-- Step 2: Documents ─────────────────────────────────── --}}
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $docsDone ? 'bg-green-100 text-green-700' : ($testDone && $docExists ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-400') }}">
                                {{ $docsDone ? '✓' : '2' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold
                                    {{ $docsDone ? 'text-green-700' : ($testDone && $docExists ? 'text-amber-700' : 'text-gray-400') }}">
                                    Document Check
                                    @if (!$docExists && $testDone)
                                        <span class="text-xs font-normal text-gray-400">(not required)</span>
                                    @endif
                                </p>
                                @if ($exemptedSplit)
                                    <p class="text-xs mt-0.5 {{ $docsDone ? 'text-green-600' : 'text-amber-600' }}">
                                        {{ $exemptedSplit->student_count }} students · {{ $exemptedSplit->docStatusLabel() }}
                                    </p>
                                @elseif (!$testDone)
                                    <p class="text-xs text-gray-400 mt-0.5">Unlocks after test entry</p>
                                @endif
                            </div>
                        </div>

                        <div class="ml-3.5 w-px h-3 bg-gray-200"></div>
                    @else
                        <div class="ml-3.5 w-px h-3 bg-gray-200"></div>
                    @endif

                    {{-- Step 3: Finalized ─────────────────────────────────────── --}}
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $allDone ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400' }}">
                            {{ $allDone ? '✓' : '3' }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold {{ $allDone ? 'text-green-700' : 'text-gray-400' }}">
                                Finalized
                            </p>
                            @if ($monitoring->finalized_at)
                                <p class="text-xs text-green-600 mt-0.5">
                                    {{ $monitoring->finalized_at->format('d M Y H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- Workflow stage badge ─────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Workflow Stage</h3>
                <span class="text-sm px-3 py-1.5 rounded-full font-bold {{ $monitoring->workflowBadge() }}">
                    {{ $monitoring->workflowLabel() }}
                </span>
                @if ($monitoring->auto_finalized_at && !$monitoring->isPartiallyFinalized())
                    <p class="text-xs text-green-600 mt-2">
                        ⚡ Auto-finalized {{ $monitoring->auto_finalized_at->format('d M Y H:i') }}
                    </p>
                @endif
            </div>

            {{-- Test result summary (shown after counts locked) ─────────────── --}}
            @if ($monitoring->hasTestCounts())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Test Result Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Total Admitted</span>
                            <span class="font-bold text-gray-800 text-base">{{ $monitoring->total_admitted }}</span>
                        </div>
                        <div class="h-px bg-gray-100"></div>
                        @if (($monitoring->passed_count ?? 0) > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-green-600">✅ Passed</span>
                                <span class="font-bold text-green-700">{{ $monitoring->passed_count }}</span>
                            </div>
                        @endif
                        @if (($monitoring->exempted_count ?? 0) > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">⚪ Exempted</span>
                                <span class="font-bold text-gray-600">{{ $monitoring->exempted_count }}</span>
                            </div>
                        @endif
                        @if (($monitoring->failed_count ?? 0) > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-red-600">❌ Failed</span>
                                <span class="font-bold text-red-700">{{ $monitoring->failed_count }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-100">
                        <span class="text-xs text-gray-400 font-medium">
                            🔒 Locked
                            @if ($monitoring->test_updated_at)
                                · {{ $monitoring->test_updated_at->format('d M H:i') }}
                            @endif
                        </span>
                    </div>
                </div>
            @endif

            {{-- Admission Entry data ─────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Admission Entry</h3>
                @if ($monitoring->dailyAdmission)
                    @php
                        $da = $monitoring->dailyAdmission;
                        $oosc = $da->morning_oosc_boys + $da->morning_oosc_girls
                              + $da->evening_oosc_boys + $da->evening_oosc_girls;
                        $p2p  = $da->morning_p2p_boys  + $da->morning_p2p_girls
                              + $da->evening_p2p_boys  + $da->evening_p2p_girls;
                    @endphp
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Morning Boys</span>
                            <span class="font-semibold text-gray-800">{{ $da->morning_boys }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Morning Girls</span>
                            <span class="font-semibold text-gray-800">{{ $da->morning_girls }}</span>
                        </div>
                        @if ($da->evening_boys || $da->evening_girls)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Evening Boys</span>
                                <span class="font-semibold text-gray-800">{{ $da->evening_boys }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Evening Girls</span>
                                <span class="font-semibold text-gray-800">{{ $da->evening_girls }}</span>
                            </div>
                        @endif
                        @if ($oosc)
                            <div class="flex justify-between">
                                <span class="text-gray-500">OOSC</span>
                                <span class="font-semibold text-purple-700">{{ $oosc }}</span>
                            </div>
                        @endif
                        @if ($p2p)
                            <div class="flex justify-between">
                                <span class="text-gray-500">P2G</span>
                                <span class="font-semibold text-orange-700">{{ $p2p }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-100 pt-2 mt-2">
                            <span class="font-bold text-gray-700">Regular Total</span>
                            <span class="font-bold text-blue-900 text-base">{{ $da->regularTotal() }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-gray-400">No admission data linked.</p>
                @endif
            </div>

        </div>{{-- /LEFT COLUMN --}}

        {{-- ╔══════════════════════════════════════════════════════════════════╗ --}}
        {{-- ║  MIDDLE COLUMN — Test entry + doc forms                        ║ --}}
        {{-- ╚══════════════════════════════════════════════════════════════════╝ --}}
        <div class="space-y-4">

            {{-- ── STEP 1: Test Results Entry ───────────────────────────────── --}}
            @if (!$monitoring->hasTestCounts() && $canEdit)

                <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5"
                     x-data="testCountForm({{ $monitoring->total_admitted }})">

                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase">Step 1 — Test Results</h3>
                        <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">
                            {{ $monitoring->total_admitted }} students
                        </span>
                    </div>

                    {{-- ── Scenario Selector ──────────────────────────────────── --}}
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                        Was a test required for this batch?
                    </p>

                    <div class="space-y-2 mb-5">

                        {{-- Option A: Test Required --}}
                        <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer
                                      transition-colors select-none"
                               :class="scenario === 'required'
                                   ? 'border-blue-400 bg-blue-50'
                                   : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50'">
                            <input type="radio" value="required" x-model="scenario"
                                   @change="onScenarioChange()" class="mt-0.5 accent-blue-600">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">✅ Yes — Test Required</p>
                                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                    Admission test was held. Enter how many passed and how many failed.
                                </p>
                            </div>
                        </label>

                        {{-- Option B: Test Not Required --}}
                        <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer
                                      transition-colors select-none"
                               :class="scenario === 'not_required'
                                   ? 'border-green-400 bg-green-50'
                                   : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50'">
                            <input type="radio" value="not_required" x-model="scenario"
                                   @change="onScenarioChange()" class="mt-0.5 accent-green-600">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">⚪ No — Test Not Required</p>
                                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                    All students admitted without test (e.g. Class 1–5, Nursery / KG).
                                    <strong>Batch finalizes immediately.</strong>
                                </p>
                            </div>
                        </label>

                        {{-- Option C: Mixed --}}
                        <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer
                                      transition-colors select-none"
                               :class="scenario === 'mixed'
                                   ? 'border-yellow-400 bg-yellow-50'
                                   : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50'">
                            <input type="radio" value="mixed" x-model="scenario"
                                   @change="onScenarioChange()" class="mt-0.5 accent-yellow-600">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">🔀 Mixed — Both Apply</p>
                                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                    Some students were tested, others are exempted. Enter all three counts.
                                </p>
                            </div>
                        </label>

                    </div>

                    {{-- ── THE FORM ────────────────────────────────────────────── --}}
                    <form method="POST" action="{{ route('hoi.monitoring.test', $monitoring) }}">
                        @csrf @method('PATCH')

                        {{-- Hidden inputs — values always submitted via Alpine binding --}}
                        <input type="hidden" name="test_mode"      :value="scenario">
                        <input type="hidden" name="passed_count"   :value="passed">
                        <input type="hidden" name="failed_count"   :value="failed">
                        <input type="hidden" name="exempted_count" :value="exempted">

                        {{-- ── Not Required: Confirmation Box ───────────────── --}}
                        <div x-show="scenario === 'not_required'" x-cloak>
                            <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-center">
                                <div class="text-3xl mb-2">⚪</div>
                                <p class="text-sm font-bold text-green-800">
                                    All <span class="text-lg font-black text-green-900" x-text="total"></span>
                                    students will be admitted without test.
                                </p>
                                <p class="text-xs text-green-700 mt-2 leading-relaxed">
                                    The batch will be <strong>immediately finalized</strong>.
                                    No further steps needed — no test results, no documents required.
                                </p>
                            </div>
                        </div>

                        {{-- ── Required / Mixed: Count Inputs ──────────────── --}}
                        <div x-show="scenario !== 'not_required'" x-cloak>

                            {{-- Live counter ──────────────────────────────────── --}}
                            <div class="mb-4 p-3 rounded-lg text-center"
                                 :class="remaining === 0
                                     ? 'bg-green-50 border border-green-200'
                                     : 'bg-yellow-50 border border-yellow-200'">
                                <span class="text-sm font-semibold"
                                      :class="remaining === 0 ? 'text-green-700' : 'text-yellow-700'">
                                    <span x-text="entered"></span> / {{ $monitoring->total_admitted }} entered
                                </span>
                                <span class="text-xs ml-2"
                                      :class="remaining === 0 ? 'text-green-600' : 'text-yellow-600'">
                                    (<span x-text="remaining"></span> remaining)
                                </span>
                            </div>

                            {{-- Passed ─────────────────────────────────────────── --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-green-700 mb-1">
                                    ✅ Passed
                                    <span class="text-xs font-normal text-gray-400 ml-1">
                                        Students who passed the admission test
                                    </span>
                                </label>
                                <input type="number" min="0" max="{{ $monitoring->total_admitted }}"
                                    value="{{ old('passed_count', 0) }}"
                                    x-model.number="passed" @input="updateCounts()"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-green-400"
                                    placeholder="0">
                                <p class="text-xs text-green-600 mt-1">
                                    → Auto-finalized immediately. No further steps.
                                </p>
                            </div>

                            {{-- Exempted — Mixed mode only ───────────────────── --}}
                            <div class="mb-4" x-show="scenario === 'mixed'">
                                <label class="block text-sm font-medium text-gray-600 mb-1">
                                    ⚪ Exempted
                                    <span class="text-xs font-normal text-gray-400 ml-1">
                                        Test not required for these students
                                    </span>
                                </label>
                                <input type="number" min="0" max="{{ $monitoring->total_admitted }}"
                                    value="{{ old('exempted_count', 0) }}"
                                    x-model.number="exempted" @input="updateCounts()"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-gray-400"
                                    placeholder="0">
                                <p class="text-xs text-gray-500 mt-1">
                                    → Goes to document check (Step 2).
                                </p>
                            </div>

                            {{-- Failed ──────────────────────────────────────────── --}}
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-red-600 mb-1">
                                    ❌ Failed
                                    <span class="text-xs font-normal text-gray-400 ml-1">
                                        Did not pass the admission test
                                    </span>
                                </label>
                                <input type="number" min="0" max="{{ $monitoring->total_admitted }}"
                                    value="{{ old('failed_count', 0) }}"
                                    x-model.number="failed" @input="updateCounts()"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-red-400"
                                    placeholder="0">
                                <p class="text-xs text-red-500 mt-1">
                                    → Re-test required. Submit a new daily admission entry when they re-apply.
                                </p>
                            </div>

                        </div>

                        {{-- ── Submit Button ────────────────────────────────── --}}
                        <button type="submit"
                            :disabled="scenario !== 'not_required' && remaining !== 0"
                            :class="{
                                'bg-green-700 hover:bg-green-800 cursor-pointer':
                                    scenario === 'not_required',
                                'bg-blue-900 hover:bg-blue-800 cursor-pointer':
                                    scenario !== 'not_required' && remaining === 0,
                                'bg-gray-300 cursor-not-allowed':
                                    scenario !== 'not_required' && remaining !== 0
                            }"
                            class="w-full py-2.5 text-white text-sm font-semibold rounded-lg transition">
                            <span x-show="scenario === 'not_required'">
                                Confirm — All Students Admitted (Test Not Required)
                            </span>
                            <span x-show="scenario !== 'not_required' && remaining === 0">
                                Save &amp; Lock Test Results
                            </span>
                            <span x-show="scenario !== 'not_required' && remaining !== 0">
                                Enter all <span x-text="remaining"></span> remaining students first
                            </span>
                        </button>

                        <p class="text-xs text-gray-400 mt-2 text-center">
                            ⚠️ Once saved, counts are permanently locked.
                        </p>
                    </form>
                </div>

            @elseif ($monitoring->hasTestCounts())
                {{-- Test counts locked ──────────────────────────────────────── --}}
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Step 1 — Test Results</span>
                        <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">🔒 Locked</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        Test counts are locked. Contact the FDE Cell to correct an error.
                    </p>
                </div>
            @endif

            {{-- ── STEP 2: Split Result Cards + Doc Forms ──────────────────── --}}

            {{-- Passed Split ─────────────────────────────────────────────────── --}}
            @if ($passedSplit)
                <div class="rounded-xl border p-4
                    {{ $passedSplit->isFinalized() ? 'bg-green-50 border-green-200' : 'bg-white border-gray-100 shadow-sm' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-green-700 uppercase">✅ Passed Students</span>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold">
                            {{ $passedSplit->student_count }} students
                        </span>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $passedSplit->workflowBadge() }}">
                        {{ $passedSplit->workflowLabel() }}
                    </span>
                    @if ($passedSplit->finalized_at)
                        <p class="text-xs text-green-600 mt-1.5">
                            Auto-finalized {{ $passedSplit->finalized_at->format('d M Y H:i') }}
                        </p>
                    @endif
                    <p class="text-xs text-green-700 mt-1 font-medium">No document check required.</p>
                </div>
            @endif

            {{-- Exempted Split + Doc Form ─────────────────────────────────────── --}}
            @if ($exemptedSplit)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">

                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase">
                            @if ($exemptedSplit->doc_status === 'not_required')
                                ⚪ Exempted Students
                            @else
                                Step 2 — Document Check
                            @endif
                        </h3>
                        <span class="text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">
                            {{ $exemptedSplit->student_count }} students
                        </span>
                    </div>

                    {{-- Current doc status badge ─────────────────────────────── --}}
                    <div class="mb-3">
                        <span class="text-sm px-3 py-1 rounded-full font-semibold {{ $exemptedSplit->docStatusBadge() }}">
                            {{ $exemptedSplit->docStatusLabel() }}
                            @if ($exemptedSplit->doc_status === 'affidavit_case' && $exemptedSplit->affidavit_path)
                                📎
                            @endif
                        </span>
                        @if ($exemptedSplit->affidavit_original_name)
                            <p class="text-xs text-gray-500 mt-1.5">
                                📄 {{ $exemptedSplit->affidavit_original_name }}
                            </p>
                        @endif
                        @if ($exemptedSplit->doc_updated_at)
                            <p class="text-xs text-gray-400 mt-0.5">
                                Updated {{ $exemptedSplit->doc_updated_at->format('d M Y H:i') }}
                            </p>
                        @endif
                    </div>

                    @if ($canEdit && !$exemptedSplit->isFinalized())
                        <form method="POST" action="{{ route('hoi.monitoring.doc', $monitoring) }}"
                              enctype="multipart/form-data"
                              x-data="{ docStatus: '{{ $exemptedSplit->doc_status ?? 'pending' }}' }">
                            @csrf @method('PATCH')
                            <input type="hidden" name="split_id" value="{{ $exemptedSplit->id }}">

                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">
                                Update Document Status
                            </label>
                            <select name="doc_status" x-model="docStatus"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-1
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="not_required"   {{ ($exemptedSplit->doc_status ?? '') === 'not_required'   ? 'selected' : '' }}>
                                    ⚪ Not Required
                                </option>
                                <option value="pending"        {{ ($exemptedSplit->doc_status ?? '') === 'pending'        ? 'selected' : '' }}>
                                    ⏳ Pending
                                </option>
                                <option value="provisional"    {{ ($exemptedSplit->doc_status ?? '') === 'provisional'    ? 'selected' : '' }}>
                                    📄 Provisional Admission
                                </option>
                                <option value="affidavit_case" {{ ($exemptedSplit->doc_status ?? '') === 'affidavit_case' ? 'selected' : '' }}>
                                    📎 Affidavit Case
                                </option>
                                <option value="complete"       {{ ($exemptedSplit->doc_status ?? '') === 'complete'       ? 'selected' : '' }}>
                                    ✅ Documents Complete
                                </option>
                            </select>

                            {{-- Helper text per status ──────────────────────── --}}
                            <p class="text-xs text-gray-400 mb-3" x-show="docStatus === 'not_required'">
                                No documents needed. Batch will be finalized immediately.
                            </p>
                            <p class="text-xs text-gray-400 mb-3" x-show="docStatus === 'pending'">
                                Documents have not been submitted yet.
                            </p>
                            <p class="text-xs text-gray-400 mb-3" x-show="docStatus === 'provisional'">
                                Student admitted provisionally — documents to be submitted later.
                            </p>
                            <p class="text-xs text-gray-400 mb-3" x-show="docStatus === 'affidavit_case'">
                                Upload the affidavit document below.
                            </p>
                            <p class="text-xs text-green-600 mb-3 font-medium" x-show="docStatus === 'complete'">
                                ✅ Documents received and verified. Submitting will finalize this batch.
                            </p>

                            {{-- Affidavit upload ─────────────────────────────── --}}
                            <div x-show="docStatus === 'affidavit_case'" x-cloak class="mb-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Affidavit File <span class="text-red-500">*</span>
                                    <span class="font-normal text-gray-400">(PDF / JPG / PNG, max 5 MB)</span>
                                </label>
                                <input type="file" name="affidavit" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                @if ($exemptedSplit->affidavit_original_name)
                                    <p class="text-xs text-green-600 mt-1">
                                        Current: {{ $exemptedSplit->affidavit_original_name }}
                                    </p>
                                @endif
                            </div>

                            <button type="submit"
                                :class="docStatus === 'complete' || docStatus === 'not_required'
                                    ? 'bg-green-700 hover:bg-green-800'
                                    : 'bg-blue-900 hover:bg-blue-800'"
                                class="w-full py-2 text-white text-sm font-semibold rounded-lg transition">
                                <span x-show="docStatus === 'complete'">✅ Confirm — Mark Documents Complete</span>
                                <span x-show="docStatus === 'not_required'">Confirm — Documents Not Required</span>
                                <span x-show="docStatus !== 'complete' && docStatus !== 'not_required'">
                                    Update Document Status
                                </span>
                            </button>
                        </form>

                    @elseif ($exemptedSplit->isFinalized())
                        <div class="bg-green-50 rounded-lg px-3 py-2 text-xs text-green-700 font-medium">
                            ✅ Documentation finalized.
                        </div>
                    @else
                        <p class="text-xs text-gray-400 mt-1">No changes allowed at this stage.</p>
                    @endif
                </div>
            @endif

            {{-- Failed Split ─────────────────────────────────────────────────── --}}
            @if ($failedSplit)
                <div class="bg-red-50 rounded-xl border border-red-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-red-700 uppercase">❌ Failed Students</span>
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold">
                            {{ $failedSplit->student_count }} students
                        </span>
                    </div>
                    <span class="inline-block text-xs px-2 py-0.5 rounded-full font-semibold {{ $failedSplit->workflowBadge() }}">
                        {{ $failedSplit->workflowLabel() }}
                    </span>
                    <p class="text-xs text-red-600 mt-2 leading-relaxed">
                        These students need to re-sit the test. When they re-apply, enter them in a
                        new daily admission — a fresh monitoring record will be created automatically.
                    </p>
                </div>
            @endif

            {{-- Edge case: counts saved but no splits ────────────────────────── --}}
            @if (!$hasSplits && $monitoring->hasTestCounts())
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-xs text-yellow-700">
                    ⚠️ Counts were saved but no splits were generated. Please contact support.
                </div>
            @endif

        </div>{{-- /MIDDLE COLUMN --}}

        {{-- ╔══════════════════════════════════════════════════════════════════╗ --}}
        {{-- ║  RIGHT COLUMN — Audit trail                                    ║ --}}
        {{-- ╚══════════════════════════════════════════════════════════════════╝ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-500 uppercase">Audit Trail</h3>
            </div>
            <div class="divide-y divide-gray-50 max-h-[560px] overflow-y-auto">
                @forelse ($monitoring->audits as $audit)
                    <div class="px-5 py-3">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-xs font-semibold text-gray-700">{{ $audit->fieldLabel() }}</span>
                            <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                                {{ $audit->created_at->format('d M H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            @if ($audit->old_value)
                                <span class="text-orange-600 line-through">{{ $audit->old_value }}</span>
                                <span class="text-gray-400">→</span>
                            @endif
                            <span class="text-green-700 font-medium">{{ $audit->new_value ?? '—' }}</span>
                        </div>
                        @if ($audit->reason)
                            <p class="text-xs text-gray-400 mt-1 italic">{{ $audit->reason }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">by {{ $audit->changedBy?->name ?? 'System' }}</p>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-400 text-xs">No changes recorded yet.</div>
                @endforelse
            </div>
        </div>{{-- /RIGHT COLUMN --}}

    </div>{{-- /3-COLUMN GRID --}}

    @push('scripts')
        <script>
            function testCountForm(total) {
                return {
                    total:    total,
                    scenario: 'required',
                    passed:   {{ old('passed_count', 0) }},
                    failed:   {{ old('failed_count', 0) }},
                    exempted: {{ old('exempted_count', 0) }},

                    get entered()   { return this.passed + this.failed + this.exempted; },
                    get remaining() { return this.total - this.entered; },

                    onScenarioChange() {
                        if (this.scenario === 'not_required') {
                            this.passed   = 0;
                            this.failed   = 0;
                            this.exempted = this.total;
                        } else if (this.scenario === 'required') {
                            this.exempted = 0;
                        }
                        // 'mixed' — let HOI enter all 3 manually
                    },

                    updateCounts() {
                        // Clamp: don't let sum exceed total (reduce failed if over)
                        if (this.entered > this.total) {
                            this.failed = Math.max(0, this.failed - (this.entered - this.total));
                        }
                    }
                }
            }
        </script>
    @endpush

@endsection
