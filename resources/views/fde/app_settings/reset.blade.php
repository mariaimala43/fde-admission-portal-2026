{{-- SAVE AS: resources/views/fde/app_settings/reset.blade.php --}}
@extends('layouts.app')
@section('title', 'System Reset')

@section('content')
    <div class="fde-page-header">
        <div>
            <h1 class="fde-page-title" style="color:var(--danger);">⚠️ System Reset</h1>
            <p class="fde-page-sub">Wipe all data and re-seed the database from scratch</p>
        </div>
        <a href="{{ route('fde.app-settings.index') }}" class="fde-btn fde-btn-ghost">← Back to Settings</a>
    </div>

    {{-- Warning banner --}}
    <div class="fde-alert fde-alert-error" style="margin-bottom:24px;font-size:14px;line-height:1.6;">
        <div>
            <strong style="font-size:15px;display:block;margin-bottom:6px;">🚨 This action is irreversible</strong>
            <p>All data in the database will be <strong>permanently deleted</strong> and replaced with fresh seeded data.
                This includes:</p>
            <ul style="margin:8px 0 0 20px;">
                <li>All daily admission records</li>
                <li>All enrollment records</li>
                <li>All transfers, referrals, and corrections</li>
                <li>All monitoring records and audit logs</li>
                <li>All users (you will be logged out)</li>
                <li>All institutions, sectors, classes, and academic years</li>
                <li>All application settings and branding</li>
            </ul>
            <p style="margin-top:8px;">After the reset, the system will be re-seeded using all registered seeders. You will
                need to log in again with the default admin credentials.</p>
        </div>
    </div>

    {{-- What will be re-seeded --}}
    <div class="fde-card" style="margin-bottom:24px;">
        <div class="fde-card-header">✅ What will be re-seeded after reset</div>
        <div class="fde-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;">
                @foreach ([['🔐', 'RolesSeeder', 'Roles & Permissions'], ['📚', 'ClassesSeeder', 'Class definitions'], ['📅', 'AcademicYearSeeder', 'Academic Year 2026–27'], ['👤', 'AdminUserSeeder', 'Default admin user'], ['🏘️', 'UnionCouncilSeeder', 'Union Councils'], ['🗺️', 'SectorSeeder', 'Sectors'], ['🏫', 'InstitutionSeeder', 'All 56 schools'], ['🔄', 'ResetAllSchoolsSeeder', 'School configurations'], ['🏗️', 'NewConstructionRoomsSeeder', 'Construction rooms']] as [$icon, $class, $label])
                    <div
                        style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:var(--card-bg);border-radius:8px;border:1px solid var(--border);">
                        <span style="font-size:18px;">{{ $icon }}</span>
                        <div>
                            <p style="font-size:12px;font-weight:700;margin:0;">{{ $class }}</p>
                            <p style="font-size:11px;color:var(--text-muted);margin:0;">{{ $label }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <p style="margin-top:12px;font-size:12px;color:var(--text-muted);">
                ⚠️ <code>TestingSeeder</code> will NOT run — it only runs in non-production environments.
            </p>
        </div>
    </div>

    {{-- Confirmation form --}}
    <div class="fde-card" style="max-width:520px;">
        <div class="fde-card-header" style="color:var(--danger);">🔒 Confirm Reset</div>
        <div class="fde-card-body">
            <form method="POST" action="{{ route('fde.system-reset.execute') }}" x-data="{ typed: '', confirmed: false }"
                @submit.prevent="if(typed === 'RESET SYSTEM') { confirmed = true; $el.submit(); }">
                @csrf

                <div class="fde-form-group">
                    <label class="fde-label">
                        Type <code
                            style="background:var(--card-bg);padding:2px 6px;border-radius:4px;font-size:13px;color:var(--danger);font-weight:700;">RESET
                            SYSTEM</code> to confirm
                    </label>
                    <input type="text" name="confirmation" class="fde-input" x-model="typed"
                        placeholder="Type: RESET SYSTEM" autocomplete="off"
                        style="font-family:monospace;font-size:15px;letter-spacing:1px;">
                    @error('confirmation')
                        <p class="fde-hint" style="color:var(--danger);">{{ $message }}</p>
                    @enderror
                </div>

                <div style="display:flex;gap:10px;align-items:center;margin-top:4px;">
                    <button type="submit" :disabled="typed !== 'RESET SYSTEM'"
                        :class="typed === 'RESET SYSTEM' ? 'fde-btn fde-btn-danger' : 'fde-btn fde-btn-ghost'"
                        style="transition:all .2s;">
                        🔄 Execute System Reset
                    </button>
                    <a href="{{ route('fde.app-settings.index') }}" class="fde-btn fde-btn-ghost">Cancel</a>
                </div>

                <p style="margin-top:12px;font-size:11px;color:var(--text-muted);">
                    This action will be logged. You will be automatically signed out when the reset completes.
                </p>
            </form>
        </div>
    </div>
@endsection
