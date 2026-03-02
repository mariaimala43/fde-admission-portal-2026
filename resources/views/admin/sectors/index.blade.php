@extends('layouts.app')
@section('title', 'Sectors')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Sectors</h2>
            <p class="text-sm text-gray-500 mt-1">Manage all Sectors</p>
        </div>
        <a href="{{ route('admin.sectors.create') }}"
            class="bg-blue-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            + Add Sector
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4 text-left">#</th>
                    <th class="px-6 py-4 text-left">Name</th>
                    <th class="px-6 py-4 text-left">Code</th>
                    <th class="px-6 py-4 text-left">Union Council</th>
                    <th class="px-6 py-4 text-left">Schools</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sectors as $sector)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $sector->name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $sector->code }}</td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $sector->unionCouncil?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $sector->institutions_count }}
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $sector->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $sector->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.sectors.edit', $sector) }}"
                                class="text-blue-600 hover:underline text-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                            No sectors found.
                            <a href="{{ route('admin.sectors.create') }}" class="text-blue-600 hover:underline">Add one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($sectors->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $sectors->links() }}
            </div>
        @endif
    </div>

@endsection
