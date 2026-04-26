@extends('layouts.app')
@section('title', 'UC Control Rooms')

@section('content')

    {{-- ── Page Header ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">📡 UC Control Rooms</h2>
            <p class="text-sm text-gray-500 mt-1">
                No Child Left Behind — Focal Persons Directory
            </p>
        </div>
        <a href="{{ route('uc.control-rooms.export-pdf', request()->only('search', 'org')) }}"
            class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            <span>⬇️</span> Export PDF
        </a>
    </div>

    {{-- ── Stats Row ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-blue-900">{{ $totalUcs }}</p>
            <p class="text-xs text-gray-500 mt-1">UCs in Directory</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-700">{{ $totalOrgs }}</p>
            <p class="text-xs text-gray-500 mt-1">Partner Organisations</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-orange-600">
                {{ $records->whereNotNull('fde_school_name')->count() }}
            </p>
            <p class="text-xs text-gray-500 mt-1">FDE Schools Listed</p>
        </div>
    </div>

    {{-- ── Filter Form ─────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('uc.control-rooms.index') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Search UC name, code, school, focal person…"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none" />
            </div>
            <div>
                <select name="org"
                    class="w-full sm:w-52 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none bg-white">
                    <option value="">All Organisations</option>
                    @foreach ($organizations as $o)
                        <option value="{{ $o }}" {{ $org === $o ? 'selected' : '' }}>
                            {{ $o }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="bg-blue-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                Filter
            </button>
            @if ($search || $org)
                <a href="{{ route('uc.control-rooms.index') }}"
                    class="px-5 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition text-center">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1 px-4 pt-3">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            Swipe right to see all columns
        </p>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-sm">
                <thead class="bg-blue-900 text-white text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">UC</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Organisation</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap hidden md:table-cell">Org Focal Person</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Contact</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">FDE School</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap hidden md:table-cell">FDE Focal</th>
                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($records as $rec)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-semibold text-blue-900 text-xs">
                                    {{ $rec->unionCouncil?->code ?? '—' }}
                                </span>
                                <div class="text-gray-500 text-xs leading-tight">
                                    {{ $rec->unionCouncil?->name ?? '' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span
                                    class="inline-block bg-blue-50 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                                    {{ $rec->organization_name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 text-xs hidden md:table-cell">{{ $rec->focal_person_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap font-mono">
                                {{ $rec->focal_person_contact ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700 text-xs">{{ $rec->fde_school_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs hidden md:table-cell">
                                <div class="text-gray-700">{{ $rec->fde_focal_person_name ?? '—' }}</div>
                                @if ($rec->fde_focal_person_contact)
                                    <div class="text-gray-500 font-mono text-xs">{{ $rec->fde_focal_person_contact }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('uc.control-rooms.show', $rec) }}"
                                    class="inline-block bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-semibold px-3 py-1 rounded-lg transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">
                                No UC control room records match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- row count footer --}}
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-500">
            Showing {{ $records->count() }} record(s)
            @if ($search || $org)
                — filtered
            @endif
        </div>
    </div>

@endsection
