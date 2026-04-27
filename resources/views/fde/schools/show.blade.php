@extends('layouts.app')
@section('title', $institution->name . ' - Report')
@section('content')

    <div class="flex flex-wrap justify-between items-start gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $institution->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->sector?->name }} Sector
                &nbsp;&middot;&nbsp; {{ $institution->type }}
                &nbsp;&middot;&nbsp; {{ ucfirst(str_replace('_', ' ', $institution->gender)) }}
                &nbsp;&middot;&nbsp; {{ ucfirst($institution->shift) }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @role('director')
                <a href="{{ route('director.schools.index') }}" class="text-sm text-blue-600 hover:underline">← All Schools</a>
            @else
                <a href="{{ route('fde.schools.index') }}" class="text-sm text-blue-600 hover:underline">← All Schools</a>
                <a href="{{ route('fde.enrollment.show', $institution) }}"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">
                    🔓 Enrollment Override
                </a>
                <button type="button" onclick="document.getElementById('resetModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                    🗑️ Reset Admission Data
                </button>
            @endrole
        </div>
    </div>

    {{-- ── Reset Confirmation Modal (FDE only) ────────────────────────── --}}
    @role('fde_cell')
    <div id="resetModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-start gap-3 mb-4">
                <span class="text-3xl">⚠️</span>
                <div>
                    <h3 class="text-lg font-bold text-red-700">Reset Admission Data</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        This will permanently delete <strong>all daily admission records</strong> for
                        <strong>{{ $institution->name }}</strong> in the current academic year
                        ({{ $academicYear?->name ?? 'active year' }}).
                        The school will be able to re-enter their data from scratch.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('fde.schools.reset-admissions', $institution) }}">
                @csrf

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                        Reason <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" name="reason" maxlength="255"
                        placeholder="e.g. School submitted incorrect figures"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                        Type <span class="font-mono font-bold text-red-600">RESET</span> to confirm
                    </label>
                    <input type="text" name="confirmation" autocomplete="off" placeholder="RESET"
                        class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-red-400">
                    @error('confirmation')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 bg-red-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-red-700 transition">
                        Yes, Delete All Records
                    </button>
                    <button type="button" onclick="document.getElementById('resetModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endrole

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- Date Range Filter --}}
    <form method="GET" action="{{ route('fde.schools.show', $institution) }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <button type="submit"
                class="bg-blue-900 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                Apply
            </button>
        </div>
    </form>

    {{-- Grand Totals --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Admitted</p>
            <p class="text-3xl font-bold text-blue-900">{{ number_format($grandTotal) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Regular</p>
            <p class="text-3xl font-bold text-blue-700">{{ number_format($grandRegular) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-purple-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">OOSC</p>
            <p class="text-3xl font-bold text-purple-700">{{ number_format($grandOosc) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-5 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Private to Government</p>
            <p class="text-3xl font-bold text-orange-600">{{ number_format($grandP2p) }}</p>
        </div>
    </div>

    {{-- Class-wise Summary (Document 7-Column Format) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6"
        @if ($hasEvening) x-data="{ shift: 'both' }" @endif>
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">Class-wise Summary</h3>
            @if ($hasEvening)
                <div class="flex items-center gap-1 text-xs">
                    <button @click="shift = 'both'"
                        :class="shift === 'both' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1.5 rounded-l-lg font-medium transition">Both</button>
                    <button @click="shift = 'morning'"
                        :class="shift === 'morning' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1.5 font-medium transition">Morning</button>
                    <button @click="shift = 'evening'"
                        :class="shift === 'evening' ? 'bg-indigo-700 text-white' :
                            'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1.5 rounded-r-lg font-medium transition">Evening</button>
                </div>
            @endif
        </div>
        <p class="block sm:hidden text-xs text-gray-400 mb-2 px-4 pt-2 flex items-center gap-1">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            Swipe right to see all columns
        </p>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Class</th>
                        <th
                            class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">
                            Sections</th>
                        <th
                            class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">
                            Intake Capacity</th>
                        <th
                            class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">
                            Promoted Students</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Newly
                            Admitted</th>
                        <th
                            class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">
                            Seats Available</th>
                        <th
                            class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left bg-blue-50">
                            Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($classes as $ic)
                        @php
                            $s = $classSummary[$ic->class_id] ?? null;
                            $sMorning = $classSummaryMorning[$ic->class_id] ?? null;
                            $sEvening = $classSummaryEvening[$ic->class_id] ?? null;
                            $admitted = $s?->total ?? 0;
                            $admMorning = $sMorning?->total ?? 0;
                            $admEvening = $sEvening?->total ?? 0;
                            $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                            $availableMorning = max(0, ($ic->morning_seats ?? 0) - ($ic->morning_existing ?? 0) - $admMorning);
                            $availableEvening = max(0, ($ic->evening_seats ?? 0) - ($ic->evening_existing ?? 0) - $admEvening);
                            $totalEnrl = $ic->existing_enrollment + $admitted;
                            $totalMorning = ($ic->morning_existing ?? 0) + $admMorning;
                            $totalEvening = ($ic->evening_existing ?? 0) + $admEvening;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap font-semibold">
                                {{ $ic->classModel?->name }}
                                @if ($ic->classModel?->is_ece)
                                    <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 rounded-full">ECE</span>
                                @endif
                            </td>
                            <td
                                class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center font-medium text-gray-700 hidden sm:table-cell">
                                {{ $sectionCounts[$ic->class_id]->count ?? 0 }}
                            </td>
                            <td
                                class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center font-medium text-gray-700 hidden md:table-cell">
                                @if ($hasEvening)
                                    <span x-show="shift === 'both'">{{ number_format($ic->total_seats) }}</span>
                                    <span x-show="shift === 'morning'"
                                        x-cloak>{{ number_format($ic->morning_seats ?? 0) }}</span>
                                    <span x-show="shift === 'evening'"
                                        x-cloak>{{ number_format($ic->evening_seats ?? 0) }}</span>
                                @else
                                    {{ number_format($ic->total_seats) }}
                                @endif
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center hidden sm:table-cell">
                                @if ($hasEvening)
                                    {{-- Both --}}
                                    <span x-show="shift === 'both'">
                                        <div class="font-bold text-orange-600 text-base">{{ number_format($ic->existing_enrollment) }}</div>
                                        @if ($ic->promoted_count + $ic->failed_count > 0)
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                Promoted: <span class="text-green-600 font-semibold">{{ number_format($ic->promoted_count) }}</span>
                                                @if ($ic->failed_count > 0)
                                                    &nbsp;&middot;&nbsp;
                                                    Repeaters: <span class="text-red-500 font-semibold">{{ number_format($ic->failed_count) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </span>
                                    {{-- Morning --}}
                                    <span x-show="shift === 'morning'" x-cloak>
                                        <div class="font-bold text-orange-600 text-base">{{ number_format($ic->morning_existing ?? 0) }}</div>
                                        @if (($ic->morning_promoted ?? 0) + ($ic->morning_failed ?? 0) > 0)
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                Promoted: <span class="text-green-600 font-semibold">{{ number_format($ic->morning_promoted ?? 0) }}</span>
                                                @if (($ic->morning_failed ?? 0) > 0)
                                                    &nbsp;&middot;&nbsp;
                                                    Repeaters: <span class="text-red-500 font-semibold">{{ number_format($ic->morning_failed ?? 0) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </span>
                                    {{-- Evening --}}
                                    <span x-show="shift === 'evening'" x-cloak>
                                        <div class="font-bold text-orange-600 text-base">{{ number_format($ic->evening_existing ?? 0) }}</div>
                                        @if (($ic->evening_promoted ?? 0) + ($ic->evening_failed ?? 0) > 0)
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                Promoted: <span class="text-green-600 font-semibold">{{ number_format($ic->evening_promoted ?? 0) }}</span>
                                                @if (($ic->evening_failed ?? 0) > 0)
                                                    &nbsp;&middot;&nbsp;
                                                    Repeaters: <span class="text-red-500 font-semibold">{{ number_format($ic->evening_failed ?? 0) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </span>
                                @else
                                    <div class="font-bold text-orange-600 text-base">{{ number_format($ic->existing_enrollment) }}</div>
                                    @if ($ic->promoted_count + $ic->failed_count > 0)
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            Promoted: <span class="text-green-600 font-semibold">{{ number_format($ic->promoted_count) }}</span>
                                            @if ($ic->failed_count > 0)
                                                &nbsp;&middot;&nbsp;
                                                Repeaters: <span class="text-red-500 font-semibold">{{ number_format($ic->failed_count) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td
                                class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center text-blue-700 font-bold">
                                @if ($hasEvening)
                                    <span x-show="shift === 'both'">{{ number_format($admitted) }}</span>
                                    <span x-show="shift === 'morning'" x-cloak>{{ number_format($admMorning) }}</span>
                                    <span x-show="shift === 'evening'" x-cloak>{{ number_format($admEvening) }}</span>
                                    @if ($admitted > 0)
                                        <div class="text-xs text-gray-400 font-normal" x-show="shift === 'both'">
                                            Reg: {{ number_format(($s?->reg_boys ?? 0) + ($s?->reg_girls ?? 0)) }}
                                            &middot; OOSC:
                                            {{ number_format(($s?->oosc_boys ?? 0) + ($s?->oosc_girls ?? 0)) }}
                                            &middot; P2G: {{ number_format(($s?->p2p_boys ?? 0) + ($s?->p2p_girls ?? 0)) }}
                                        </div>
                                        <div class="text-xs text-gray-400 font-normal" x-show="shift === 'morning'"
                                            x-cloak>
                                            Reg:
                                            {{ number_format(($sMorning?->reg_boys ?? 0) + ($sMorning?->reg_girls ?? 0)) }}
                                            &middot; OOSC:
                                            {{ number_format(($sMorning?->oosc_boys ?? 0) + ($sMorning?->oosc_girls ?? 0)) }}
                                            &middot; P2G:
                                            {{ number_format(($sMorning?->p2p_boys ?? 0) + ($sMorning?->p2p_girls ?? 0)) }}
                                        </div>
                                        <div class="text-xs text-gray-400 font-normal" x-show="shift === 'evening'"
                                            x-cloak>
                                            Reg:
                                            {{ number_format(($sEvening?->reg_boys ?? 0) + ($sEvening?->reg_girls ?? 0)) }}
                                            &middot; OOSC:
                                            {{ number_format(($sEvening?->oosc_boys ?? 0) + ($sEvening?->oosc_girls ?? 0)) }}
                                            &middot; P2G:
                                            {{ number_format(($sEvening?->p2p_boys ?? 0) + ($sEvening?->p2p_girls ?? 0)) }}
                                        </div>
                                    @endif
                                @else
                                    {{ number_format($admitted) }}
                                    @if ($admitted > 0)
                                        <div class="text-xs text-gray-400 font-normal">
                                            Reg: {{ number_format(($s?->reg_boys ?? 0) + ($s?->reg_girls ?? 0)) }}
                                            &middot; OOSC:
                                            {{ number_format(($s?->oosc_boys ?? 0) + ($s?->oosc_girls ?? 0)) }}
                                            &middot; P2G: {{ number_format(($s?->p2p_boys ?? 0) + ($s?->p2p_girls ?? 0)) }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td
                                class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center font-bold hidden md:table-cell {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                @if ($hasEvening)
                                    <span x-show="shift === 'both'">{{ number_format($available) }}</span>
                                    <span x-show="shift === 'morning'" x-cloak>{{ number_format($availableMorning) }}</span>
                                    <span x-show="shift === 'evening'" x-cloak>{{ number_format($availableEvening) }}</span>
                                @else
                                    {{ number_format($available) }}
                                @endif
                            </td>
                            <td
                                class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center font-bold text-blue-900 bg-blue-50">
                                @if ($hasEvening)
                                    <span x-show="shift === 'both'">{{ number_format($totalEnrl) }}</span>
                                    <span x-show="shift === 'morning'" x-cloak>{{ number_format($totalMorning) }}</span>
                                    <span x-show="shift === 'evening'" x-cloak>{{ number_format($totalEvening) }}</span>
                                @else
                                    {{ number_format($totalEnrl) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                    <tr>
                        <td class="px-3 py-3 text-gray-700">TOTAL</td>
                        <td class="px-3 py-3 text-center text-gray-500 hidden sm:table-cell">
                            {{ $sectionCounts->sum('count') }}
                        </td>
                        <td class="px-3 py-3 text-center text-blue-900 hidden md:table-cell">
                            @if ($hasEvening)
                                <span x-show="shift === 'both'">{{ number_format($classes->sum('total_seats')) }}</span>
                                <span x-show="shift === 'morning'" x-cloak>{{ number_format($classes->sum('morning_seats')) }}</span>
                                <span x-show="shift === 'evening'" x-cloak>{{ number_format($classes->sum('evening_seats')) }}</span>
                            @else
                                {{ number_format($classes->sum('total_seats')) }}
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-orange-600 hidden sm:table-cell">
                            @if ($hasEvening)
                                <span x-show="shift === 'both'">{{ number_format($classes->sum('existing_enrollment')) }}</span>
                                <span x-show="shift === 'morning'" x-cloak>{{ number_format($classes->sum('morning_existing')) }}</span>
                                <span x-show="shift === 'evening'" x-cloak>{{ number_format($classes->sum('evening_existing')) }}</span>
                            @else
                                {{ number_format($classes->sum('existing_enrollment')) }}
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-blue-700">
                            @if ($hasEvening)
                                <span x-show="shift === 'both'">{{ number_format($grandTotal) }}</span>
                                <span x-show="shift === 'morning'" x-cloak>{{ number_format($classSummaryMorning->sum('total')) }}</span>
                                <span x-show="shift === 'evening'" x-cloak>{{ number_format($classSummaryEvening->sum('total')) }}</span>
                            @else
                                {{ number_format($grandTotal) }}
                            @endif
                        </td>
                        <td
                            class="px-3 py-3 text-center hidden md:table-cell {{ max(0, $classes->sum('total_seats') - $classes->sum('existing_enrollment') - $grandTotal) > 0 ? 'text-green-600' : 'text-red-500' }}">
                            @if ($hasEvening)
                                <span x-show="shift === 'both'">{{ number_format(max(0, $classes->sum('total_seats') - $classes->sum('existing_enrollment') - $grandTotal)) }}</span>
                                <span x-show="shift === 'morning'" x-cloak>{{ number_format(max(0, $classes->sum('morning_seats') - $classes->sum('morning_existing') - $classSummaryMorning->sum('total'))) }}</span>
                                <span x-show="shift === 'evening'" x-cloak>{{ number_format(max(0, $classes->sum('evening_seats') - $classes->sum('evening_existing') - $classSummaryEvening->sum('total'))) }}</span>
                            @else
                                {{ number_format(max(0, $classes->sum('total_seats') - $classes->sum('existing_enrollment') - $grandTotal)) }}
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-blue-900 bg-blue-100">
                            @if ($hasEvening)
                                <span x-show="shift === 'both'">{{ number_format($classes->sum('existing_enrollment') + $grandTotal) }}</span>
                                <span x-show="shift === 'morning'" x-cloak>{{ number_format($classes->sum('morning_existing') + $classSummaryMorning->sum('total')) }}</span>
                                <span x-show="shift === 'evening'" x-cloak>{{ number_format($classes->sum('evening_existing') + $classSummaryEvening->sum('total')) }}</span>
                            @else
                                {{ number_format($classes->sum('existing_enrollment') + $grandTotal) }}
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{--
    ── Admission Quota ────────────────────────────────────────────
    Hidden temporarily. Keep this block for future re-enable.

    @php
        $totalQuota = $classes->sum('admission_quota');
        $totalAdmitted = $classSummary->sum('total');
        $totalRemain = $classes->sum(function ($ic) use ($classSummary) {
            if (!$ic->admission_quota) {
                return 0;
            }
            return max(0, $ic->admission_quota - ($classSummary[$ic->class_id]?->total ?? 0));
        });
    @endphp

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">🎯 Admission Quota per Class</h3>
                <p class="text-xs text-gray-400 mt-0.5">Max new students planned for this academic year. Leave blank for no
                    limit.</p>
            </div>
            <div class="hidden sm:flex items-center gap-3 text-xs">
                <span class="px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 font-semibold">
                    Quota: {{ $totalQuota > 0 ? number_format($totalQuota) : '—' }}
                </span>
                <span class="px-3 py-1.5 rounded-full bg-orange-50 text-orange-600 font-semibold">
                    Admitted: {{ number_format($totalAdmitted) }}
                </span>
                <span
                    class="px-3 py-1.5 rounded-full {{ $totalQuota > 0 ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-400' }} font-semibold">
                    Remaining: {{ $totalQuota > 0 ? number_format($totalRemain) : '—' }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('fde.schools.save-quota', $institution) }}">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Class</th>
                            <th class="px-4 py-3 text-center">Admitted So Far</th>
                            <th class="px-4 py-3 text-center">
                                Admission Quota
                                <div class="text-gray-300 font-normal normal-case text-xs mt-0.5">max new this year</div>
                            </th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Remaining</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($classes as $i => $ic)
                            @php
                                $admitted = (int) ($classSummary[$ic->class_id]?->total ?? 0);
                                $quota = $ic->admission_quota;
                                $remaining = $quota !== null ? max(0, $quota - $admitted) : null;
                                $isOver = $quota !== null && $admitted > $quota;
                            @endphp
                            <tr class="hover:bg-gray-50 {{ $isOver ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    <input type="hidden" name="quota[{{ $i }}][class_id]"
                                        value="{{ $ic->class_id }}">
                                    {{ $ic->classModel?->name ?? "Class {$ic->class_id}" }}
                                    @if ($ic->classModel?->is_ece)
                                        <span
                                            class="ml-1 text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full">ECE</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="{{ $admitted > 0 ? ($isOver ? 'text-red-600 font-bold' : 'text-orange-600 font-semibold') : 'text-gray-400' }}">
                                        {{ $admitted > 0 ? number_format($admitted) : '—' }}
                                    </span>
                                    @if ($isOver)
                                        <div class="text-xs text-red-500 font-medium">
                                            {{ number_format($admitted - $quota) }} over quota</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" name="quota[{{ $i }}][quota]"
                                        value="{{ $quota }}" min="0" max="99999"
                                        placeholder="No limit"
                                        class="w-28 text-center border {{ $isOver ? 'border-red-300 bg-red-50' : 'border-gray-300' }} rounded-lg px-2 py-1.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                                </td>
                                <td class="px-4 py-3 text-center hidden sm:table-cell">
                                    @if ($quota === null)
                                        <span class="text-xs text-gray-400 italic">No limit</span>
                                    @elseif ($isOver)
                                        <span class="text-xs font-bold text-red-500">Over by
                                            {{ number_format($admitted - $quota) }}</span>
                                    @elseif ($remaining === 0)
                                        <span class="text-xs font-semibold text-red-500">Full</span>
                                    @else
                                        <span class="font-semibold text-green-600">{{ number_format($remaining) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-100 text-sm font-semibold">
                        <tr>
                            <td class="px-4 py-3 text-gray-600">TOTAL</td>
                            <td class="px-4 py-3 text-center text-orange-600">{{ number_format($totalAdmitted) }}</td>
                            <td class="px-4 py-3 text-center text-blue-900">
                                {{ $totalQuota > 0 ? number_format($totalQuota) : '—' }}</td>
                            <td class="px-4 py-3 text-center text-green-700 hidden sm:table-cell">
                                {{ $totalQuota > 0 ? number_format($totalRemain) : '—' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                <button type="submit"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    💾 Save Quotas
                </button>
            </div>
        </form>
    </div>
    --}}

    {{-- Day-by-Day --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Day-by-Day Breakdown</h3>
        </div>
        @if ($dailyRows->isEmpty())
            <div class="px-6 py-10 text-center text-gray-400 text-sm">No admissions in this date range.</div>
        @else
            <p class="block sm:hidden text-xs text-gray-400 mb-2 px-4 pt-2 flex items-center gap-1">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                Swipe right to see all columns
            </p>
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Date
                            </th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Class
                            </th>
                            <th
                                class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">
                                Reg Boys</th>
                            <th
                                class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">
                                Reg Girls</th>
                            <th
                                class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">
                                OOSC B</th>
                            <th
                                class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">
                                OOSC G</th>
                            <th
                                class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">
                                P2G B</th>
                            <th
                                class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">
                                P2G G</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($dailyRows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-gray-600">
                                    {{ $row->admission_date->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap font-medium text-gray-800">
                                    {{ $row->classModel?->name }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center text-blue-700 hidden sm:table-cell">
                                    {{ $row->morning_boys + $row->evening_boys }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center text-pink-700 hidden sm:table-cell">
                                    {{ $row->morning_girls + $row->evening_girls }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center text-purple-700 hidden md:table-cell">
                                    {{ $row->oosc_boys }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center text-pink-700 hidden md:table-cell">
                                    {{ $row->oosc_girls }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center text-orange-700 hidden md:table-cell">
                                    {{ $row->p2p_boys }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center text-pink-700 hidden md:table-cell">
                                    {{ $row->p2p_girls }}</td>
                                <td
                                    class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-center font-bold text-gray-900">
                                    {{ $row->displayTotal() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    @role('fde_cell')
        @if ($errors->has('confirmation'))
            {{-- Re-open the modal automatically if validation failed --}}
            <script>
                document.getElementById('resetModal').classList.remove('hidden');
            </script>
        @endif
    @endrole
@endpush
