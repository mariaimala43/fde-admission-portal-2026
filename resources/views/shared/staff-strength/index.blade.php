@extends('layouts.app')
@section('title', 'Staff Strength Registers')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Staff Strength Registers</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $academicYear?->name ?? 'Active Year' }} · Read-only view
            </p>
        </div>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────────────── --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        @if(isset($sectors))
        <select name="sector_id"
            class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">All Sectors</option>
            @foreach($sectors as $sector)
                <option value="{{ $sector->id }}" {{ request('sector_id') == $sector->id ? 'selected' : '' }}>
                    {{ $sector->name }}
                </option>
            @endforeach
        </select>
        @endif

        <select name="status"
            class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">All Statuses</option>
            <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
            <option value="locked"    {{ request('status') === 'locked'    ? 'selected' : '' }}>Locked</option>
        </select>

        <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
            Filter
        </button>
        <a href="{{ request()->url() }}"
            class="text-sm text-gray-500 px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50">
            Clear
        </a>
    </form>

    {{-- ── Table ────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Institution</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Sector</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Submitted At</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($registers as $register)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3 font-medium">
                            {{ $register->institution->name }}
                            <span class="block text-xs text-gray-400 font-normal">{{ $register->institution->code }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $register->institution->type }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $register->institution->sector->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($register->status === 'locked')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Locked</span>
                            @elseif($register->status === 'submitted')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Submitted</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $register->submitted_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route($showRoute, $register) }}"
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium underline underline-offset-2">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                            No staff strength registers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($registers->hasPages())
        <div class="mt-4">{{ $registers->withQueryString()->links() }}</div>
    @endif

@endsection
