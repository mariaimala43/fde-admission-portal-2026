@extends('layouts.app')
@section('title', 'UC Control Room — ' . ($ucControlRoom->unionCouncil?->code ?? 'Detail'))

@section('content')

    {{-- ── Back + Title ─────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('uc.control-rooms.index') }}" class="text-gray-400 hover:text-gray-600 transition text-sm">
            ← Back
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                📡 {{ $ucControlRoom->unionCouncil?->code ?? 'UC' }}
                — {{ $ucControlRoom->unionCouncil?->name ?? '' }}
            </h2>
            @if ($ucControlRoom->unionCouncil?->sector)
                <p class="text-sm text-gray-500 mt-0.5">
                    Sector: <strong>{{ $ucControlRoom->unionCouncil->sector->name }}</strong>
                </p>
            @endif
        </div>
    </div>

    <div class="space-y-5">

        {{-- ── Section 1: Organisation ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-blue-900 text-white px-5 py-3">
                <h3 class="text-sm font-bold tracking-wide uppercase">Partner Organisation</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Organisation Name</p>
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $ucControlRoom->organization_name ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Focal Person</p>
                    <p class="text-sm text-gray-700">{{ $ucControlRoom->focal_person_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Contact</p>
                    <p class="text-sm font-mono text-gray-700">{{ $ucControlRoom->focal_person_contact ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- ── Section 3: FDE School ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-emerald-700 text-white px-5 py-3">
                <h3 class="text-sm font-bold tracking-wide uppercase">FDE School Focal Person</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">School Name</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $ucControlRoom->fde_school_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Focal Person / HOI</p>
                    <p class="text-sm text-gray-700">{{ $ucControlRoom->fde_focal_person_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Contact</p>
                    <p class="text-sm font-mono text-gray-700">{{ $ucControlRoom->fde_focal_person_contact ?? '—' }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Footer Actions ────────────────────────────────────────────────────── --}}
    <div class="mt-6 flex gap-3">
        <a href="{{ route('uc.control-rooms.index') }}"
            class="px-6 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
            ← Back to List
        </a>
        <a href="{{ route('uc.control-rooms.export-pdf') }}"
            class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            ⬇️ Export All PDF
        </a>
    </div>

@endsection
