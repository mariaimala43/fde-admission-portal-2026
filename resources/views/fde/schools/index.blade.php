@extends('layouts.app')
@section('title', 'All Schools Report')
@section('content')

@php
    $isDirector   = auth()->user()->hasRole('director');
    $indexRoute   = $isDirector ? route('director.schools.index') : route('fde.schools.index');
    $showRouteFn  = fn($inst) => $isDirector ? route('director.schools.show', $inst) : route('fde.schools.show', $inst);
@endphp

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">All Schools</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institutions->total() }} schools found
                · showing {{ $institutions->firstItem() }}–{{ $institutions->lastItem() }}
            </p>
        </div>
        @role('director')
            <a href="{{ route('director.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Dashboard</a>
        @else
            <a href="{{ route('fde.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Dashboard</a>
        @endrole
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ $indexRoute }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">

        {{-- Search by name --}}
        <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Search School</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Type school name or code…"
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sector</label>
                <select name="sector_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Sectors</option>
                    <option value="model_colleges" {{ ($sectorId ?? '') === 'model_colleges' ? 'selected' : '' }}>
                        🎓 All Model Colleges
                    </option>
                    <option disabled>──────────────</option>
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
                    @foreach (['I-V', 'I-VIII', 'I-X', 'I-XII', 'VI-VIII', 'VI-X', 'VI-XII', 'XI-XII', 'XI-XIV', 'Model College'] as $t)
                        <option value="{{ $t }}" {{ $type == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Class</label>
                <select name="class_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Classes</option>
                    @foreach ($allClasses as $cls)
                        <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }}
                        </option>
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
                <a href="{{ $indexRoute }}"
                    class="px-4 py-2 rounded-lg text-sm border border-gray-300
                      text-gray-600 hover:bg-gray-50 transition">
                    Reset
                </a>
            </div>
        </div>

        {{-- Facility checkboxes --}}
        <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-4">
            <span class="text-xs font-semibold text-gray-400 uppercase self-center">Facilities:</span>
            @foreach ([
            'has_transport' => '🚌 Transport',
            'has_meal_program' => '🍱 Meal Program',
            'has_matric_tech' => '⚙️ Matric Tech',
            'is_cambridge' => '🎓 Cambridge',
            'has_ece' => '👶 ECE',
        ] as $key => $label)
                <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="{{ $key }}" value="1" {{ request($key) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 rounded" />
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </form>

    {{-- Schools Table --}}
    <p class="block sm:hidden text-xs text-gray-400 mb-2 flex items-center gap-1">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        Swipe right to see all columns
    </p>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto -mx-4 sm:mx-0 [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-400 [&::-webkit-scrollbar-thumb]:rounded-full">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                    <tr>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">EMIS</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">School</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Sector</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Type</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Gender</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Total Seats</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden lg:table-cell">Enrolled</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Regular</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">OOSC</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">P2G</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Total Admitted</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden sm:table-cell">Available</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left hidden md:table-cell">Facilities</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left">Status</th>
                        <th class="px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide text-left"></th>
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
                            <td class="px-3 py-3 whitespace-nowrap text-xs font-mono text-gray-500">
                                {{ $inst->code ?? '—' }}
                            </td>
                            <td class="px-3 py-3 max-w-[128px] sm:max-w-none">
                                <div class="truncate font-medium text-gray-900 max-w-[120px] sm:max-w-none"
                                    title="{{ $inst->name }}">
                                    <a href="{{ $showRouteFn($inst) }}"
                                       class="hover:text-blue-600 hover:underline">{{ $inst->name }}</a>
                                </div>
                                @if ($inst->is_cambridge)
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">CAM</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell">{{ $inst->sector?->name }}</td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell">{{ $inst->type }}</td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell">
                                {{ ucfirst(str_replace('_', ' ', $inst->gender)) }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden sm:table-cell">
                                {{ number_format($seats) }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-orange-600 hidden lg:table-cell">
                                {{ number_format($enrolled) }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-blue-700 hidden md:table-cell">
                                {{ number_format($adm?->regular ?? 0) }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-purple-700 hidden md:table-cell">
                                {{ number_format($adm?->oosc ?? 0) }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap text-orange-700 hidden md:table-cell">
                                {{ number_format($adm?->p2p ?? 0) }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap font-bold">
                                {{ number_format($admitted) }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap font-bold hidden sm:table-cell
                                {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ number_format($available) }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap hidden md:table-cell">
                                <div class="flex flex-wrap gap-1 justify-center">
                                    @if ($inst->has_transport)
                                        <span class="text-base" title="Transport">🚌</span>
                                    @endif
                                    @if ($inst->has_meal_program)
                                        <span class="text-base" title="Meal Program">🍱</span>
                                    @endif
                                    @if ($inst->has_matric_tech)
                                        <span class="text-base" title="Matric Tech">⚙️</span>
                                    @endif
                                    @if ($inst->has_ece)
                                        <span class="text-base" title="ECE">👶</span>
                                    @endif
                                    @if ($inst->is_cambridge)
                                        <span class="text-base" title="Cambridge">🎓</span>
                                    @endif
                                    @if (
                                        !$inst->has_transport &&
                                            !$inst->has_meal_program &&
                                            !$inst->has_matric_tech &&
                                            !$inst->has_ece &&
                                            !$inst->is_cambridge)
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
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
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap">
                                <a href="{{ $showRouteFn($inst) }}" title="View"
                                    class="inline-flex items-center gap-1 px-2 py-1.5 sm:px-3 text-xs sm:text-sm rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span class="hidden sm:inline">View</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-4 py-10 text-center text-gray-400">
                                No schools found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($institutions->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $institutions->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
