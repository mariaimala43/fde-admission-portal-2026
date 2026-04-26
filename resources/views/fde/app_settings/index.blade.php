{{-- SAVE AS: resources/views/fde/app_settings/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Application Settings')

@section('content')
    <div class="fde-page-header">
        <div>
            <h1 class="fde-page-title">⚙️ Application Settings</h1>
            <p class="fde-page-sub">Branding, logo, favicon, colors and system configuration for the entire dashboard</p>
        </div>
        <a href="{{ route('fde.system-reset.index') }}" class="fde-btn fde-btn-danger">
            🔄 System Reset
        </a>
    </div>

    <form method="POST" action="{{ route('fde.app-settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

            {{-- ── LEFT COLUMN ────────────────────────────────────────── --}}
            <div>

                {{-- Branding --}}
                <div class="fde-card" style="margin-bottom:20px;">
                    <div class="fde-card-header">🏷️ Branding</div>
                    <div class="fde-card-body">

                        <div class="fde-form-group">
                            <label class="fde-label">Application Name</label>
                            <input type="text" name="app_name" class="fde-input"
                                value="{{ old('app_name', $settings['app_name']) }}" placeholder="FDE Admission Portal"
                                required>
                            <p class="fde-hint">Shown in browser tab, sidebar header and topbar</p>
                        </div>

                        <div class="fde-form-group">
                            <label class="fde-label">Tagline / Sub-title</label>
                            <input type="text" name="app_tagline" class="fde-input"
                                value="{{ old('app_tagline', $settings['app_tagline']) }}"
                                placeholder="Federal Directorate of Education · 2026–27">
                            <p class="fde-hint">Shown below the title in topbar</p>
                        </div>

                        <div class="fde-form-group">
                            <label class="fde-label">Sidebar Footer Text</label>
                            <input type="text" name="sidebar_footer_text" class="fde-input"
                                value="{{ old('sidebar_footer_text', $settings['sidebar_footer_text']) }}"
                                placeholder="Admission System 2026–27">
                            <p class="fde-hint">Small text under the logo in the sidebar</p>
                        </div>

                    </div>
                </div>

                {{-- Colors --}}
                <div class="fde-card" style="margin-bottom:20px;">
                    <div class="fde-card-header">🎨 Theme Colors</div>
                    <div class="fde-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="fde-form-group">
                                <label class="fde-label">Primary Color</label>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <input type="color" name="primary_color"
                                        value="{{ old('primary_color', $settings['primary_color']) }}"
                                        style="width:48px;height:38px;padding:2px;border-radius:6px;cursor:pointer;border:1px solid var(--border);">
                                    <input type="text" name="primary_color" class="fde-input"
                                        value="{{ old('primary_color', $settings['primary_color']) }}"
                                        pattern="^#[0-9A-Fa-f]{6}$" placeholder="#4bad46" style="font-family:monospace;">
                                </div>
                                <p class="fde-hint">Buttons, badges, active links</p>
                            </div>
                            <div class="fde-form-group">
                                <label class="fde-label">Secondary Color</label>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <input type="color" name="secondary_color"
                                        value="{{ old('secondary_color', $settings['secondary_color']) }}"
                                        style="width:48px;height:38px;padding:2px;border-radius:6px;cursor:pointer;border:1px solid var(--border);">
                                    <input type="text" name="secondary_color" class="fde-input"
                                        value="{{ old('secondary_color', $settings['secondary_color']) }}"
                                        pattern="^#[0-9A-Fa-f]{6}$" placeholder="#1d4ed8" style="font-family:monospace;">
                                </div>
                                <p class="fde-hint">Sidebar, headers, accents</p>
                            </div>
                        </div>
                        <div
                            style="padding:12px;background:var(--card-bg);border-radius:8px;border:1px solid var(--border);margin-top:4px;">
                            <p style="font-size:11px;color:var(--text-muted);margin-bottom:8px;">Preview</p>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <button type="button" id="preview-primary"
                                    style="padding:6px 14px;border:none;border-radius:6px;font-size:12px;cursor:default;font-weight:600;color:#fff;background:{{ $settings['primary_color'] }}">
                                    Primary Button
                                </button>
                                <button type="button" id="preview-secondary"
                                    style="padding:6px 14px;border:none;border-radius:6px;font-size:12px;cursor:default;font-weight:600;color:#fff;background:{{ $settings['secondary_color'] }}">
                                    Secondary Button
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Support --}}
                <div class="fde-card">
                    <div class="fde-card-header">📞 Support Contact</div>
                    <div class="fde-card-body">
                        <div class="fde-form-group">
                            <label class="fde-label">Support Email</label>
                            <input type="email" name="support_email" class="fde-input"
                                value="{{ old('support_email', $settings['support_email']) }}"
                                placeholder="support@fde.edu.pk">
                        </div>
                        <div class="fde-form-group">
                            <label class="fde-label">Support Phone</label>
                            <input type="text" name="support_phone" class="fde-input"
                                value="{{ old('support_phone', $settings['support_phone']) }}"
                                placeholder="+92-51-XXXXXXX">
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── RIGHT COLUMN ────────────────────────────────────────── --}}
            <div>

                {{-- Logo --}}
                <div class="fde-card" style="margin-bottom:20px;">
                    <div class="fde-card-header">🖼️ Dashboard Logo</div>
                    <div class="fde-card-body">
                        @if (!empty($settings['app_logo']))
                            <div
                                style="margin-bottom:12px;padding:12px;background:var(--card-bg);border-radius:8px;border:1px solid var(--border);text-align:center;">
                                <img src="{{ Storage::url($settings['app_logo']) }}" alt="Logo"
                                    style="max-height:80px;max-width:200px;object-fit:contain;">
                                <div style="margin-top:8px;">
                                    <label
                                        style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--danger);cursor:pointer;">
                                        <input type="checkbox" name="remove_app_logo" value="1"> Remove current logo
                                    </label>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="app_logo" class="fde-input"
                            accept="image/png,image/jpeg,image/svg+xml,image/webp">
                        <p class="fde-hint">PNG, JPG, SVG or WebP · Max 2MB · Recommended: 200×60px transparent PNG</p>
                        <p class="fde-hint" style="margin-top:4px;">If no logo uploaded, the emoji icon 🏛️ is used
                            instead</p>
                    </div>
                </div>

                {{-- Favicon --}}
                <div class="fde-card" style="margin-bottom:20px;">
                    <div class="fde-card-header">🔖 Favicon</div>
                    <div class="fde-card-body">
                        @if (!empty($settings['app_favicon']))
                            <div
                                style="margin-bottom:12px;display:flex;align-items:center;gap:12px;padding:10px;background:var(--card-bg);border-radius:8px;border:1px solid var(--border);">
                                <img src="{{ Storage::url($settings['app_favicon']) }}" alt="Favicon"
                                    style="width:32px;height:32px;object-fit:contain;">
                                <div>
                                    <p style="font-size:12px;color:var(--text-muted);">Current favicon</p>
                                    <label
                                        style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--danger);cursor:pointer;">
                                        <input type="checkbox" name="remove_app_favicon" value="1"> Remove
                                    </label>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="app_favicon" class="fde-input"
                            accept="image/png,image/jpeg,image/x-icon">
                        <p class="fde-hint">PNG or ICO · Max 512KB · Recommended: 32×32px or 64×64px</p>
                    </div>
                </div>

                {{-- System --}}
                <div class="fde-card">
                    <div class="fde-card-header">🔧 System</div>
                    <div class="fde-card-body">

                        <div class="fde-form-group">
                            <label class="fde-toggle-row">
                                <div>
                                    <p class="fde-label" style="margin:0;">Show Public Portal Link</p>
                                    <p class="fde-hint" style="margin:0;">Show 🌐 Portal button in topbar and sidebar</p>
                                </div>
                                <input type="hidden" name="show_public_portal" value="0">
                                <input type="checkbox" name="show_public_portal" value="1" class="fde-toggle"
                                    {{ ($settings['show_public_portal'] ?? '1') == '1' ? 'checked' : '' }}>
                            </label>
                        </div>

                        <div class="fde-form-group">
                            <label class="fde-toggle-row" style="border-top:1px solid var(--border);padding-top:12px;">
                                <div>
                                    <p class="fde-label" style="margin:0;color:var(--danger);">🚧 Maintenance Mode</p>
                                    <p class="fde-hint" style="margin:0;">Redirects public portal to maintenance page</p>
                                </div>
                                <input type="hidden" name="maintenance_mode" value="0">
                                <input type="checkbox" name="maintenance_mode" value="1" class="fde-toggle"
                                    {{ ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }}>
                            </label>
                        </div>

                        <div class="fde-form-group">
                            <label class="fde-label">Maintenance Message</label>
                            <textarea name="maintenance_message" class="fde-input" rows="3"
                                placeholder="The portal is currently under maintenance. Please check back later.">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        {{-- Save bar --}}
        <div
            style="position:sticky;bottom:0;background:var(--bg);border-top:1px solid var(--border);padding:14px 0;margin-top:20px;display:flex;justify-content:flex-end;gap:10px;">
            <a href="{{ route('fde.dashboard') }}" class="fde-btn fde-btn-ghost">Cancel</a>
            <button type="submit" class="fde-btn fde-btn-primary">💾 Save Settings</button>
        </div>

    </form>
@endsection

@push('scripts')
    <script>
        // Live color preview
        document.querySelectorAll('input[name="primary_color"]').forEach(inp => {
            inp.addEventListener('input', e => {
                const val = e.target.value;
                document.querySelectorAll('input[name="primary_color"]').forEach(i => i.value = val);
                document.getElementById('preview-primary').style.background = val;
            });
        });
        document.querySelectorAll('input[name="secondary_color"]').forEach(inp => {
            inp.addEventListener('input', e => {
                const val = e.target.value;
                document.querySelectorAll('input[name="secondary_color"]').forEach(i => i.value = val);
                document.getElementById('preview-secondary').style.background = val;
            });
        });
    </script>
@endpush
