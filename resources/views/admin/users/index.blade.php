@extends('layouts.app')
@section('title', 'Users')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Users</h2>
            <p class="text-sm text-gray-500 mt-1">Manage system users and roles</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="bg-blue-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            + Add User
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64" />

            <select name="role"
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Roles</option>
                <option value="hoi" {{ request('role') == 'hoi' ? 'selected' : '' }}>HoI</option>
                <option value="aeo" {{ request('role') == 'aeo' ? 'selected' : '' }}>AEO</option>
                <option value="fde_cell" {{ request('role') == 'fde_cell' ? 'selected' : '' }}>FDE Cell</option>
                <option value="director" {{ request('role') == 'director' ? 'selected' : '' }}>Director</option>
            </select>

            <button type="submit" class="bg-gray-700 text-white px-5 py-2 rounded-lg text-sm hover:bg-gray-600 transition">
                Filter
            </button>

            @if (request()->hasAny(['search', 'role']))
                <a href="{{ route('admin.users.index') }}"
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
                    <th class="px-6 py-4 text-left">Email</th>
                    <th class="px-6 py-4 text-left">Role</th>
                    <th class="px-6 py-4 text-left">Institution / Sector</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @php $role = $user->getRoleNames()->first(); @endphp
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium uppercase
                        {{ $role === 'fde_cell'
                            ? 'bg-blue-100 text-blue-700'
                            : ($role === 'aeo'
                                ? 'bg-yellow-100 text-yellow-700'
                                : ($role === 'hoi'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-600')) }}">
                                {{ $role ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            @if ($user->institution)
                                {{ $user->institution->name }}
                            @elseif($user->sectors->count())
                                {{ $user->sectors->pluck('name')->join(', ') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 flex gap-3 items-center">
                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="text-blue-600 hover:underline text-sm">Edit</a>

                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf
                                    <button type="submit"
                                        class="text-sm {{ $user->is_active ? 'text-red-500' : 'text-green-600' }} hover:underline">
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                            No users found.
                            <a href="{{ route('admin.users.create') }}" class="text-blue-600 hover:underline">Add one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

@endsection
