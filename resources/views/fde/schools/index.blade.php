@extends('layouts.app')
@section('title', 'All Schools Report')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">All Schools</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $institutions->count() }} schools shown</p>
        </div>
        <a href="{{ route('fde.dashboard') }}" class="text-sm text-blue-600 hover:underline">
            ← Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('fde.schools.index') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sector</label>
                <select name="sector_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Sectors</option>
                    @foreach ($sectors as $sector)
                        <option value="{{ $sector->id }}" {{ $sectorId == $sector->id ? 'selected' : '' }}>
                            {{ $sector->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">School Type</label>
                <select name="type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach (['I-V', 'I-VIII', 'I-X', 'I-XII', 'VI-VIII', 'VI-X', 'VI-XII', 'Model_College'] as $t)
                        <option value="{{ $t }}" {{ $type == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Gender</label>
                <select name="gender"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="boys" {{ $gender == 'boys' ? 'selected' : '' }}>Boys</option>
                    <option value="girls" {{ $gender == 'girls' ? 'selected' : '' }}>Girls</option>
                    <option value="co_education" {{ $gender == 'co_education' ? 'selected' : '' }}>Co-Education</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 bg-blue-900 text-white px-4 py-2 rounded-lg text-sm
                           font-medium hover:bg-blue-800 transition">
                    Filter
                </button>
                <a href="{{ route('fde.schools.index') }}"
                    class="px-4 py-2 rounded-lg text-sm border border-gray-300
                      text-gray-600 hover:bg-gray-50 transition">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Schools Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                <tr>
                    <th class="px-4 py-3 text-left">School</th>
                    <th class="px-4 py-3 text-left">Sector</th>
                    <th class="px-4 py-3 text-center">Type</th>
                    <th class="px-4 py-3 text-center">Gender</th>
                    <th class="px-4 py-3 text-center">Total Seats</th>
                    <th class="px-4 py-3 text-center">Enrolled</th>
                    <th class="px-4 py-3 text-center text-blue-600">Regular</th>
                    <th class="px-4 py-3 text-center text-purple-600">OOSC</th>
                    <th class="px-4 py-3 text-center text-orange-600">P2P</th>
                    <th class="px-4 py-3 text-center font-bold text-gray-700">Total Admitted</th>
                    <th class="px-4 py-3 text-center text-green-600">Available</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($institutions as $inst)
                    @php
                        $adm = $admissionSummary[$inst->id] ?? null;
                        $seat = $seatSummary[$inst->id] ?? null;
                        $seats = $seat?->seats ?? 0;
                        $enrolled = $seat?->enrolled ?? 0;
                        $admitted = $adm?->total ?? 0;
                        $available = max(0, $seats - $enrolled - $admitted);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $inst->name }}
                            @if ($inst->is_cambridge)
                                <span class="ml-1 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">CAM</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $inst->sector?->name }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $inst->type }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-600">
                            {{ ucfirst(str_replace('_', ' ', $inst->gender)) }}
                        </td>
                        <td class="px-4 py-3 text-center font-medium text-gray-700">
                            {{ number_format($seats) }}
                        </td>
                        <td class="px-4 py-3 text-center text-orange-600 font-medium">
                            {{ number_format($enrolled) }}
                        </td>
                        <td class="px-4 py-3 text-center text-blue-700">
                            {{ number_format($adm?->regular ?? 0) }}
                        </td>
                        <td class="px-4 py-3 text-center text-purple-700">
                            {{ number_format($adm?->oosc ?? 0) }}
                        </td>
                        <td class="px-4 py-3 text-center text-orange-700">
                            {{ number_format($adm?->p2p ?? 0) }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-gray-900">
                            {{ number_format($admitted) }}
                        </td>
                        <td
                            class="px-4 py-3 text-center font-bold
                    {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format($available) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $inst->admission_status === 'open'
                            ? 'bg-green-100 text-green-700'
                            : ($inst->admission_status === 'closed'
                                ? 'bg-red-100 text-red-700'
                                : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst(str_replace('_', ' ', $inst->admission_status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('fde.schools.show', $inst) }}"
                                class="text-xs text-blue-600 hover:underline font-medium">
                                View →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="px-4 py-10 text-center text-gray-400">
                            No schools found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
