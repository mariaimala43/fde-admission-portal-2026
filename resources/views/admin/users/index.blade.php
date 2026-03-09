{{-- SAVE AS: resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Users')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
            <p class="text-sm text-gray-500 mt-1">All portal users — managed by FDE Cell</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="bg-blue-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            + Add User
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-gray-700">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Total</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-blue-700">{{ $stats['hoi'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">HOI</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-green-600">{{ $stats['aeo'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">AEO</p>
        </div>
        <div class="bg-white rounded-xl border border-purple-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-purple-600">{{ $stats['fde_cell'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">FDE Cell</p>
        </div>
        <div class="bg-white rounded-xl border border-yellow-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-yellow-600">{{ $stats['director'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Director</p>
        </div>
        <div class="bg-white rounded-xl border border-red-100 shadow-sm px-4 py-3 text-center">
            <p class="text-xl font-bold text-red-500">{{ $stats['inactive'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Inactive</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email or phone…"
                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-56" />
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Role</label>
                <select name="role"
                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Roles</option>
                    <option value="hoi" {{ request('role') === 'hoi' ? 'selected' : '' }}>HOI</option>
                    <option value="aeo" {{ request('role') === 'aeo' ? 'selected' : '' }}>AEO</option>
                    <option value="fde_cell" {{ request('role') === 'fde_cell' ? 'selected' : '' }}>FDE Cell</option>
                    <option value="director" {{ request('role') === 'director' ? 'selected' : '' }}>Director</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status"
                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="bg-blue-900 text-white px-5 py-2 rounded-lg text-sm hover:bg-blue-800 transition">
                Filter
            </button>

            @if (request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-500 hover:bg-gray-50">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3 text-left">#</th>
                    <th class="px-5 py-3 text-left">Name</th>
                    <th class="px-5 py-3 text-left">Email</th>
                    <th class="px-5 py-3 text-left">Phone</th>
                    <th class="px-5 py-3 text-center">Role</th>
                    <th class="px-5 py-3 text-left">Assignment</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                    @php
                        $role = $user->getRoleNames()->first();
                        $roleBadge = match ($role) {
                            'hoi' => 'bg-blue-100 text-blue-700',
                            'aeo' => 'bg-green-100 text-green-700',
                            'fde_cell' => 'bg-purple-100 text-purple-700',
                            'director' => 'bg-yellow-100 text-yellow-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $roleLabel = match ($role) {
                            'hoi' => 'HOI',
                            'aeo' => 'AEO',
                            'fde_cell' => 'FDE Cell',
                            'director' => 'Director',
                            default => ucfirst($role ?? '—'),
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ !$user->is_active ? 'opacity-60' : '' }}">
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $loop->iteration }}</td>

                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center
                                            text-blue-800 text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $user->name }}</span>
                            </div>
                        </td>

                        <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>

                        <td class="px-5 py-3 text-gray-500">{{ $user->phone ?? '—' }}</td>

                        <td class="px-5 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleBadge }}">
                                {{ $roleLabel }}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-gray-600 text-xs">
                            @if ($role === 'hoi' && $user->institution)
                                <span class="font-medium">{{ $user->institution->name }}</span>
                            @elseif($role === 'aeo' && $user->sectors->isNotEmpty())
                                <span class="font-medium">{{ $user->sectors->first()->name }}</span>
                                <span class="text-gray-400"> (Sector)</span>
                            @elseif($role === 'fde_cell')
                                <span class="text-gray-400">System-wide</span>
                            @elseif($role === 'director')
                                <span class="text-gray-400">All institutions</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-5 py-3 text-center">
                            @if ($user->is_active)
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                            @else
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600">Inactive</span>
                            @endif
                        </td>

                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>

                                @if ($user->id !== auth()->id())
                                    <span class="text-gray-200">|</span>
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
                                        onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} {{ $user->name }}?')">
                                        @csrf
                                        <button type="submit"
                                            class="text-sm font-medium {{ $user->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }}">
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                            No users found.
                            <a href="{{ route('admin.users.create') }}" class="text-blue-600 hover:underline">Add one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($users->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

@endsection
