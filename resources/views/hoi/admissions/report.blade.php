{{-- SAVE AS: resources/views/hoi/admissions/report.blade.php --}}

@extends('layouts.app')
@section('title', 'Admission Report — ' . $institution->name)

@section('content')

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Report</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->name }}
                @if ($academicYear)
                    <span class="mx-2 text-gray-300">·</span>
                    <span>{{ $academicYear->name }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Export buttons --}}
            <a href="{{ route('hoi.admissions.report.excel', request()->query()) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium bg-green-700 text-white hover:bg-green-600 transition flex items-center gap-1">
                📊 Excel
            </a>
            <a href="{{ route('hoi.admissions.report.pdf', request()->query()) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium bg-red-700 text-white hover:bg-red-600 transition flex items-center gap-1">
                📄 PDF
            </a>
            <a href="{{ route('hoi.admissions.daily') }}"
                class="px-4 py-2 rounded-lg text-sm font-medium bg-blue-900 text-white hover:bg-blue-800 transition">
                ← Back to Daily Entry
            </a>
        </div>
    </div>

    {{-- ── Date Filter ──────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('hoi.admissions.report') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit"
            class="px-6 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-800 transition">
            Apply
        </button>
        <a href="{{ route('hoi.admissions.report') }}"
            class="px-5 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">
            Reset
        </a>
        <span class="ml-auto self-center text-xs text-gray-400">
            {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
        </span>
    </form>

    {{-- ── Grand Total Cards ────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

        <div class="bg-blue-900 rounded-xl p-4 text-white text-center">
            <p class="text-xs text-blue-200 mb-1 uppercase tracking-wide">Total Admitted</p>
            <p class="text-3xl font-bold">{{ number_format($grandTotal) }}</p>
            <p class="text-xs text-blue-300 mt-1">All categories · all shifts</p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-blue-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1 uppercase tracking-wide">Regular</p>
            <p class="text-3xl font-bold text-blue-700">{{ number_format($grandRegular) }}</p>
            @if ($hasEvening)
                <p class="text-xs text-gray-400 mt-1">
                    🌅 {{ number_format($grandMorningRegular) }} · 🌆 {{ number_format($grandEveningRegular) }}
                </p>
            @endif
        </div>

        <div class="bg-white rounded-xl p-4 border border-purple-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1 uppercase tracking-wide">OOSC</p>
            <p class="text-3xl font-bold text-purple-700">{{ number_format($grandOosc) }}</p>
            @if ($hasEvening)
                <p class="text-xs text-gray-400 mt-1">
                    🌅 {{ number_format($grandMorningOosc) }} · 🌆 {{ number_format($grandEveningOosc) }}
                </p>
            @else
                <p class="text-xs text-gray-400 mt-1">Out-of-School Children</p>
            @endif
        </div>

        <div class="bg-white rounded-xl p-4 border border-orange-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 mb-1 uppercase tracking-wide">P2G</p>
            <p class="text-3xl font-bold text-orange-600">{{ number_format($grandP2p) }}</p>
            @if ($hasEvening)
                <p class="text-xs text-gray-400 mt-1">
                    🌅 {{ number_format($grandMorningP2p) }} · 🌆 {{ number_format($grandEveningP2p) }}
                </p>
            @else
                <p class="text-xs text-gray-400 mt-1">Private to Government</p>
            @endif
        </div>

    </div>

    @if ($hasMatricTech)
    <div class="bg-white rounded-xl p-4 border border-teal-200 shadow-sm mb-6 flex items-center gap-4">
        <div class="text-3xl">⚙️</div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Matric Tech Program (Class 9 &amp; 10)</p>
            <p class="text-2xl font-bold text-teal-700">{{ number_format($grandMatricTech) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Subset of Regular — already counted in total above</p>
        </div>
    </div>
    @endif


    {{-- ── Class-wise Summary ───────────────────────────────────────── --}}
    <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>Swipe right to see all columns
    </p>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800">Class-wise Enrollment Summary</h3>
            <p class="text-xs text-gray-400 mt-0.5">Cumulative for selected date range</p>
        </div>

        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase" rowspan="2">Class</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase" rowspan="2">Sec.</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-orange-600 uppercase bg-orange-50" rowspan="2">
                            Existing<br>Students</th>
                        {{-- NEW: Newly Admitted column --}}
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-800 uppercase bg-blue-50" rowspan="2">
                            Newly<br>Admitted</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase" rowspan="2">
                            Total<br>Seats</th>

                        {{-- HIDDEN: Seats Available column
                        <th class="px-3 py-3 text-center text-xs font-semibold text-green-600 uppercase bg-green-50"
                            rowspan="2">Seats<br>Avail.</th>
                        --}}

                        {{-- Regular (2 cols — Boys/Girls OR Morning/Evening) --}}
                        <th class="px-3 py-2 text-center text-xs font-semibold text-blue-700 uppercase bg-blue-50" colspan="2">
                            Regular New Admitted</th>

                        {{-- OOSC (2 cols) --}}
                        <th class="px-3 py-2 text-center text-xs font-semibold text-purple-700 uppercase bg-purple-50" colspan="2">
                            OOSC Out-of-School</th>

                        {{-- P2G (2 cols) --}}
                        <th class="px-3 py-2 text-center text-xs font-semibold text-orange-700 uppercase bg-orange-50" colspan="2">
                            P2G Private to Govt</th>

                        @if ($hasMatricTech)
                        <th class="px-3 py-2 text-center text-xs font-semibold text-teal-700 uppercase bg-teal-50" rowspan="2">
                            Matric<br>Tech ⚙️</th>
                        @endif

                        <th class="px-3 py-2 text-center text-xs font-semibold text-blue-900 uppercase bg-blue-100" rowspan="2">
                            Total</th>
                    </tr>
                    <tr class="text-xs text-gray-400 border-b border-gray-100">
                        {{-- Regular sub-headers: Morning/Evening for evening schools, Boys/Girls otherwise --}}
                        @if ($hasEvening)
                            <th class="px-3 py-1 text-center bg-blue-50 text-blue-500">🌅 Morning</th>
                            <th class="px-3 py-1 text-center bg-indigo-50 text-indigo-500">🌆 Evening</th>
                        @else
                            <th class="px-3 py-1 text-center bg-blue-50 text-blue-500">Boys</th>
                            <th class="px-3 py-1 text-center bg-blue-50 text-pink-500">Girls</th>
                        @endif

                        {{-- OOSC sub-headers --}}
                        @if ($hasEvening)
                            <th class="px-3 py-1 text-center bg-purple-50 text-blue-500">🌅 Morning</th>
                            <th class="px-3 py-1 text-center bg-purple-50 text-indigo-500">🌆 Evening</th>
                        @else
                            <th class="px-3 py-1 text-center bg-purple-50 text-blue-500">Boys</th>
                            <th class="px-3 py-1 text-center bg-purple-50 text-pink-500">Girls</th>
                        @endif

                        {{-- P2G sub-headers --}}
                        @if ($hasEvening)
                            <th class="px-3 py-1 text-center bg-orange-50 text-blue-500">🌅 Morning</th>
                            <th class="px-3 py-1 text-center bg-orange-50 text-indigo-500">🌆 Evening</th>
                        @else
                            <th class="px-3 py-1 text-center bg-orange-50 text-blue-500">Boys</th>
                            <th class="px-3 py-1 text-center bg-orange-50 text-pink-500">Girls</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50">
                    @foreach ($classes as $ic)
                        @php
                            $s        = $classSummary[$ic->class_id] ?? null;
                            $admitted = $s ? (int) $s->grand_total : 0;
                            // $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted); // HIDDEN
                            $totalEnrl = $ic->existing_enrollment + $admitted;
                            $secCount  = \App\Models\InstitutionSection::where('institution_id', $ic->institution_id)
                                             ->where('class_id', $ic->class_id)
                                             ->count() ?: 1;
                            // Combined boys/girls across both shifts
                            $regBoys   = ($s?->morning_boys ?? 0)      + ($s?->evening_boys ?? 0);
                            $regGirls  = ($s?->morning_girls ?? 0)     + ($s?->evening_girls ?? 0);
                            $ooscBoys  = ($s?->morning_oosc_boys ?? 0) + ($s?->evening_oosc_boys ?? 0);
                            $ooscGirls = ($s?->morning_oosc_girls ?? 0)+ ($s?->evening_oosc_girls ?? 0);
                            $p2pBoys   = ($s?->morning_p2p_boys ?? 0)  + ($s?->evening_p2p_boys ?? 0);
                            $p2pGirls  = ($s?->morning_p2p_girls ?? 0) + ($s?->evening_p2p_girls ?? 0);
                        @endphp
                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $ic->classModel?->name }}
                                @if ($ic->classModel?->is_ece)
                                    <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">ECE</span>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-center text-gray-600">{{ $secCount }}</td>

                            <td class="px-4 py-3 text-center text-orange-600 bg-orange-50">
                                {{ number_format($ic->existing_enrollment) }}
                                {{-- HIDDEN: promoted/failed sub-text (columns do not exist in DB)
                                @if ($ic->promoted_count + $ic->failed_count > 0)
                                    <div class="text-xs text-gray-400 font-normal mt-0.5">
                                        Promoted: <span class="text-green-600 font-semibold">{{ number_format($ic->promoted_count) }}</span>
                                        @if ($ic->failed_count > 0)
                                            &middot; Repeaters: <span class="text-red-500 font-semibold">{{ number_format($ic->failed_count) }}</span>
                                        @endif
                                    </div>
                                @endif
                                --}}
                            </td>

                            {{-- Newly Admitted (grand_total for selected period) --}}
                            <td class="px-3 py-3 text-center font-bold text-blue-800 bg-blue-50">
                                {{ number_format($admitted) }}
                            </td>

                            <td class="px-3 py-3 text-center font-medium text-gray-700">
                                {{ number_format($ic->total_seats) }}
                            </td>

                            {{-- HIDDEN: Seats Available
                            <td class="px-3 py-3 text-center bg-green-50">
                                <span class="font-bold {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ number_format($available) }}
                                </span>
                            </td>
                            --}}

                            {{-- Regular: Morning/Evening for evening schools, Boys/Girls otherwise --}}
                            @if ($hasEvening)
                                <td class="px-3 py-3 text-center text-blue-700 bg-blue-50">
                                    {{ number_format($s?->morning_regular ?? 0) }}</td>
                                <td class="px-3 py-3 text-center text-indigo-700 bg-indigo-50">
                                    {{ number_format($s?->evening_regular ?? 0) }}</td>
                            @else
                                <td class="px-3 py-3 text-center text-blue-700 bg-blue-50">
                                    {{ number_format($regBoys) }}</td>
                                <td class="px-3 py-3 text-center text-pink-700 bg-blue-50">
                                    {{ number_format($regGirls) }}</td>
                            @endif

                            {{-- OOSC: Morning/Evening for evening schools, Boys/Girls otherwise --}}
                            @if ($hasEvening)
                                <td class="px-3 py-3 text-center text-purple-700 bg-purple-50">
                                    {{ number_format($s?->morning_oosc ?? 0) }}</td>
                                <td class="px-3 py-3 text-center text-purple-600 bg-purple-50">
                                    {{ number_format($s?->evening_oosc ?? 0) }}</td>
                            @else
                                <td class="px-3 py-3 text-center text-purple-700 bg-purple-50">
                                    {{ number_format($ooscBoys) }}</td>
                                <td class="px-3 py-3 text-center text-purple-600 bg-purple-50">
                                    {{ number_format($ooscGirls) }}</td>
                            @endif

                            {{-- P2G: Morning/Evening for evening schools, Boys/Girls otherwise --}}
                            @if ($hasEvening)
                                <td class="px-3 py-3 text-center text-orange-700 bg-orange-50">
                                    {{ number_format($s?->morning_p2p ?? 0) }}</td>
                                <td class="px-3 py-3 text-center text-orange-600 bg-orange-50">
                                    {{ number_format($s?->evening_p2p ?? 0) }}</td>
                            @else
                                <td class="px-3 py-3 text-center text-orange-700 bg-orange-50">
                                    {{ number_format($p2pBoys) }}</td>
                                <td class="px-3 py-3 text-center text-orange-600 bg-orange-50">
                                    {{ number_format($p2pGirls) }}</td>
                            @endif

                            {{-- Matric Tech (only for matric tech schools, only Class 9 & 10) --}}
                            @if ($hasMatricTech)
                                @php $isMatricClass = in_array($ic->classModel?->order, [9, 10]); @endphp
                                <td class="px-3 py-3 text-center bg-teal-50">
                                    @if ($isMatricClass)
                                        <span class="font-semibold text-teal-700">
                                            {{ number_format((int)($s?->matric_tech_count ?? 0)) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            @endif

                            {{-- Total = grand_total (same as Newly Admitted) --}}
                            <td class="px-3 py-3 text-center font-bold text-blue-900 bg-blue-100">
                                {{ number_format($admitted) }}
                            </td>

                        </tr>
                    @endforeach
                </tbody>

                <tfoot class="bg-blue-900 text-white font-bold text-sm">
                    <tr>
                        <td class="px-4 py-3" colspan="2">GRAND TOTAL</td>
                        <td class="px-3 py-3 text-center bg-orange-800">
                            {{ number_format($classes->sum('existing_enrollment')) }}</td>
                        {{-- Newly Admitted total --}}
                        <td class="px-3 py-3 text-center">{{ number_format($grandTotal) }}</td>
                        <td class="px-3 py-3 text-center">{{ number_format($classes->sum('total_seats')) }}</td>

                        {{-- HIDDEN: Seats Available footer
                        <td class="px-3 py-3 text-center">
                            @php $totalAvail = max(0, $classes->sum('total_seats') - $classes->sum('existing_enrollment') - $grandTotal); @endphp
                            {{ number_format($totalAvail) }}
                        </td>
                        --}}

                        {{-- Regular totals --}}
                        @if ($hasEvening)
                            <td class="px-3 py-3 text-center">{{ number_format($grandMorningRegular) }}</td>
                            <td class="px-3 py-3 text-center">{{ number_format($grandEveningRegular) }}</td>
                        @else
                            <td class="px-3 py-3 text-center">
                                {{ number_format($classSummary->sum('morning_boys') + $classSummary->sum('evening_boys')) }}</td>
                            <td class="px-3 py-3 text-center">
                                {{ number_format($classSummary->sum('morning_girls') + $classSummary->sum('evening_girls')) }}</td>
                        @endif

                        {{-- OOSC totals --}}
                        @if ($hasEvening)
                            <td class="px-3 py-3 text-center bg-purple-800">{{ number_format($grandMorningOosc) }}</td>
                            <td class="px-3 py-3 text-center bg-purple-800">{{ number_format($grandEveningOosc) }}</td>
                        @else
                            <td class="px-3 py-3 text-center bg-purple-800">
                                {{ number_format($classSummary->sum('morning_oosc_boys') + $classSummary->sum('evening_oosc_boys')) }}</td>
                            <td class="px-3 py-3 text-center bg-purple-800">
                                {{ number_format($classSummary->sum('morning_oosc_girls') + $classSummary->sum('evening_oosc_girls')) }}</td>
                        @endif

                        {{-- P2G totals --}}
                        @if ($hasEvening)
                            <td class="px-3 py-3 text-center bg-orange-800">{{ number_format($grandMorningP2p) }}</td>
                            <td class="px-3 py-3 text-center bg-orange-800">{{ number_format($grandEveningP2p) }}</td>
                        @else
                            <td class="px-3 py-3 text-center bg-orange-800">
                                {{ number_format($classSummary->sum('morning_p2p_boys') + $classSummary->sum('evening_p2p_boys')) }}</td>
                            <td class="px-3 py-3 text-center bg-orange-800">
                                {{ number_format($classSummary->sum('morning_p2p_girls') + $classSummary->sum('evening_p2p_girls')) }}</td>
                        @endif

                        @if ($hasMatricTech)
                        <td class="px-3 py-3 text-center bg-teal-800">{{ number_format($grandMatricTech) }}</td>
                        @endif

                        <td class="px-3 py-3 text-center bg-blue-800">{{ number_format($grandTotal) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>


    {{-- ── Day-by-Day Breakdown ─────────────────────────────────────── --}}
    <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>Swipe right to see all columns
    </p>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800">Day-by-Day Breakdown</h3>
            <p class="text-xs text-gray-400 mt-0.5">All entries for the selected date range</p>
        </div>

        @if ($dailyRows->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-gray-400 text-sm">No admissions recorded for this period.</p>
                <a href="{{ route('hoi.admissions.daily') }}"
                    class="inline-block mt-3 text-sm text-blue-600 hover:underline">Enter today's admissions →</a>
            </div>
        @else
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold" rowspan="2">Date</th>
                            <th class="px-4 py-3 text-left font-semibold" rowspan="2">Class</th>
                            {{-- Regular --}}
                            @if ($hasEvening)
                                <th class="px-3 py-2 text-center font-semibold bg-blue-50 text-blue-700" colspan="2">🌅 Morning Reg.</th>
                                <th class="px-3 py-2 text-center font-semibold bg-indigo-50 text-indigo-700" colspan="2">🌆 Evening Reg.</th>
                            @else
                                <th class="px-3 py-2 text-center font-semibold bg-blue-50 text-blue-700" colspan="2">Regular</th>
                            @endif

                            {{-- OOSC --}}
                            @if ($hasEvening)
                                <th class="px-3 py-2 text-center font-semibold bg-purple-50 text-purple-700" colspan="2">🌅 Morning OOSC</th>
                                <th class="px-3 py-2 text-center font-semibold bg-purple-50 text-purple-600" colspan="2">🌆 Evening OOSC</th>
                            @else
                                <th class="px-3 py-2 text-center font-semibold bg-purple-50 text-purple-700" colspan="2">OOSC</th>
                            @endif

                            {{-- P2G --}}
                            @if ($hasEvening)
                                <th class="px-3 py-2 text-center font-semibold bg-orange-50 text-orange-700" colspan="2">🌅 Morning P2G</th>
                                <th class="px-3 py-2 text-center font-semibold bg-orange-50 text-orange-600" colspan="2">🌆 Evening P2G</th>
                            @else
                                <th class="px-3 py-2 text-center font-semibold bg-orange-50 text-orange-700" colspan="2">P2G</th>
                            @endif

                            @if ($hasMatricTech)
                            <th class="px-3 py-2 text-center font-semibold bg-teal-50 text-teal-700" rowspan="2">Matric<br>Tech ⚙️</th>
                            @endif

                            <th class="px-3 py-2 text-center font-semibold bg-blue-100 text-blue-900" rowspan="2">Total</th>
                            <th class="px-3 py-2 text-center font-semibold text-gray-500" rowspan="2">Status</th>
                        </tr>
                        <tr class="text-xs text-gray-400 border-b border-gray-100">
                            {{-- Regular sub-headers --}}
                            <th class="px-3 py-1 text-center bg-blue-50 text-blue-500">Boys</th>
                            <th class="px-3 py-1 text-center bg-blue-50 text-pink-500">Girls</th>
                            @if ($hasEvening)
                                <th class="px-3 py-1 text-center bg-indigo-50 text-blue-500">Boys</th>
                                <th class="px-3 py-1 text-center bg-indigo-50 text-pink-500">Girls</th>
                            @endif
                            {{-- OOSC sub-headers --}}
                            <th class="px-3 py-1 text-center bg-purple-50 text-blue-500">Boys</th>
                            <th class="px-3 py-1 text-center bg-purple-50 text-pink-500">Girls</th>
                            @if ($hasEvening)
                                <th class="px-3 py-1 text-center bg-purple-50 text-blue-500">Boys</th>
                                <th class="px-3 py-1 text-center bg-purple-50 text-pink-500">Girls</th>
                            @endif
                            {{-- P2G sub-headers --}}
                            <th class="px-3 py-1 text-center bg-orange-50 text-blue-500">Boys</th>
                            <th class="px-3 py-1 text-center bg-orange-50 text-pink-500">Girls</th>
                            @if ($hasEvening)
                                <th class="px-3 py-1 text-center bg-orange-50 text-blue-500">Boys</th>
                                <th class="px-3 py-1 text-center bg-orange-50 text-pink-500">Girls</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($dailyRows as $row)
                            @php
                                $rowTotal =
                                    $row->morning_boys + $row->morning_girls +
                                    $row->evening_boys + $row->evening_girls +
                                    $row->morning_oosc_boys + $row->morning_oosc_girls +
                                    $row->evening_oosc_boys + $row->evening_oosc_girls +
                                    $row->morning_p2p_boys + $row->morning_p2p_girls +
                                    $row->evening_p2p_boys + $row->evening_p2p_girls;
                                // Combined across shifts
                                $dRegBoys   = $row->morning_boys       + $row->evening_boys;
                                $dRegGirls  = $row->morning_girls      + $row->evening_girls;
                                $dOoscBoys  = $row->morning_oosc_boys  + $row->evening_oosc_boys;
                                $dOoscGirls = $row->morning_oosc_girls + $row->evening_oosc_girls;
                                $dP2pBoys   = $row->morning_p2p_boys   + $row->evening_p2p_boys;
                                $dP2pGirls  = $row->morning_p2p_girls  + $row->evening_p2p_girls;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2.5 whitespace-nowrap text-gray-600">
                                    {{ \Carbon\Carbon::parse($row->admission_date)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-2.5 font-medium text-gray-800">
                                    {{ $row->classModel?->name }}
                                </td>

                                {{-- Regular --}}
                                <td class="px-3 py-2.5 text-center text-blue-700 bg-blue-50">{{ $row->morning_boys }}</td>
                                <td class="px-3 py-2.5 text-center text-pink-700 bg-blue-50">{{ $row->morning_girls }}</td>
                                @if ($hasEvening)
                                    <td class="px-3 py-2.5 text-center text-blue-700 bg-indigo-50">{{ $row->evening_boys }}</td>
                                    <td class="px-3 py-2.5 text-center text-pink-700 bg-indigo-50">{{ $row->evening_girls }}</td>
                                @endif

                                {{-- OOSC --}}
                                <td class="px-3 py-2.5 text-center text-purple-700 bg-purple-50">{{ $row->morning_oosc_boys }}</td>
                                <td class="px-3 py-2.5 text-center text-purple-600 bg-purple-50">{{ $row->morning_oosc_girls }}</td>
                                @if ($hasEvening)
                                    <td class="px-3 py-2.5 text-center text-purple-700 bg-purple-50">{{ $row->evening_oosc_boys }}</td>
                                    <td class="px-3 py-2.5 text-center text-purple-600 bg-purple-50">{{ $row->evening_oosc_girls }}</td>
                                @endif

                                {{-- P2G --}}
                                <td class="px-3 py-2.5 text-center text-orange-700 bg-orange-50">{{ $row->morning_p2p_boys }}</td>
                                <td class="px-3 py-2.5 text-center text-orange-600 bg-orange-50">{{ $row->morning_p2p_girls }}</td>
                                @if ($hasEvening)
                                    <td class="px-3 py-2.5 text-center text-orange-700 bg-orange-50">{{ $row->evening_p2p_boys }}</td>
                                    <td class="px-3 py-2.5 text-center text-orange-600 bg-orange-50">{{ $row->evening_p2p_girls }}</td>
                                @endif

                                @if ($hasMatricTech)
                                <td class="px-3 py-2.5 text-center text-teal-700 bg-teal-50">
                                    {{ $row->matric_tech_count > 0 ? $row->matric_tech_count : '—' }}
                                </td>
                                @endif

                                <td class="px-3 py-2.5 text-center font-bold text-blue-900 bg-blue-100">
                                    {{ $rowTotal }}</td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $row->statusBadgeClass() }}">
                                        {{ $row->statusLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
