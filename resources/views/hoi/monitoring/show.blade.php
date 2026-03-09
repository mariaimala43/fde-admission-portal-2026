{{-- SAVE AS: resources/views/hoi/monitoring/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Monitoring Record #' . $monitoring->id)

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Monitoring — Detail</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $monitoring->classModel?->name }} &middot;
                {{ $monitoring->admission_date->format('d M Y') }}
            </p>
        </div>
        <a href="{{ route('hoi.monitoring.index') }}"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
            ← Back
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 mb-5 text-sm">ℹ️
            {{ session('info') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $e)
                <p>{{ $e }}</p>
            @endforeach
        </div>
    @endif

    {{-- Blocked / Finalized banners --}}
    @if ($monitoring->isBlocked())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3 mb-5 text-sm font-medium">
            🚫 This record is blocked — merit was rejected. No further updates allowed.
        </div>
    @elseif ($monitoring->isFinalized())
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-3 mb-5 text-sm font-medium">
            ✅ This record is finalized. No further changes needed.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Left column: Status cards ───────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Workflow status --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Workflow Stage</h3>
                <span class="text-sm px-3 py-1.5 rounded-full font-bold {{ $monitoring->workflowBadge() }}">
                    {{ $monitoring->workflowLabel() }}
                </span>
            </div>

            {{-- Admission summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Admission Summary</h3>
                @if ($monitoring->dailyAdmission)
                    @php $da = $monitoring->dailyAdmission; @endphp
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
                        @php
                            $oosc =
                                $da->morning_oosc_boys +
                                $da->morning_oosc_girls +
                                $da->evening_oosc_boys +
                                $da->evening_oosc_girls;
                            $p2p =
                                $da->morning_p2p_boys +
                                $da->morning_p2p_girls +
                                $da->evening_p2p_boys +
                                $da->evening_p2p_girls;
                            $total =
                                $da->morning_boys +
                                $da->morning_girls +
                                $da->evening_boys +
                                $da->evening_girls +
                                $oosc +
                                $p2p;
                        @endphp
                        @if ($oosc)
                            <div class="flex justify-between">
                                <span class="text-gray-500">OOSC</span>
                                <span class="font-semibold text-purple-700">{{ $oosc }}</span>
                            </div>
                        @endif
                        @if ($p2p)
                            <div class="flex justify-between">
                                <span class="text-gray-500">P2P</span>
                                <span class="font-semibold text-orange-700">{{ $p2p }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-100 pt-2 mt-2">
                            <span class="font-bold text-gray-700">Total</span>
                            <span class="font-bold text-blue-900 text-base">{{ $total }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-gray-400">No admission data linked.</p>
                @endif
            </div>

            {{-- Merit status (read-only for HOI) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Merit Status
                    <span class="text-gray-300 font-normal normal-case ml-1">(FDE managed)</span>
                </h3>
                <span class="text-sm px-3 py-1.5 rounded-full font-semibold {{ $monitoring->meritStatusBadge() }}">
                    {{ $monitoring->meritStatusLabel() }}
                </span>
            </div>
        </div>

        {{-- ── Middle column: Test & Doc update forms ──────────────────────── --}}
        <div class="space-y-4">

            {{-- Test Status --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Admission Test</h3>
                <div class="mb-3">
                    <span class="text-sm px-3 py-1.5 rounded-full font-semibold {{ $monitoring->testStatusBadge() }}">
                        {{ $monitoring->testStatusLabel() }}
                    </span>
                    @if ($monitoring->test_updated_at)
                        <p class="text-xs text-gray-400 mt-2">
                            Updated {{ $monitoring->test_updated_at->format('d M Y H:i') }}
                        </p>
                    @endif
                </div>

                @if (!$monitoring->isFinalized() && !$monitoring->isBlocked())
                    <form method="POST" action="{{ route('hoi.monitoring.test', $monitoring) }}">
                        @csrf @method('PATCH')
                        <select name="test_status"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach (['not_required' => 'Not Required', 'pending' => 'Pending', 'passed' => 'Passed', 'failed' => 'Failed'] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ $monitoring->test_status === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="w-full py-2 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                            Update Test Status
                        </button>
                    </form>
                @endif
            </div>

            {{-- Doc Status --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Documentation</h3>
                <div class="mb-3">
                    <span class="text-sm px-3 py-1.5 rounded-full font-semibold {{ $monitoring->docStatusBadge() }}">
                        {{ $monitoring->docStatusLabel() }}
                        @if ($monitoring->doc_status === 'affidavit_case' && $monitoring->affidavit_path)
                            📎
                        @endif
                    </span>
                    @if ($monitoring->affidavit_original_name)
                        <p class="text-xs text-gray-500 mt-2">
                            📄 {{ $monitoring->affidavit_original_name }}
                        </p>
                    @endif
                    @if ($monitoring->doc_updated_at)
                        <p class="text-xs text-gray-400 mt-1">
                            Updated {{ $monitoring->doc_updated_at->format('d M Y H:i') }}
                        </p>
                    @endif
                </div>

                @if (!$monitoring->isFinalized() && !$monitoring->isBlocked())
                    <form method="POST" action="{{ route('hoi.monitoring.doc', $monitoring) }}"
                        enctype="multipart/form-data" x-data="{ docStatus: '{{ $monitoring->doc_status }}' }">
                        @csrf @method('PATCH')

                        <select name="doc_status" x-model="docStatus"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pending">Pending</option>
                            <option value="provisional">Provisional</option>
                            <option value="affidavit_case">Affidavit Case</option>
                            {{-- 'complete' is FDE-only, not shown here --}}
                        </select>

                        {{-- Affidavit upload — shown only when affidavit_case selected --}}
                        <div x-show="docStatus === 'affidavit_case'" class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Affidavit File <span class="text-red-500">*</span>
                                <span class="font-normal text-gray-400">(PDF/JPG/PNG, max 5MB)</span>
                            </label>
                            <input type="file" name="affidavit" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            @if ($monitoring->affidavit_original_name)
                                <p class="text-xs text-green-600 mt-1">Current: {{ $monitoring->affidavit_original_name }}
                                </p>
                            @endif
                        </div>

                        <button type="submit"
                            class="w-full py-2 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                            Update Doc Status
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ── Right column: Audit trail ───────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-500 uppercase">Audit Trail</h3>
            </div>
            <div class="divide-y divide-gray-50 max-h-[520px] overflow-y-auto">
                @forelse ($monitoring->audits ?? [] as $audit)
                    <div class="px-5 py-3">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-xs font-semibold text-gray-700">
                                {{ str_replace('_', ' ', ucfirst($audit->field)) }}
                            </span>
                            <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                                {{ $audit->created_at->format('d M H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-orange-600 line-through">{{ $audit->old_value ?? '—' }}</span>
                            <span class="text-gray-400">→</span>
                            <span class="text-green-700 font-medium">{{ $audit->new_value ?? '—' }}</span>
                        </div>
                        @if ($audit->note)
                            <p class="text-xs text-gray-400 mt-1 italic">{{ $audit->note }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">by {{ $audit->changedBy?->name ?? 'System' }}</p>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-400 text-xs">No changes recorded yet.</div>
                @endforelse
            </div>
        </div>

    </div>

@endsection
