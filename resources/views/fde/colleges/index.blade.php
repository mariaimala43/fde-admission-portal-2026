@extends('layouts.app')
@section('title', $type === 'Model College' ? 'Model Colleges' : 'Ex-FG Colleges')

@section('content')

    @php
        $isModel = $type === 'Model College';
        $titleLabel = $isModel ? 'Model Colleges' : 'Ex-FG Colleges';
        $emoji = $isModel ? '🏛️' : '🎓';
        $typeSlug = $isModel ? 'model' : 'ex-fg';
        $routeIndex = $isModel ? route('fde.colleges.model') : route('fde.colleges.ex-fg');
        $exportUrl =
            route('fde.colleges.export-pdf', $typeSlug) .
            (request()->query() ? '?' . http_build_query(request()->query()) : '');
    @endphp

    @if ($isModel)

    {{-- ── Header ────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $emoji }} {{ $titleLabel }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $academicYear?->name ?? 'Active Year' }} &middot; {{ $totalColleges }} colleges
            </p>
        </div>
        <a href="{{ $exportUrl }}"
            class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            ⬇️ Export PDF
        </a>
    </div>

    {{-- ── Summary Cards ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <p class="text-2xl font-bold text-blue-900">{{ $totalColleges }}</p>
            <p class="text-xs text-gray-400 uppercase tracking-wider mt-1">Total Colleges</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm p-5 text-center">
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalAdmitted) }}</p>
            <p class="text-xs text-gray-400 uppercase tracking-wider mt-1">Total Admitted</p>
        </div>
        <div class="bg-white rounded-xl border border-sky-100 shadow-sm p-5 text-center">
            <p class="text-2xl font-bold text-sky-700">{{ number_format($totalBoys) }}</p>
            <p class="text-xs text-gray-400 uppercase tracking-wider mt-1">Boys</p>
        </div>
        <div class="bg-white rounded-xl border border-pink-100 shadow-sm p-5 text-center">
            <p class="text-2xl font-bold text-pink-600">{{ number_format($totalGirls) }}</p>
            <p class="text-xs text-gray-400 uppercase tracking-wider mt-1">Girls</p>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ $routeIndex }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="College name…"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sector</label>
                <select name="sector_id"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-300 outline-none">
                    <option value="">All Sectors</option>
                    @foreach ($sectors as $sec)
                        <option value="{{ $sec->id }}" {{ $sectorId == $sec->id ? 'selected' : '' }}>
                            {{ $sec->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Union Council</label>
                <select name="uc_id"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-300 outline-none">
                    <option value="">All UCs</option>
                    @foreach ($ucs as $uc)
                        <option value="{{ $uc->id }}" {{ $ucId == $uc->id ? 'selected' : '' }}>
                            {{ $uc->code }} — {{ $uc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Filter
                </button>
                @if ($search || $sectorId || $ucId)
                    <a href="{{ $routeIndex }}"
                        class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50 transition text-center">
                        Clear
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ── Table ──────────────────────────────────────────────────────────── --}}
    <p class="block md:hidden text-xs text-gray-500 mb-2">Scroll right to see all columns, or view on a larger screen for full detail.</p>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-blue-900 text-white text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold hidden md:table-cell">#</th>
                        <th class="px-4 py-3 text-left font-semibold max-w-[180px] min-w-[140px]">College Name</th>
                        <th class="px-4 py-3 text-left font-semibold hidden md:table-cell">EMIS</th>
                        <th class="px-4 py-3 text-left font-semibold hidden md:table-cell">IB No.</th>
                        <th class="px-4 py-3 text-left font-semibold">Gender</th>
                        <th class="px-4 py-3 text-left font-semibold hidden md:table-cell">UC / Sector</th>
                        <th class="px-4 py-3 text-left font-semibold hidden lg:table-cell">Current HOI</th>
                        <th class="px-4 py-3 text-left font-semibold hidden md:table-cell">Contact</th>
                        <th class="px-4 py-3 text-center font-semibold">Total Admitted</th>
                        <th class="px-4 py-3 text-center font-semibold hidden md:table-cell">Boys</th>
                        <th class="px-4 py-3 text-center font-semibold hidden md:table-cell">Girls</th>
                        <th class="px-4 py-3 text-center font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($institutions as $i => $inst)
                        @php
                            $hoi = $inst->users->first(fn($u) => $u->hasRole('hoi') && $u->is_active);
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-400 text-xs hidden md:table-cell">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 max-w-[180px]">
                                <a href="{{ route('fde.colleges.profile', $inst) }}"
                                    class="font-semibold text-gray-800 hover:text-blue-700 hover:underline truncate block max-w-[180px]"
                                    title="{{ $inst->name }}">
                                    {{ $inst->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600 hidden md:table-cell">{{ $inst->code ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600 hidden md:table-cell">{{ $inst->ib_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs">
                                @if ($inst->gender === 'boys')
                                    <span class="text-sky-700 font-semibold">♂ Boys</span>
                                @elseif ($inst->gender === 'girls')
                                    <span class="text-pink-600 font-semibold">♀ Girls</span>
                                @else
                                    <span class="text-gray-500">Co-Ed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 hidden md:table-cell">
                                <div>{{ $inst->unionCouncil?->code ?? '—' }}</div>
                                <div class="text-gray-400">{{ $inst->sector?->name ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 hidden lg:table-cell">{{ $hoi?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs font-mono text-gray-600 hidden md:table-cell">{{ $hoi?->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-bold text-green-700">
                                {{ number_format($inst->total_admitted) }}
                            </td>
                            <td class="px-4 py-3 text-center text-sky-700 font-semibold text-xs hidden md:table-cell">
                                {{ number_format($inst->total_boys) }}
                            </td>
                            <td class="px-4 py-3 text-center text-pink-600 font-semibold text-xs hidden md:table-cell">
                                {{ number_format($inst->total_girls) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('fde.colleges.profile', $inst) }}"
                                    class="inline-block bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-semibold px-3 py-1 rounded-lg transition">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-12 text-center text-gray-400 text-sm">
                                No {{ strtolower($titleLabel) }} found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-500">
            {{ $institutions->count() }} college(s) shown
            @if ($search || $sectorId || $ucId)
                — filtered
            @endif
        </div>
    </div>

    @else

    {{-- ── Ex-FG Reference Table (information only) ───────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-blue-900">
                <h3 class="text-base font-bold text-white uppercase tracking-wide">EX FG College Head of Institution Information</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-center">S.No.</th>
                            <th class="px-4 py-3 text-center">EMIS</th>
                            <th class="px-4 py-3 text-left">IB</th>
                            <th class="px-4 py-3 text-left">Name of Institution</th>
                            <th class="px-4 py-3 text-left">Name of Principal</th>
                            <th class="px-4 py-3 text-left">Contact No</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ([
                            [1,  '802', 'IB-2524', 'IMPC, H-8',                      'Prof. Atthar ul Islam',           '0332-7942808'],
                            [2,  '805', 'IB-2869', 'IMPCC, H-8/4',                   'Prof. Dr. Muhammad Khalid',       '0333-5511561'],
                            [3,  '804', 'IB-2522', 'IMCB, H-9',                      'Prof. Muhammad Javed Iqbal',      '0300-9780372'],
                            [4,  '803', 'IB-2520', 'IMCB, F-10/4',                   'Mr. Muhammad Rashid',             '0321-5106044'],
                            [5,  '801', 'IB-2765', 'IMCB, Sihala',                   'Mr. Zahoor Ahmed',                '0334-5856956'],
                            [6,  '806', 'IB-2768', 'IMCG (PG) F-7/2',               'Prof. Dr. Fouzia Tanveer Sheikh', '0333-5107474'],
                            [7,  '810', 'IB-2527', 'IMCG (PG) F-7/4',               'Ms. Ayesha Kiyani',               '0307-5555415'],
                            [8,  '807', 'IB-2523', 'IMCG (PG) G-10/4',              'Prof. Sadia Ibrar',               '0334-5164710'],
                            [9,  '809', 'IB-5147', 'IMCG, I-8/3',                   'Ms. Najam Un Nisa',               '0333-3601098'],
                            [10, '811', 'IB-1583', 'IMCG, I-14/3',                  'Ms. Shazia Wazir',                '0332-5137674'],
                            [11, '808', 'IB-2541', 'IMCG, Humak',                   'Dr. Humaira Jabeen',              '0312-5281522'],
                            [12, '812', 'IB-2766', 'IMCG, Bharakau',                'Ms. Abida Parveen',               '0333-7241916'],
                            [13, '813', 'IB-2835', 'Home Economics College F-11/1', 'Prof. Rozina Faheem',             '0300-5098602'],
                        ] as [$sno, $emis, $ib, $instName, $principal, $contact])
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $sno }}</td>
                                <td class="px-4 py-3 text-center font-mono text-xs text-gray-600">{{ $emis }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $ib }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $instName }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $principal }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $contact }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif

@endsection
