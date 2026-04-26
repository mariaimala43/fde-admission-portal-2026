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

                {{-- Referrals — with pending badge --}}
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

                <p class="sb-sec">Staff &amp; Data</p>

                <a href="{{ route('hoi.merit-lists.index') }}"
                    class="sb-link {{ request()->routeIs('hoi.merit-lists.*') ? 'active' : '' }}">
                    <span class="ico">📋</span> Merit Lists
                </a>

                <a href="{{ route('hoi.facilities.index') }}"
                    class="sb-link {{ request()->routeIs('hoi.facilities.*') ? 'active' : '' }}">
                    <span class="ico">🏫</span> Facilities
                </a>

                <a href="{{ route('hoi.notifications.index') }}"
                    class="sb-link {{ request()->routeIs('hoi.notifications.*') ? 'active' : '' }}">
                    <span class="ico">🔔</span> Notifications
                </a>

            @endrole

            {{-- ══════════════════════════════════════════════════════
             FDE CELL — Full System Access
             Permissions: all permissions
        ══════════════════════════════════════════════════════ --}}
            @role('fde_cell')
                @php
                    $fdeColOpen   = request()->routeIs('fde.colleges.*') || request()->routeIs('uc.control-rooms.*');
                    $fdeAdmOpen   = request()->routeIs('fde.corrections.*','fde.admission-grants.*','fde.transfers.*','fde.referrals.*','fde.monitoring.*','fde.admissions.*','fde.rooms.*','fde.staff-strength.*','fde.merit-lists.*');
                    $fdeRptOpen   = request()->routeIs('fde.reports.*','fde.ai.*');
                    $fdeCfgOpen   = request()->routeIs('fde.seats.*','fde.admission-period.*');
                    $fdeAdmOpen   = $fdeAdmOpen ?? false;
                    $fdeAdminOpen = request()->routeIs('fde.audit.*','fde.portal-settings.*','admin.*','fde.app-settings.*','fde.theme.*','fde.system-reset.*','announcements.*');
                @endphp

                <p class="sb-sec">FDE Dashboard</p>

                <a href="{{ route('fde.dashboard') }}"
                    class="sb-link {{ request()->routeIs('fde.dashboard') ? 'active' : '' }}">
                    <span class="ico">🏠</span> Dashboard
                </a>
                <a href="{{ route('fde.schools.index') }}"
                    class="sb-link {{ request()->routeIs('fde.schools.*') ? 'active' : '' }}">
                    <span class="ico">🏫</span> All Schools
                </a>

                {{-- ── Colleges ── --}}
                <div x-data="{ open: {{ $fdeColOpen ? 'true' : 'false' }} }">
                    <div @click="open = !open" style="display:flex;flex-direction:row;align-items:center;justify-content:space-between;width:100%;padding:6px 8px;margin-top:10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-faint);cursor:pointer;border-radius:7px;">
                        <span>Colleges</span>
                        <span style="font-size:11px;opacity:0.5;transition:transform 0.2s;display:inline-block;line-height:1;" :style="open ? 'transform:rotate(180deg)' : ''">▼</span>
                    </div>
                    <div x-show="open" x-cloak>
                        <a href="{{ route('fde.colleges.model') }}"
                            class="sb-link {{ request()->routeIs('fde.colleges.model','fde.colleges.profile') ? 'active' : '' }}">
                            <span class="ico">🎓</span> Model Colleges
                        </a>
                        <a href="{{ route('fde.colleges.ex-fg') }}"
                            class="sb-link {{ request()->routeIs('fde.colleges.ex-fg') ? 'active' : '' }}">
                            <span class="ico">🏛️</span> Ex-FG Colleges
                        </a>
                        <a href="{{ route('uc.control-rooms.index') }}"
                            class="sb-link {{ request()->routeIs('uc.control-rooms.*') ? 'active' : '' }}">
                            <span class="ico">🖥️</span> UC Control Rooms
                        </a>
                    </div>
                </div>

                {{-- ── Admissions ── --}}
                <div x-data="{ open: {{ $fdeAdmOpen ? 'true' : 'false' }} }">
                    <div @click="open = !open" style="display:flex;flex-direction:row;align-items:center;justify-content:space-between;width:100%;padding:6px 8px;margin-top:10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-faint);cursor:pointer;border-radius:7px;">
                        <span>Admissions</span>
                        <span style="font-size:11px;opacity:0.5;transition:transform 0.2s;display:inline-block;line-height:1;" :style="open ? 'transform:rotate(180deg)' : ''">▼</span>
                    </div>
                    <div x-show="open" x-cloak>
                        <a href="{{ route('fde.corrections.index') }}"
                            class="sb-link {{ request()->routeIs('fde.corrections.*') ? 'active' : '' }}">
                            <span class="ico">✏️</span> Corrections
                            @php $fdePendingCorrections = \App\Models\AdmissionCorrection::where('status','pending')->count(); @endphp
                            @if ($fdePendingCorrections > 0)<span class="sb-badge">{{ $fdePendingCorrections }}</span>@endif
                        </a>
                        <a href="{{ route('fde.admission-grants.index') }}"
                            class="sb-link {{ request()->routeIs('fde.admission-grants.*') ? 'active' : '' }}">
                            <span class="ico">🔑</span> Edit Grants
                            @php $activeGrants = \App\Models\AdmissionEditGrant::where('status','active')->count(); @endphp
                            @if ($activeGrants > 0)<span class="sb-badge sb-badge-y">{{ $activeGrants }}</span>@endif
                        </a>
                        <a href="{{ route('fde.transfers.index') }}"
                            class="sb-link {{ request()->routeIs('fde.transfers.*') ? 'active' : '' }}">
                            <span class="ico">🔄</span> Transfers
                            @php $fdePendingTransfers = \App\Models\StudentTransfer::whereIn('status',['pending','info_requested'])->count(); @endphp
                            @if ($fdePendingTransfers > 0)<span class="sb-badge sb-badge-y">{{ $fdePendingTransfers }}</span>@endif
                        </a>
                        <a href="{{ route('fde.referrals.index') }}"
                            class="sb-link {{ request()->routeIs('fde.referrals.*') ? 'active' : '' }}">
                            <span class="ico">📨</span> Referrals
                            @php $fdePendingReferrals = \App\Models\Referral::where('status','pending')->count(); @endphp
                            @if ($fdePendingReferrals > 0)<span class="sb-badge">{{ $fdePendingReferrals }}</span>@endif
                        </a>
                        <a href="{{ route('fde.monitoring.index') }}"
                            class="sb-link {{ request()->routeIs('fde.monitoring.*') ? 'active' : '' }}">
                            <span class="ico">👁</span> Monitoring
                        </a>
                        <a href="{{ route('fde.admissions.index') }}"
                            class="sb-link {{ request()->routeIs('fde.admissions.*') ? 'active' : '' }}">
                            <span class="ico">⚡</span> Admission Overrides
                        </a>
                        <a href="{{ route('fde.rooms.index') }}"
                            class="sb-link {{ request()->routeIs('fde.rooms.*') ? 'active' : '' }}">
                            <span class="ico">🏗️</span> New Classrooms
                        </a>
                        @if (Route::has('fde.staff-strength.index'))
                        <a href="{{ route('fde.staff-strength.index') }}"
                            class="sb-link {{ request()->routeIs('fde.staff-strength.*') ? 'active' : '' }}">
                            <span class="ico">👨‍🏫</span> Staff Strength
                        </a>
                        @endif
                        <a href="{{ route('fde.merit-lists.index') }}"
                            class="sb-link {{ request()->routeIs('fde.merit-lists.*') ? 'active' : '' }}">
                            <span class="ico">📋</span> Merit Lists
                        </a>
                    </div>
                </div>

                {{-- ── Reports ── --}}
                <div x-data="{ open: {{ $fdeRptOpen ? 'true' : 'false' }} }">
                    <div @click="open = !open" style="display:flex;flex-direction:row;align-items:center;justify-content:space-between;width:100%;padding:6px 8px;margin-top:10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-faint);cursor:pointer;border-radius:7px;">
                        <span>Reports</span>
                        <span style="font-size:11px;opacity:0.5;transition:transform 0.2s;display:inline-block;line-height:1;" :style="open ? 'transform:rotate(180deg)' : ''">▼</span>
                    </div>
                    <div x-show="open" x-cloak>
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
                            class="sb-link {{ request()->routeIs('fde.ai.*') ? 'active' : '' }}"
                            target="_blank">
                            <span class="ico">🤖</span> AI Report Studio ↗
                        </a>
                    </div>
                </div>

                {{-- ── Configuration ── --}}
                <div x-data="{ open: {{ $fdeCfgOpen ? 'true' : 'false' }} }">
                    <div @click="open = !open" style="display:flex;flex-direction:row;align-items:center;justify-content:space-between;width:100%;padding:6px 8px;margin-top:10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-faint);cursor:pointer;border-radius:7px;">
                        <span>Configuration</span>
                        <span style="font-size:11px;opacity:0.5;transition:transform 0.2s;display:inline-block;line-height:1;" :style="open ? 'transform:rotate(180deg)' : ''">▼</span>
                    </div>
                    <div x-show="open" x-cloak>
                        <a href="{{ route('fde.seats.index') }}"
                            class="sb-link {{ request()->routeIs('fde.seats.*') ? 'active' : '' }}">
                            <span class="ico">💺</span> Seat Configuration
                        </a>
                        <a href="{{ route('fde.admission-period.index') }}"
                            class="sb-link {{ request()->routeIs('fde.admission-period.*') ? 'active' : '' }}">
                            <span class="ico">🗓️</span> Admission Period
                        </a>
                    </div>
                </div>

                {{-- ── Admin ── --}}
                <div x-data="{ open: {{ $fdeAdminOpen ? 'true' : 'false' }} }">
                    <div @click="open = !open" style="display:flex;flex-direction:row;align-items:center;justify-content:space-between;width:100%;padding:6px 8px;margin-top:10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-faint);cursor:pointer;border-radius:7px;">
                        <span>Admin</span>
                        <span style="font-size:11px;opacity:0.5;transition:transform 0.2s;display:inline-block;line-height:1;" :style="open ? 'transform:rotate(180deg)' : ''">▼</span>
                    </div>
                    <div x-show="open" x-cloak>
                        <a href="{{ route('fde.audit.index') }}"
                            class="sb-link {{ request()->routeIs('fde.audit.*') ? 'active' : '' }}">
                            <span class="ico">📜</span> Audit Log
                        </a>
                        <a href="{{ route('fde.portal-settings.index') }}"
                            class="sb-link {{ request()->routeIs('fde.portal-settings.*') ? 'active' : '' }}">
                            <span class="ico">⚙️</span> Portal Settings
                        </a>
                        <a href="{{ route('admin.academic-years.index') }}"
                            class="sb-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}">
                            <span class="ico">📅</span> Academic Years
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                            class="sb-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <span class="ico">👤</span> Users
                        </a>
                        <a href="{{ route('admin.institutions.index') }}"
                            class="sb-link {{ request()->routeIs('admin.institutions.*') ? 'active' : '' }}">
                            <span class="ico">🏫</span> Institutions
                        </a>
                        <a href="{{ route('admin.sectors.index') }}"
                            class="sb-link {{ request()->routeIs('admin.sectors.*') ? 'active' : '' }}">
                            <span class="ico">🗺️</span> Sectors
                        </a>
                        <a href="{{ route('admin.ucs.index') }}"
                            class="sb-link {{ request()->routeIs('admin.ucs.*') ? 'active' : '' }}">
                            <span class="ico">🏘️</span> Union Councils
                        </a>
                        <a href="{{ route('admin.import.index') }}"
                            class="sb-link {{ request()->routeIs('admin.import.*') ? 'active' : '' }}">
                            <span class="ico">📥</span> Import Data
                        </a>
                        <a href="{{ route('announcements.index') }}"
                            class="sb-link {{ request()->routeIs('announcements.*') ? 'active' : '' }}">
                            <span class="ico">📢</span> Announcements
                        </a>
                        <a href="{{ route('fde.app-settings.index') }}"
                            class="sb-link {{ request()->routeIs('fde.app-settings.*') ? 'active' : '' }}">
                            <span class="ico">🎨</span> App Settings
                        </a>
                        <a href="{{ route('fde.theme.index') }}"
                            class="sb-link {{ request()->routeIs('fde.theme.*') ? 'active' : '' }}">
                            <span class="ico">🖌️</span> Theme
                        </a>
                        <a href="{{ route('fde.system-reset.index') }}"
                            class="sb-link {{ request()->routeIs('fde.system-reset.*') ? 'active' : '' }}">
                            <span class="ico">⚠️</span> System Reset
                        </a>
                    </div>
                </div>

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

                <a href="{{ route('aeo.staff-strength.index') }}"
                    class="sb-link {{ request()->routeIs('aeo.staff-strength.*') ? 'active' : '' }}">
                    <span class="ico">👨‍🏫</span> Staff Strength
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

                <a href="{{ route('director.staff-strength.index') }}"
                    class="sb-link {{ request()->routeIs('director.staff-strength.*') ? 'active' : '' }}">
                    <span class="ico">👨‍🏫</span> Staff Strength
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

                <a href="{{ route('director.reports.master') }}"
                    class="sb-link {{ request()->routeIs('director.reports.master') ? 'active' : '' }}">
                    <span class="ico">📋</span> Master Report
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
                    <span class="ico">⬇️</span> Export Master (CSV)
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
