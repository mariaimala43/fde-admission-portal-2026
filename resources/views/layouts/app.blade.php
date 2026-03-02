<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FDE Admission Portal 2026')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

    {{-- Top Navigation --}}
    <nav class="bg-blue-900 text-white px-6 py-3 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="font-bold text-lg tracking-wide">
                FDE Admission Portal
            </a>
            <span class="text-blue-400 text-sm hidden md:block">2026-27</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-blue-200">{{ Auth::user()->name }}</span>
            <span class="bg-blue-700 text-xs px-3 py-1 rounded-full uppercase tracking-wide">
                {{ Auth::user()->getRoleNames()->first() }}
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm text-blue-300 hover:text-white transition">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-64 bg-white shadow-sm min-h-screen pt-6 hidden md:block">
            <nav class="px-4 space-y-1">

                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                          {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                    Dashboard
                </a>

                @role('fde_cell')
                    <div class="pt-4 pb-1 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        FDE Cell
                    </div>
                    <a href="{{ route('fde.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                        {{ request()->routeIs('fde.dashboard') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('fde.schools.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                             {{ request()->routeIs('fde.schools.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        All Schools
                    </a>
                    <a href="{{ route('fde.reports.master') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                        {{ request()->routeIs('fde.reports.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Master Report
                    </a>

                    <div class="pt-4 pb-1 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Admin
                    </div>
                    <a href="{{ route('admin.ucs.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                        {{ request()->routeIs('admin.ucs.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Union Councils
                    </a>
                    <a href="{{ route('admin.sectors.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                        {{ request()->routeIs('admin.sectors.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Sectors
                    </a>
                    <a href="{{ route('admin.institutions.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                        {{ request()->routeIs('admin.institutions.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Institutions
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                        {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Users
                    </a>
                    <a href="{{ route('admin.import.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                        {{ request()->routeIs('admin.import.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Import Schools
                    </a>
                @endrole

                @role('aeo')
                    <div class="pt-4 pb-1 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        AEO Panel
                    </div>
                    <a href="{{ route('aeo.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                        {{ request()->routeIs('aeo.dashboard') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Sector Dashboard
                    </a>
                @endrole
                @role('hoi')
                    <div class="pt-4 pb-1 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        My School
                    </div>

                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
          {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Dashboard
                    </a>

                    <a href="{{ route('hoi.classes.setup') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                         {{ request()->routeIs('hoi.classes.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Classes & Sections
                    </a>

                    <a href="{{ route('hoi.enrollment.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                         {{ request()->routeIs('hoi.enrollment.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Baseline Enrollment
                    </a>

                    <a href="{{ route('hoi.admissions.daily') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                            {{ request()->routeIs('hoi.admissions.*') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Daily Admissions
                    </a>

                    <a href="{{ route('hoi.admissions.report') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                             {{ request()->routeIs('hoi.admissions.report') ? 'bg-blue-50 text-blue-900' : 'text-gray-600 hover:bg-gray-50' }}">
                        Admission Report
                    </a>
                    <a href="{{ route('portal.index') }}" target="_blank"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
          text-gray-600 hover:bg-gray-50">
                        🌐 Public Portal ↗
                    </a>
                @endrole


            </nav>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 p-8">

            {{-- Success Message --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Error Message --}}
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')

        </main>

    </div>

</body>

</html>
