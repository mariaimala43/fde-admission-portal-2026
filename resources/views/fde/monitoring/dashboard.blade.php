{{-- SAVE AS: resources/views/fde/monitoring/dashboard.blade.php --}}
@extends('layouts.app')
@section('title', 'Admission Monitoring Dashboard')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Process Monitoring</h2>
            <p class="text-sm text-gray-500 mt-1">System-wide tracking · {{ $academicYear?->name }}</p>
        </div>
        <div class="flex gap-2">
            {{-- Sync button — creates monitoring records for any admissions that lack one --}}
            <form method="POST" action="{{ route('fde.monitoring.sync') }}">
                @csrf
                <button type="submit"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition"
                    onclick="return confirm('Sync monitoring records for all daily admissions?')">
                    🔄 Sync Records
                </button>
            </form>
            <a href="{{ route('fde.monitoring.index') }}"
                class="px-4 py-2 bg-blue-900 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">
                View All Records
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif

    {{-- ── Today's Summary ─────────────────────────────────────────────── --}}
    <div class="bg-blue-900 rounded-2xl px-6 py-4 mb-6 flex flex-wrap gap-6 items-center">
        <div>
            <p class="text-blue-300 text-xs uppercase tracking-wide">Today's Admissions</p>
            <p class="text-3xl font-bold text-white">{{ $todayStats->total ?? 0 }}</p>
        </div>
        <div class="w-px bg-blue-700 h-10 hidden md:block"></div>
        <div>
            <p class="text-blue-300 text-xs uppercase tracking-wide">Finalized Today</p>
            <p class="text-3xl font-bold text-green-400">{{ $todayStats->finalized ?? 0 }}</p>
        </div>
        <div class="ml-auto text-right">
            <p class="text-blue-300 text-xs">{{ now()->format('l, d M Y') }}</p>
            <p class="text-blue-400 text-xs">{{ $academicYear?->name }}</p>
        </div>
    </div>

    {{-- ── Grand Stats Cards ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
        @foreach ([['label' => 'Total Records', 'value' => $stats->total, 'color' => 'gray', 'icon' => '📋'], ['label' => 'Finalized', 'value' => $stats->finalized, 'color' => 'green', 'icon' => '✅'], ['label' => 'Test Failed', 'value' => $stats->test_failed, 'color' => 'red', 'icon' => '❌'], ['label' => 'Merit Rejected', 'value' => $stats->merit_rejected, 'color' => 'red', 'icon' => '🚫'], ['label' => 'Provisional Docs', 'value' => $stats->doc_provisional, 'color' => 'orange', 'icon' => '📄'], ['label' => 'Affidavit Cases', 'value' => $stats->doc_affidavit, 'color' => 'purple', 'icon' => '📎']] as $c)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
                <div class="text-lg mb-0.5">{{ $c['icon'] }}</div>
                <p class="text-xl font-bold text-{{ $c['color'] }}-600">{{ $c['value'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">{{ $c['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Workflow Funnel ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Workflow Pipeline</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @foreach ([['stage' => 'Draft', 'value' => $stats->total - $stats->test_verification - $stats->merit_confirmation - $stats->doc_verification - $stats->finalized, 'color' => 'gray'], ['stage' => 'Test Verification', 'value' => $stats->test_verification, 'color' => 'blue'], ['stage' => 'Merit Confirmation', 'value' => $stats->merit_confirmation, 'color' => 'yellow'], ['stage' => 'Doc Review', 'value' => $stats->doc_verification, 'color' => 'orange'], ['stage' => 'Finalized', 'value' => $stats->finalized, 'color' => 'green']] as $stage)
                <a href="{{ route('fde.monitoring.index', ['workflow' => str_replace(' ', '_', strtolower($stage['stage']))]) }}"
                    class="text-center p-3 rounded-xl bg-{{ $stage['color'] }}-50 border border-{{ $stage['color'] }}-100
                  hover:border-{{ $stage['color'] }}-300 transition cursor-pointer">
                    <p class="text-2xl font-bold text-{{ $stage['color'] }}-600">{{ max(0, $stage['value'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $stage['stage'] }}</p>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ── Sector-wise Breakdown ───────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 bg-blue-900 flex justify-between items-center">
            <span class="text-white font-bold text-sm">📊 Sector-wise Breakdown</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sector</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-green-600 uppercase">Finalized</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-orange-600 uppercase">Provisional</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-purple-600 uppercase">Affidavit</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-red-600 uppercase">Test Failed</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-red-600 uppercase">Merit Rej.</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-blue-600 uppercase">Complete %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sectors as $sector)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $sector->name }}</td>
                            <td class="px-4 py-3 text-center text-gray-700 font-semibold">{{ $sector->m_total }}</td>
                            <td class="px-4 py-3 text-center text-green-600 font-semibold">{{ $sector->m_finalized }}</td>
                            <td class="px-4 py-3 text-center text-orange-600">{{ $sector->m_provisional }}</td>
                            <td class="px-4 py-3 text-center text-purple-600">{{ $sector->m_affidavit }}</td>
                            <td class="px-4 py-3 text-center text-red-600">{{ $sector->m_test_failed }}</td>
                            <td class="px-4 py-3 text-center text-red-600">{{ $sector->m_merit_rej }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-green-500 h-1.5 rounded-full"
                                            style="width: {{ $sector->finalize_pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-600">{{ $sector->finalize_pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm">No data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Recent Audit Activity ────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-700 text-sm">🕐 Recent Changes (Audit Log)</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentAudits as $audit)
                <div class="px-5 py-3 flex items-start gap-3 hover:bg-gray-50 transition-colors">
                    <div
                        class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-bold shrink-0">
                        {{ strtoupper(substr($audit->changedBy?->name ?? 'S', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800">
                            <strong>{{ $audit->changedBy?->name ?? 'System' }}</strong>
                            <span class="text-gray-400 font-normal">({{ $audit->role_at_time }})</span>
                            changed <strong>{{ $audit->fieldLabel() }}</strong>
                            @if ($audit->monitoring?->institution)
                                at <span class="text-blue-600">{{ $audit->monitoring->institution->name }}</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span class="line-through text-red-400">{{ $audit->old_value ?: '—' }}</span>
                            → <span class="text-green-600 font-medium">{{ $audit->new_value }}</span>
                            @if ($audit->reason)
                                · <em class="text-gray-400">{{ Str::limit($audit->reason, 60) }}</em>
                            @endif
                        </p>
                    </div>
                    <div class="text-xs text-gray-400 shrink-0">{{ $audit->created_at->diffForHumans() }}</div>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No recent audit activity.</div>
            @endforelse
        </div>
    </div>

@endsection
