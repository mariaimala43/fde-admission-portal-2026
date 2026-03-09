@extends('layouts.app')
@section('title', 'Institutions')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Institutions</h2>
            <p class="text-sm text-gray-500 mt-1">Manage all FDE schools</p>
        </div>
        <a href="{{ route('admin.institutions.create') }}"
            class="bg-blue-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            + Add Institution
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..."
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64" />

            <select name="type"
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                @foreach (['I-V', 'I-VIII', 'I-X', 'I-XII', 'VI-VIII', 'VI-X', 'VI-XII', 'Model College'] as $type)
                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>

            <select name="gender"
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Genders</option>
                <option value="boys" {{ request('gender') == 'boys' ? 'selected' : '' }}>Boys</option>
                <option value="girls" {{ request('gender') == 'girls' ? 'selected' : '' }}>Girls</option>
                <option value="co_education" {{ request('gender') == 'co_education' ? 'selected' : '' }}>Co-Education
                </option>
            </select>

            <select name="sector"
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Sectors</option>
                @foreach ($sectors as $sector)
                    <option value="{{ $sector->id }}" {{ request('sector') == $sector->id ? 'selected' : '' }}>
                        {{ $sector->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="bg-gray-700 text-white px-5 py-2 rounded-lg text-sm hover:bg-gray-600 transition">
                Filter
            </button>

            @if (request()->hasAny(['search', 'type', 'gender']))
                <a href="{{ route('admin.institutions.index') }}"
                    class="px-5 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Clear
                </a>
            @endif

        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4 text-left">#</th>
                    <th class="px-6 py-4 text-left">Name</th>
                    <th class="px-6 py-4 text-left">Type</th>
                    <th class="px-6 py-4 text-left">Gender</th>
                    <th class="px-6 py-4 text-left">Sector</th>
                    <th class="px-6 py-4 text-left">UC</th>
                    <th class="px-6 py-4 text-left">Shift</th>
                    <th class="px-6 py-4 text-left">Cambridge</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($institutions as $institution)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $institution->name }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-blue-50 text-blue-700 text-xs px-2 py-1 rounded-full">
                                {{ $institution->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 capitalize">
                            {{ str_replace('_', '-', $institution->gender) }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $institution->sector?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $institution->unionCouncil?->code ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-600 capitalize">
                            {{ $institution->shift }}
                        </td>
                        <td class="px-6 py-4">
                            @if ($institution->is_cambridge)
                                <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">Yes</span>
                            @else
                                <span class="text-gray-400 text-xs">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $institution->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $institution->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 flex gap-3">
                            <a href="{{ route('admin.institutions.show', $institution) }}"
                                class="text-gray-600 hover:underline text-sm">View</a>
                            <a href="{{ route('admin.institutions.edit', $institution) }}"
                                class="text-blue-600 hover:underline text-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-8 text-center text-gray-400">
                            No institutions found.
                            <a href="{{ route('admin.institutions.create') }}" class="text-blue-600 hover:underline">Add
                                one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($institutions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $institutions->links() }}
            </div>
        @endif
    </div>

@endsection
