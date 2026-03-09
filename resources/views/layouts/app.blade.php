<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — FDE Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    {{-- FDE Portal Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/fde-portal.css') }}">

    @stack('styles')
</head>

<body x-data="fdeApp()" :data-theme="theme">

    {{-- Mobile overlay --}}
    <div class="sb-overlay" :class="{ open: sidebarOpen }" @click="sidebarOpen = false"></div>

    {{-- ═══════════════════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════════════════ --}}
    <aside class="sidebar" :class="{ open: sidebarOpen }">

        {{-- Brand --}}
        <div class="sb-brand">
            <div class="sb-logo">🏛️</div>
            <div>
                <p class="sb-title">FDE Portal</p>
                <p class="sb-sub">Admission System 2026–27</p>
            </div>
        </div>

        {{-- User chip --}}
        <div class="sb-user">
            <div class="sb-avatar">👤</div>
            <div style="min-width:0;flex:1;">
                <p class="sb-uname" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ Auth::user()->name }}
                </p>
                <p class="sb-urole">
                    {{ Str::upper(str_replace('_', ' ', Auth::user()->getRoleNames()->first() ?? 'User')) }}
                </p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="sb-nav">

            {{-- ══════════════════════════════════════════════════════
             HOI — Head of Institution (Principal)
             Permissions: enrollment.*, admission.*, section.*,
                          transfer.*, referral.respond,
                          monitoring.view/update_test/update_doc,
                          dashboard.view, reports.view/vacancy
        ══════════════════════════════════════════════════════ --}}
            @role('hoi')

                <p class="sb-sec">My School</p>

                <a href="{{ route('dashboard') }}" class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="ico">🏠</span> Dashboard
                </a>

                <a href="{{ route('hoi.classes.setup') }}"
                    class="sb-link {{ request()->routeIs('hoi.classes.*') ? 'active' : '' }}">
                    <span class="ico">🎓</span> Classes &amp; Sections
                </a>

                <a href="{{ route('hoi.enrollment.index') }}"
                    class="sb-link {{ request()->routeIs('hoi.enrollment.*') ? 'active' : '' }}">
                    <span class="ico">📊</span> Baseline Enrollment
                </a>

                <p class="sb-sec">Admissions</p>

                <a href="{{ route('hoi.admissions.daily') }}"
                    class="sb-link {{ request()->routeIs('hoi.admissions.daily') ? 'active' : '' }}">
                    <span class="ico">📝</span> Daily Admissions
                </a>

                <a href="{{ route('hoi.admissions.report') }}"
                    class="sb-link {{ request()->routeIs('hoi.admissions.report') ? 'active' : '' }}">
                    <span class="ico">📋</span> Admission Report
                </a>

                {{-- Corrections — with pending badge --}}
                <a href="{{ route('hoi.corrections.index') }}"
                    class="sb-link {{ request()->routeIs('hoi.corrections.*') ? 'active' : '' }}">
                    <span class="ico">✏️</span> Corrections
                    @php
                        $hoiInstitution = Auth::user()->institution;
                        $hoiPendingCorrections = $hoiInstitution
                            ? \App\Models\AdmissionCorrection::where('institution_id', $hoiInstitution->id)
                                ->where('status', 'pending')
                                ->count()
                            : 0;
                    @endphp
                    @if ($hoiPendingCorrections > 0)
                        <span class="sb-badge">{{ $hoiPendingCorrections }}</span>
                    @endif
                </a>

                {{-- Transfers — badge for incoming pending --}}
                <a href="{{ route('hoi.transfers.index') }}"
                    class="sb-link {{ request()->routeIs('hoi.transfers.*') ? 'active' : '' }}">
                    <span class="ico">🔄</span> Transfers
                    @php
                        $hoiInstitution = Auth::user()->institution;
                        $hoiPendingTransfers = $hoiInstitution
                            ? \App\Models\StudentTransfer::where('to_institution_id', $hoiInstitution->id)
                                ->whereIn('status', ['pending', 'info_requested'])
                                ->count()
                            : 0;
                    @endphp
                    @if ($hoiPendingTransfers > 0)
                        <span class="sb-badge sb-badge-y">{{ $hoiPendingTransfers }}</span>
                    @endif
                </a>

                {{-- Referrals — HOI can only respond (referral.respond) --}}
                <a href="{{ route('hoi.referrals.index') }}"
                    class="sb-link {{ request()->routeIs('hoi.referrals.*') ? 'active' : '' }}">
                    <span class="ico">📨</span> Referrals
                    @php
                        $hoiInstitution = Auth::user()->institution;
                        $hoiPendingReferrals = $hoiInstitution
                            ? \App\Models\Referral::where('institution_id', $hoiInstitution->id)
                                ->where('status', 'pending')
                                ->count()
                            : 0;
                    @endphp
                    @if ($hoiPendingReferrals > 0)
                        <span class="sb-badge">{{ $hoiPendingReferrals }}</span>
                    @endif
                </a>

                <p class="sb-sec">Monitoring</p>

                {{-- Monitoring — monitoring.view + update_test + update_doc --}}
                <a href="{{ route('hoi.monitoring.index') }}"
                    class="sb-link {{ request()->routeIs('hoi.monitoring.*') ? 'active' : '' }}">
                    <span class="ico">👁</span> Monitoring
                </a>

                {{-- New Classrooms — only visible if institution has rooms --}}
                @if (Auth::user()->institution &&
                        \App\Models\NewConstructionRoom::where('institution_id', Auth::user()->institution->id)->exists())
                    <a href="{{ route('hoi.rooms.index') }}"
                        class="sb-link {{ request()->routeIs('hoi.rooms.*') ? 'active' : '' }}">
                        <span class="ico">🏗️</span> New Classrooms
                    </a>
                @endif

                <p class="sb-sec">Reports</p>

                {{-- Vacancy report (reports.vacancy permission) --}}
                @can('reports.vacancy')
                <a href="{{ route('hoi.reports.vacancy') }}"
                   class="sb-link {{ request()->routeIs('hoi.reports.vacancy') ? 'active' : '' }}">
                    <span class="ico">💺</span> Vacancy Report
                </a>
                @endcan

                <p class="sb-sec">Links</p>

                <a href="{{ route('portal.index') }}" target="_blank" class="sb-link">
                    <span class="ico">🌐</span> Public Portal ↗
                </a>

            @endrole

            {{-- ══════════════════════════════════════════════════════
             FDE CELL — Full System Access
             Permissions: all permissions
        ══════════════════════════════════════════════════════ --}}
            @role('fde_cell')

                <p class="sb-sec">FDE Dashboard</p>

                <a href="{{ route('fde.dashboard') }}"
                    class="sb-link {{ request()->routeIs('fde.dashboard') ? 'active' : '' }}">
                    <span class="ico">🏠</span> Dashboard
                </a>

                <a href="{{ route('fde.schools.index') }}"
                    class="sb-link {{ request()->routeIs('fde.schools.*') ? 'active' : '' }}">
                    <span class="ico">🏫</span> All Schools
                </a>

                <p class="sb-sec">Admissions</p>

                {{-- Corrections — badge for all pending system-wide --}}
                <a href="{{ route('fde.corrections.index') }}"
                    class="sb-link {{ request()->routeIs('fde.corrections.*') ? 'active' : '' }}">
                    <span class="ico">✏️</span> Corrections
                    @php $fdePendingCorrections = \App\Models\AdmissionCorrection::where('status','pending')->count(); @endphp
                    @if ($fdePendingCorrections > 0)
                        <span class="sb-badge">{{ $fdePendingCorrections }}</span>
                    @endif
                </a>

                {{-- Transfers — includes cross_sector permission --}}
                <a href="{{ route('fde.transfers.index') }}"
                    class="sb-link {{ request()->routeIs('fde.transfers.*') ? 'active' : '' }}">
                    <span class="ico">🔄</span> Transfers
                    @php $fdePendingTransfers = \App\Models\StudentTransfer::whereIn('status',['pending','info_requested'])->count(); @endphp
                    @if ($fdePendingTransfers > 0)
                        <span class="sb-badge sb-badge-y">{{ $fdePendingTransfers }}</span>
                    @endif
                </a>

                {{-- Referrals — FDE can create/edit/cancel/re-refer --}}
                <a href="{{ route('fde.referrals.index') }}"
                    class="sb-link {{ request()->routeIs('fde.referrals.*') ? 'active' : '' }}">
                    <span class="ico">📨</span> Referrals
                    @php $fdePendingReferrals = \App\Models\Referral::where('status','pending')->count(); @endphp
                    @if ($fdePendingReferrals > 0)
                        <span class="sb-badge">{{ $fdePendingReferrals }}</span>
                    @endif
                </a>

                {{-- Monitoring — full control including merit + override --}}
                <a href="{{ route('fde.monitoring.index') }}"
                    class="sb-link {{ request()->routeIs('fde.monitoring.*') ? 'active' : '' }}">
                    <span class="ico">👁</span> Monitoring
                </a>

                {{-- Admission Overrides (admission.override + admission.return) --}}
                <a href="{{ route('fde.admissions.index') }}"
                    class="sb-link {{ request()->routeIs('fde.admissions.*') ? 'active' : '' }}">
                    <span class="ico">⚡</span> Admission Overrides
                </a>

                {{-- New Classrooms --}}
                <a href="{{ route('fde.rooms.index') }}"
                    class="sb-link {{ request()->routeIs('fde.rooms.*') ? 'active' : '' }}">
                    <span class="ico">🏗️</span> New Classrooms
                </a>

                <p class="sb-sec">Reports</p>

                <a href="{{ route('fde.reports.master') }}"
                    class="sb-link {{ request()->routeIs('fde.reports.master') ? 'active' : '' }}">
                    <span class="ico">📋</span> Master Report
                </a>

                <a href="{{ route('fde.reports.dashboard') }}"
                    class="sb-link {{ request()->routeIs('fde.reports.dashboard') ? 'active' : '' }}">
                    <span class="ico">📊</span> Analytics
                </a>

                <a href="{{ route('fde.reports.sector') }}"
                    class="sb-link {{ request()->routeIs('fde.reports.sector') ? 'active' : '' }}">
                    <span class="ico">🗺️</span> Sector / UC
                </a>

                <a href="{{ route('fde.reports.vacancy') }}"
                    class="sb-link {{ request()->routeIs('fde.reports.vacancy') ? 'active' : '' }}">
                    <span class="ico">💺</span> Vacancy
                </a>

                <a href="{{ route('fde.reports.oosc') }}"
                    class="sb-link {{ request()->routeIs('fde.reports.oosc') ? 'active' : '' }}">
                    <span class="ico">🎯</span> OOSC / P2P
                </a>

                <a href="{{ route('fde.reports.gender') }}"
                    class="sb-link {{ request()->routeIs('fde.reports.gender') ? 'active' : '' }}">
                    <span class="ico">👥</span> Gender
                </a>
                <a href="{{ route('fde.ai.reports') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                    {{ request()->routeIs('fde.ai.reports') ? 'bg-blue-50 dark:bg-blue-950 text-blue-900 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}"
                    target="_blank">
                    🤖 AI Report Studio
                </a>

                <p class="sb-sec">Configuration</p>

                {{-- Seat configuration (seats.configure permission) --}}
                <a href="{{ route('fde.seats.index') }}"
                    class="sb-link {{ request()->routeIs('fde.seats.*') ? 'active' : '' }}">
                    <span class="ico">💺</span> Seat Configuration
                </a>

                {{-- Admission period management (admission_period.manage) --}}
                <a href="{{ route('fde.admission-period.index') }}"
                    class="sb-link {{ request()->routeIs('fde.admission-period.*') ? 'active' : '' }}">
                    <span class="ico">🗓️</span> Admission Period
                </a>

                <p class="sb-sec">Admin</p>

                {{-- Audit log (audit.view + audit.export) --}}
                <a href="{{ route('fde.audit.index') }}"
                    class="sb-link {{ request()->routeIs('fde.audit.*') ? 'active' : '' }}">
                    <span class="ico">📜</span> Audit Log
                </a>

                {{-- Portal settings (portal.settings permission) --}}
                <a href="{{ route('fde.portal-settings.index') }}"
                    class="sb-link {{ request()->routeIs('fde.portal-settings.*') ? 'active' : '' }}">
                    <span class="ico">⚙️</span> Portal Settings
                </a>

                {{-- Academic years (academic_year.manage permission) --}}
                <a href="{{ route('admin.academic-years.index') }}"
                    class="sb-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}">
                    <span class="ico">📅</span> Academic Years
                </a>

                {{-- User management (users.manage permission) --}}
                <a href="{{ route('admin.users.index') }}"
                    class="sb-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <span class="ico">👤</span> Users
                </a>

                {{-- Institutions (institution.create/edit permission) --}}
                <a href="{{ route('admin.institutions.index') }}"
                    class="sb-link {{ request()->routeIs('admin.institutions.*') ? 'active' : '' }}">
                    <span class="ico">🏫</span> Institutions
                </a>

                {{-- Sectors --}}
                <a href="{{ route('admin.sectors.index') }}"
                    class="sb-link {{ request()->routeIs('admin.sectors.*') ? 'active' : '' }}">
                    <span class="ico">🗺️</span> Sectors
                </a>

                {{-- Import --}}
                <a href="{{ route('admin.import.index') }}"
                    class="sb-link {{ request()->routeIs('admin.import.*') ? 'active' : '' }}">
                    <span class="ico">📥</span> Import Data
                </a>

                <p class="sb-sec">Links</p>

                <a href="{{ route('portal.index') }}" target="_blank" class="sb-link">
                    <span class="ico">🌐</span> Public Portal ↗
                </a>


            @endrole

            {{-- ══════════════════════════════════════════════════════
             AEO — Area Education Officer
             Permissions: dashboard.view, monitoring.view,
                          reports.view/export/sector/vacancy/oosc/gender/dashboard
             READ-ONLY — no data entry
        ══════════════════════════════════════════════════════ --}}
            @role('aeo')
                <p class="sb-sec">AEO Panel</p>

                <a href="{{ route('aeo.dashboard') }}"
                    class="sb-link {{ request()->routeIs('aeo.dashboard') ? 'active' : '' }}">
                    <span class="ico">🏠</span> Sector Dashboard
                </a>

                {{-- Monitoring — read-only, scoped to sector (monitoring.view) --}}
                <a href="{{ route('aeo.monitoring.index') }}"
                    class="sb-link {{ request()->routeIs('aeo.monitoring.*') ? 'active' : '' }}">
                    <span class="ico">👁</span> Monitoring
                </a>

                <p class="sb-sec">Reports</p>

                <a href="{{ route('aeo.reports.dashboard') }}"
                    class="sb-link {{ request()->routeIs('aeo.reports.dashboard') ? 'active' : '' }}">
                    <span class="ico">📊</span> Analytics
                </a>

                <a href="{{ route('aeo.reports.sector') }}"
                    class="sb-link {{ request()->routeIs('aeo.reports.sector') ? 'active' : '' }}">
                    <span class="ico">🗺️</span> Sector / UC
                </a>

                <a href="{{ route('aeo.reports.vacancy') }}"
                    class="sb-link {{ request()->routeIs('aeo.reports.vacancy') ? 'active' : '' }}">
                    <span class="ico">💺</span> Vacancy
                </a>

                <a href="{{ route('aeo.reports.oosc') }}"
                    class="sb-link {{ request()->routeIs('aeo.reports.oosc') ? 'active' : '' }}">
                    <span class="ico">🎯</span> OOSC / P2P
                </a>

                <a href="{{ route('aeo.reports.gender') }}"
                    class="sb-link {{ request()->routeIs('aeo.reports.gender') ? 'active' : '' }}">
                    <span class="ico">👥</span> Gender
                </a>

                <p class="sb-sec">Exports</p>

                {{-- reports.export permission --}}
                <a href="{{ route('aeo.export.vacancy') }}" class="sb-link">
                    <span class="ico">⬇️</span> Export Vacancy
                </a>

                <a href="{{ route('aeo.export.oosc') }}" class="sb-link">
                    <span class="ico">⬇️</span> Export OOSC
                </a>
            @endrole

            {{-- ══════════════════════════════════════════════════════
             DIRECTOR / DG / SECRETARY
             Permissions: dashboard.view, monitoring.view,
                          reports.view/export/sector/vacancy/oosc/gender/dashboard
             READ-ONLY — system-wide, no data entry, no overrides
        ══════════════════════════════════════════════════════ --}}
            @role('director')
                <p class="sb-sec">Executive View</p>

                <a href="{{ route('director.dashboard') }}"
                    class="sb-link {{ request()->routeIs('director.dashboard') ? 'active' : '' }}">
                    <span class="ico">🏠</span> System Dashboard
                </a>

                {{-- Monitoring — read-only, all schools (monitoring.view) --}}
                <a href="{{ route('director.monitoring.index') }}"
                    class="sb-link {{ request()->routeIs('director.monitoring.*') ? 'active' : '' }}">
                    <span class="ico">👁</span> System Monitoring
                </a>

                <p class="sb-sec">Reports</p>

                <a href="{{ route('director.reports.dashboard') }}"
                    class="sb-link {{ request()->routeIs('director.reports.dashboard') ? 'active' : '' }}">
                    <span class="ico">📊</span> Analytics
                </a>

                <a href="{{ route('director.reports.sector') }}"
                    class="sb-link {{ request()->routeIs('director.reports.sector') ? 'active' : '' }}">
                    <span class="ico">🗺️</span> Sector / UC
                </a>

                <a href="{{ route('director.reports.vacancy') }}"
                    class="sb-link {{ request()->routeIs('director.reports.vacancy') ? 'active' : '' }}">
                    <span class="ico">💺</span> Vacancy
                </a>

                <a href="{{ route('director.reports.oosc') }}"
                    class="sb-link {{ request()->routeIs('director.reports.oosc') ? 'active' : '' }}">
                    <span class="ico">🎯</span> OOSC / P2P
                </a>

                <a href="{{ route('director.reports.gender') }}"
                    class="sb-link {{ request()->routeIs('director.reports.gender') ? 'active' : '' }}">
                    <span class="ico">👥</span> Gender
                </a>

                <p class="sb-sec">Exports</p>

                {{-- reports.export permission --}}
                <a href="{{ route('director.export.vacancy') }}" class="sb-link">
                    <span class="ico">⬇️</span> Export Vacancy
                </a>

                <a href="{{ route('director.export.oosc') }}" class="sb-link">
                    <span class="ico">⬇️</span> Export OOSC
                </a>

                <a href="{{ route('director.export.master') }}" class="sb-link">
                    <span class="ico">⬇️</span> Export Master
                </a>
            @endrole

        </nav>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-logout">
                <span class="ico">🚪</span> Sign Out
            </button>
        </form>

    </aside>

    {{-- ═══════════════════════════════════════════════════════
     TOPBAR
═══════════════════════════════════════════════════════ --}}
    <header class="topbar">
        <div class="topbar-left">
            <button class="hamburger" :class="{ open: sidebarOpen }" @click="sidebarOpen = !sidebarOpen"
                aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div>
                <p class="topbar-title">@yield('title', 'Dashboard')</p>
                <p class="topbar-sub hide-mob">Federal Directorate of Education · 2026–27</p>
            </div>
        </div>

        <div class="topbar-right">

            {{-- Dark / Light toggle — persists via localStorage --}}
            <button class="theme-btn" @click="toggleTheme()"
                :title="theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                <span x-text="theme === 'dark' ? '☀️' : '🌙'"></span>
            </button>

            {{-- Public portal shortcut --}}
            <a href="{{ route('portal.index') }}" target="_blank" class="portal-btn hide-mob">
                🌐 Portal
            </a>

            {{-- Live indicator --}}
            <div class="live-pill">
                <div class="live-dot"></div>
                <span class="live-txt">Live</span>
            </div>

        </div>
    </header>

    {{-- ═══════════════════════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════════════════════ --}}
    <div class="main-wrap">
        <main class="main-content">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="fde-alert fde-alert-success">✅ <span>{{ session('success') }}</span></div>
            @endif
            @if (session('error'))
                <div class="fde-alert fde-alert-error">❌ <span>{{ session('error') }}</span></div>
            @endif
            @if (session('warning'))
                <div class="fde-alert fde-alert-warning">⚠️ <span>{{ session('warning') }}</span></div>
            @endif
            @if (session('info'))
                <div class="fde-alert fde-alert-info">ℹ️ <span>{{ session('info') }}</span></div>
            @endif
            @if ($errors->any())
                <div class="fde-alert fde-alert-error">
                    <span>❌</span>
                    <div>
                        <p style="font-weight:700;margin-bottom:4px;">Please fix the following:</p>
                        <ul style="padding-left:16px;margin:0;">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')

        </main>
    </div>

    {{-- ═══════════════════════════════════════════════════════
     ALPINE JS — Theme + Sidebar
═══════════════════════════════════════════════════════ --}}
    <script>
        function fdeApp() {
            return {
                sidebarOpen: false,
                theme: localStorage.getItem('fde-theme') || 'dark',

                init() {
                    // Apply saved theme immediately on load
                    document.documentElement.setAttribute('data-theme', this.theme);

                    // Hamburger visibility: show only on mobile
                    const syncHamburger = () => {
                        document.querySelectorAll('.hamburger').forEach(el => {
                            el.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
                        });
                    };
                    syncHamburger();
                    window.addEventListener('resize', () => {
                        syncHamburger();
                        if (window.innerWidth > 768) this.sidebarOpen = false;
                    });
                },

                toggleTheme() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('fde-theme', this.theme);
                    document.documentElement.setAttribute('data-theme', this.theme);
                }
            }
        }
    </script>

    @stack('scripts')

</body>

</html>
