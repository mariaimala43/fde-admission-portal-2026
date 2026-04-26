{{-- SAVE AS: resources/views/fde/theme/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Theme Customizer')

@push('styles')
    <style>
        .tc-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .tc-section-hdr {
            padding: 12px 18px;
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tc-body {
            padding: 18px;
        }

        .tc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 14px;
        }

        .tc-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .tc-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .tc-color-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tc-swatch {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid var(--border);
            cursor: pointer;
            flex-shrink: 0;
            padding: 2px;
            background: none;
        }

        .tc-hex {
            font-family: monospace;
            font-size: 12px;
            width: 100%;
        }

        .tc-range {
            width: 100%;
            accent-color: var(--primary-green);
        }

        .tc-range-val {
            font-size: 11px;
            color: var(--primary-green);
            font-weight: 700;
            font-family: monospace;
            min-width: 36px;
            text-align: right;
        }

        /* Preview panel */
        .preview-wrap {
            position: sticky;
            top: 74px;
        }

        .preview-sidebar {
            width: 200px;
            background: var(--preview-sidebar-bg, #0d1235);
            border-radius: 10px 0 0 10px;
            padding: 14px 10px;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, .07);
            border-right: none;
        }

        .preview-topbar {
            background: var(--preview-topbar-bg, rgba(10, 14, 39, .94));
            border-radius: 0 10px 0 0;
            padding: 10px 16px;
            border: 1px solid rgba(255, 255, 255, .07);
            border-bottom: 1px solid rgba(255, 255, 255, .07);
        }

        .preview-content {
            background: var(--preview-bg, #0a0e27);
            border-radius: 0 0 10px 0;
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, .07);
            border-top: none;
            flex: 1;
        }

        .preview-card {
            border-radius: 8px;
            padding: 12px;
            background: var(--preview-card, #1a1f3a);
            border: 1px solid rgba(255, 255, 255, .07);
            margin-bottom: 8px;
        }

        .preview-link {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 6px 8px;
            border-radius: 7px;
            font-size: 11px;
            color: rgba(255, 255, 255, .55);
            margin-bottom: 2px;
        }

        .preview-link.active {
            background: rgba(75, 173, 70, .13);
            color: #6dda67;
            font-weight: 600;
        }

        .preview-btn {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            border: none;
            cursor: default;
        }

        .preview-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="fde-page-header">
        <div>
            <h1 class="fde-page-title">🎨 Theme Customizer</h1>
            <p class="fde-page-sub">Customize colors, sidebar, topbar, cards and typography for the entire dashboard</p>
        </div>
        <div style="display:flex;gap:8px;">
            <form method="POST" action="{{ route('fde.theme.reset') }}" style="display:inline;">
                @csrf
                <button type="submit" class="fde-btn fde-btn-ghost"
                    onclick="return confirm('Reset all theme settings to defaults?')">
                    ↺ Reset Defaults
                </button>
            </form>
            <button form="theme-form" type="submit" class="fde-btn fde-btn-primary">💾 Save Theme</button>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

        {{-- ── LEFT: FORM ──────────────────────────────────────────── --}}
        <div>
            <form id="theme-form" method="POST" action="{{ route('fde.theme.update') }}">
                @csrf @method('PUT')

                {{-- 1. Colors --}}
                <div class="tc-section">
                    <div class="tc-section-hdr">🎨 Brand Colors</div>
                    <div class="tc-body">
                        <div class="tc-grid">
                            @php
                                $colorFields = [
                                    ['theme_primary', 'Primary Color', 'Buttons, active links, badges'],
                                    ['theme_secondary', 'Secondary Color', 'Hover states, secondary actions'],
                                    [
                                        'theme_active_text_dark',
                                        'Active Link (Dark)',
                                        'Active nav link text in dark mode',
                                    ],
                                    [
                                        'theme_active_text_light',
                                        'Active Link (Light)',
                                        'Active nav link text in light mode',
                                    ],
                                ];
                            @endphp
                            @foreach ($colorFields as [$key, $label, $hint])
                                <div class="tc-field">
                                    <span class="tc-label">{{ $label }}</span>
                                    <div class="tc-color-row">
                                        <input type="color" class="tc-swatch" id="swatch_{{ $key }}"
                                            value="{{ $settings[$key] }}"
                                            oninput="syncColor('{{ $key }}', this.value)">
                                        <input type="text" name="{{ $key }}" class="fde-input tc-hex"
                                            id="hex_{{ $key }}" value="{{ $settings[$key] }}"
                                            oninput="syncColor('{{ $key }}', this.value)"
                                            pattern="^#[0-9A-Fa-f]{6}$">
                                    </div>
                                    <span style="font-size:10px;color:var(--text-faint);">{{ $hint }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 2. Dark Mode Backgrounds --}}
                <div class="tc-section">
                    <div class="tc-section-hdr">🌙 Dark Mode Backgrounds</div>
                    <div class="tc-body">
                        <div class="tc-grid">
                            @php
                                $darkFields = [
                                    ['theme_dark_bg', 'Page Background', 'Main page bg'],
                                    ['theme_dark_bg2', 'Page Background 2', 'Secondary bg'],
                                    ['theme_dark_card', 'Card Background', 'Card / panel bg'],
                                    ['theme_dark_sidebar_bg', 'Sidebar Background', 'Sidebar panel bg'],
                                ];
                            @endphp
                            @foreach ($darkFields as [$key, $label, $hint])
                                <div class="tc-field">
                                    <span class="tc-label">{{ $label }}</span>
                                    <div class="tc-color-row">
                                        <input type="color" class="tc-swatch" id="swatch_{{ $key }}"
                                            value="{{ $settings[$key] }}"
                                            oninput="syncColor('{{ $key }}', this.value)">
                                        <input type="text" name="{{ $key }}" class="fde-input tc-hex"
                                            id="hex_{{ $key }}" value="{{ $settings[$key] }}"
                                            oninput="syncColor('{{ $key }}', this.value)"
                                            pattern="^#[0-9A-Fa-f]{6}$">
                                    </div>
                                    <span style="font-size:10px;color:var(--text-faint);">{{ $hint }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 3. Light Mode Backgrounds --}}
                <div class="tc-section">
                    <div class="tc-section-hdr">☀️ Light Mode Backgrounds</div>
                    <div class="tc-body">
                        <div class="tc-grid">
                            @php
                                $lightFields = [
                                    ['theme_light_bg', 'Page Background', 'Main page bg'],
                                    ['theme_light_card', 'Card Background', 'Card / panel bg'],
                                    ['theme_light_sidebar_bg', 'Sidebar Background', 'Sidebar panel bg'],
                                ];
                            @endphp
                            @foreach ($lightFields as [$key, $label, $hint])
                                <div class="tc-field">
                                    <span class="tc-label">{{ $label }}</span>
                                    <div class="tc-color-row">
                                        <input type="color" class="tc-swatch" id="swatch_{{ $key }}"
                                            value="{{ $settings[$key] }}"
                                            oninput="syncColor('{{ $key }}', this.value)">
                                        <input type="text" name="{{ $key }}" class="fde-input tc-hex"
                                            id="hex_{{ $key }}" value="{{ $settings[$key] }}"
                                            oninput="syncColor('{{ $key }}', this.value)"
                                            pattern="^#[0-9A-Fa-f]{6}$">
                                    </div>
                                    <span style="font-size:10px;color:var(--text-faint);">{{ $hint }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 4. Sidebar --}}
                <div class="tc-section">
                    <div class="tc-section-hdr">📐 Sidebar</div>
                    <div class="tc-body" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">

                        <div class="tc-field">
                            <span class="tc-label">Width</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="range" name="theme_sidebar_width" class="tc-range" min="180"
                                    max="380" step="5" value="{{ $settings['theme_sidebar_width'] }}"
                                    oninput="this.nextElementSibling.textContent=this.value+'px';updatePreview()">
                                <span class="tc-range-val">{{ $settings['theme_sidebar_width'] }}px</span>
                            </div>
                            <span style="font-size:10px;color:var(--text-faint);">180–380px</span>
                        </div>

                        <div class="tc-field">
                            <span class="tc-label">Link Font Size</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="range" name="theme_sidebar_font_size" class="tc-range" min="10"
                                    max="16" step="1" value="{{ $settings['theme_sidebar_font_size'] }}"
                                    oninput="this.nextElementSibling.textContent=this.value+'px';updatePreview()">
                                <span class="tc-range-val">{{ $settings['theme_sidebar_font_size'] }}px</span>
                            </div>
                            <span style="font-size:10px;color:var(--text-faint);">10–16px</span>
                        </div>

                        <div class="tc-field">
                            <span class="tc-label">Link Spacing</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="range" name="theme_sidebar_link_py" class="tc-range" min="4"
                                    max="16" step="0.5" value="{{ $settings['theme_sidebar_link_py'] }}"
                                    oninput="this.nextElementSibling.textContent=this.value+'px';updatePreview()">
                                <span class="tc-range-val">{{ $settings['theme_sidebar_link_py'] }}px</span>
                            </div>
                            <span style="font-size:10px;color:var(--text-faint);">4–16px (padding top/bottom)</span>
                        </div>

                    </div>
                </div>

                {{-- 5. Topbar --}}
                <div class="tc-section">
                    <div class="tc-section-hdr">⬆️ Topbar</div>
                    <div class="tc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                        <div class="tc-field">
                            <span class="tc-label">Height</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="range" name="theme_topbar_height" class="tc-range" min="44"
                                    max="80" step="2" value="{{ $settings['theme_topbar_height'] }}"
                                    oninput="this.nextElementSibling.textContent=this.value+'px';updatePreview()">
                                <span class="tc-range-val">{{ $settings['theme_topbar_height'] }}px</span>
                            </div>
                        </div>

                        <div class="tc-field">
                            <span class="tc-label">Title Font Size</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="range" name="theme_topbar_font_size" class="tc-range" min="12"
                                    max="20" step="1" value="{{ $settings['theme_topbar_font_size'] }}"
                                    oninput="this.nextElementSibling.textContent=this.value+'px';updatePreview()">
                                <span class="tc-range-val">{{ $settings['theme_topbar_font_size'] }}px</span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 6. Cards --}}
                <div class="tc-section">
                    <div class="tc-section-hdr">🃏 Cards</div>
                    <div class="tc-body" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">

                        <div class="tc-field">
                            <span class="tc-label">Border Radius</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="range" name="theme_radius" class="tc-range" min="0"
                                    max="24" step="1" value="{{ $settings['theme_radius'] }}"
                                    oninput="this.nextElementSibling.textContent=this.value+'px';updatePreview()">
                                <span class="tc-range-val">{{ $settings['theme_radius'] }}px</span>
                            </div>
                        </div>

                        <div class="tc-field">
                            <span class="tc-label">Small Radius</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="range" name="theme_radius_sm" class="tc-range" min="0"
                                    max="16" step="1" value="{{ $settings['theme_radius_sm'] }}"
                                    oninput="this.nextElementSibling.textContent=this.value+'px';updatePreview()">
                                <span class="tc-range-val">{{ $settings['theme_radius_sm'] }}px</span>
                            </div>
                        </div>

                        <div class="tc-field">
                            <span class="tc-label">Card Padding</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="range" name="theme_card_padding" class="tc-range" min="8"
                                    max="40" step="2" value="{{ $settings['theme_card_padding'] }}"
                                    oninput="this.nextElementSibling.textContent=this.value+'px';updatePreview()">
                                <span class="tc-range-val">{{ $settings['theme_card_padding'] }}px</span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 7. Typography --}}
                <div class="tc-section">
                    <div class="tc-section-hdr">🔤 Typography</div>
                    <div class="tc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                        <div class="tc-field">
                            <span class="tc-label">Font Family</span>
                            <select name="theme_font_family" class="fde-input" onchange="updatePreview()">
                                @foreach (['Inter', 'Roboto', 'Poppins', 'DM Sans', 'Nunito', 'Outfit'] as $font)
                                    <option value="{{ $font }}"
                                        {{ $settings['theme_font_family'] === $font ? 'selected' : '' }}
                                        style="font-family:'{{ $font }}'">{{ $font }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="tc-field">
                            <span class="tc-label">Base Font Size</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="range" name="theme_base_font_size" class="tc-range" min="12"
                                    max="18" step="1" value="{{ $settings['theme_base_font_size'] }}"
                                    oninput="this.nextElementSibling.textContent=this.value+'px';updatePreview()">
                                <span class="tc-range-val">{{ $settings['theme_base_font_size'] }}px</span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 8. Default Mode --}}
                <div class="tc-section">
                    <div class="tc-section-hdr">🌓 Default Theme Mode</div>
                    <div class="tc-body">
                        <div style="display:flex;gap:12px;">
                            <label
                                style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 18px;border-radius:10px;border:2px solid {{ $settings['theme_default_mode'] === 'dark' ? 'var(--primary-green)' : 'var(--border)' }};flex:1;">
                                <input type="radio" name="theme_default_mode" value="dark"
                                    {{ $settings['theme_default_mode'] === 'dark' ? 'checked' : '' }}>
                                <span style="font-size:20px;">🌙</span>
                                <div>
                                    <p style="font-weight:700;font-size:13px;">Dark Mode</p>
                                    <p style="font-size:11px;color:var(--text-muted);">Default for all users</p>
                                </div>
                            </label>
                            <label
                                style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 18px;border-radius:10px;border:2px solid {{ $settings['theme_default_mode'] === 'light' ? 'var(--primary-green)' : 'var(--border)' }};flex:1;">
                                <input type="radio" name="theme_default_mode" value="light"
                                    {{ $settings['theme_default_mode'] === 'light' ? 'checked' : '' }}>
                                <span style="font-size:20px;">☀️</span>
                                <div>
                                    <p style="font-weight:700;font-size:13px;">Light Mode</p>
                                    <p style="font-size:11px;color:var(--text-muted);">Default for all users</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        {{-- ── RIGHT: LIVE PREVIEW ─────────────────────────────────── --}}
        <div class="preview-wrap">
            <div class="tc-section">
                <div class="tc-section-hdr">👁 Live Preview</div>
                <div style="padding:14px;">

                    {{-- Mini sidebar + topbar + content --}}
                    <div id="preview-root" style="border-radius:10px;overflow:hidden;border:1px solid var(--border);">

                        {{-- Topbar --}}
                        <div id="p-topbar"
                            style="
                    background:#0a0e1f;
                    padding:10px 14px;
                    display:flex;align-items:center;justify-content:space-between;
                    border-bottom:1px solid rgba(255,255,255,.07);">
                            <div>
                                <p id="p-topbar-title" style="font-size:13px;font-weight:700;color:#fff;">Dashboard</p>
                                <p style="font-size:10px;color:rgba(255,255,255,.45);">FDE Admission Portal</p>
                            </div>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <div id="p-live" style="width:7px;height:7px;border-radius:50%;background:#4bad46;">
                                </div>
                                <span style="font-size:9px;color:#4bad46;font-weight:600;">Live</span>
                            </div>
                        </div>

                        <div style="display:flex;min-height:200px;">
                            {{-- Sidebar --}}
                            <div id="p-sidebar"
                                style="
                        width:120px;flex-shrink:0;
                        background:#0d1235;
                        padding:10px 8px;
                        border-right:1px solid rgba(255,255,255,.07);">

                                <div
                                    style="font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.3);padding:8px 4px 4px;">
                                    Navigation</div>

                                <div id="p-link-active" class="preview-link active" style="margin-bottom:3px;">
                                    <span style="font-size:11px;">🏠</span>
                                    <span id="p-link-active-text" style="font-size:10px;">Dashboard</span>
                                </div>
                                <div class="preview-link" style="margin-bottom:3px;">
                                    <span style="font-size:11px;">🏫</span>
                                    <span style="font-size:10px;">Schools</span>
                                </div>
                                <div class="preview-link">
                                    <span style="font-size:11px;">📊</span>
                                    <span style="font-size:10px;">Reports</span>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div id="p-content" style="flex:1;background:#0a0e27;padding:12px;">

                                {{-- Cards --}}
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
                                    <div id="p-card-1"
                                        style="background:#1a1f3a;border-radius:8px;padding:10px;border:1px solid rgba(255,255,255,.07);">
                                        <p
                                            style="font-size:8px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;">
                                            Schools</p>
                                        <p id="p-stat" style="font-size:18px;font-weight:800;color:#4bad46;">56</p>
                                    </div>
                                    <div id="p-card-2"
                                        style="background:#1a1f3a;border-radius:8px;padding:10px;border:1px solid rgba(255,255,255,.07);">
                                        <p
                                            style="font-size:8px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;">
                                            Admitted</p>
                                        <p style="font-size:18px;font-weight:800;color:#60a5fa;">1,240</p>
                                    </div>
                                </div>

                                {{-- Button + Badge --}}
                                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                    <button id="p-btn-primary" class="preview-btn"
                                        style="background:#4bad46;">Save</button>
                                    <button id="p-btn-secondary" class="preview-btn"
                                        style="background:#28a745;">Export</button>
                                    <span id="p-badge" class="preview-badge"
                                        style="background:rgba(75,173,70,.15);color:#6dda67;border:1px solid rgba(75,173,70,.3);">Active</span>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Font preview --}}
                    <div
                        style="margin-top:12px;padding:12px;background:var(--card);border-radius:8px;border:1px solid var(--border);">
                        <p id="p-font-preview" style="font-size:13px;font-weight:600;">The quick brown fox — FDE Portal
                        </p>
                        <p id="p-font-sub" style="font-size:11px;color:var(--text-muted);margin-top:3px;">
                            abcdefghijklmnopqrstuvwxyz 0123456789</p>
                        <p id="p-font-name" style="font-size:10px;color:var(--text-faint);margin-top:4px;">Font:
                            {{ $settings['theme_font_family'] }}</p>
                    </div>

                </div>
            </div>
        </div>

    </div>{{-- end grid --}}
@endsection

@push('scripts')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Roboto:wght@400;600;700&family=Poppins:wght@400;600;700&family=DM+Sans:wght@400;600;700&family=Nunito:wght@400;600;700&family=Outfit:wght@400;600;700&display=swap"
        rel="stylesheet">

    <script>
        function syncColor(key, value) {
            document.getElementById('swatch_' + key).value = value;
            document.getElementById('hex_' + key).value = value;
            updatePreview();
        }

        function val(name) {
            const el = document.querySelector('[name="' + name + '"]');
            return el ? el.value : '';
        }

        function updatePreview() {
            const primary = val('theme_primary') || '#4bad46';
            const secondary = val('theme_secondary') || '#28a745';
            const darkBg = val('theme_dark_bg') || '#0a0e27';
            const darkCard = val('theme_dark_card') || '#1a1f3a';
            const darkSb = val('theme_dark_sidebar_bg') || '#0d1235';
            const activeD = val('theme_active_text_dark') || '#6dda67';
            const radius = val('theme_radius') || '12';
            const radiusSm = val('theme_radius_sm') || '8';
            const pad = val('theme_card_padding') || '20';
            const font = val('theme_font_family') || 'Inter';
            const fontSize = val('theme_base_font_size') || '14';
            const topbarH = val('theme_topbar_height') || '58';
            const topbarFs = val('theme_topbar_font_size') || '15';
            const sbFs = val('theme_sidebar_font_size') || '13';
            const sbPy = val('theme_sidebar_link_py') || '8.5';

            // Sidebar
            document.getElementById('p-sidebar').style.background = darkSb;
            document.getElementById('p-sidebar').style.width = '120px';

            // Topbar
            document.getElementById('p-topbar').style.minHeight = Math.round(topbarH * 0.55) + 'px';
            document.getElementById('p-topbar-title').style.fontSize = Math.round(topbarFs * 0.8) + 'px';

            // Content bg
            document.getElementById('p-content').style.background = darkBg;

            // Cards
            ['p-card-1', 'p-card-2'].forEach(id => {
                document.getElementById(id).style.background = darkCard;
                document.getElementById(id).style.borderRadius = Math.round(radius * 0.6) + 'px';
                document.getElementById(id).style.padding = Math.round(pad * 0.5) + 'px';
            });

            // Active link
            document.getElementById('p-link-active').style.background = primary + '22';
            document.getElementById('p-link-active').style.color = activeD;
            document.getElementById('p-link-active').style.borderRadius = radiusSm + 'px';
            document.getElementById('p-link-active-text').style.fontSize = Math.round(sbFs * 0.75) + 'px';
            document.getElementById('p-link-active').style.padding = sbPy * 0.5 + 'px 6px';

            // Stat color
            document.getElementById('p-stat').style.color = primary;
            document.getElementById('p-live').style.background = primary;

            // Buttons
            document.getElementById('p-btn-primary').style.background = primary;
            document.getElementById('p-btn-secondary').style.background = secondary;
            document.getElementById('p-btn-primary').style.borderRadius = radiusSm + 'px';
            document.getElementById('p-btn-secondary').style.borderRadius = radiusSm + 'px';

            // Badge
            document.getElementById('p-badge').style.color = activeD;
            document.getElementById('p-badge').style.background = primary + '26';

            // Font preview
            const fontStack = "'" + font + "', sans-serif";
            document.getElementById('p-font-preview').style.fontFamily = fontStack;
            document.getElementById('p-font-sub').style.fontFamily = fontStack;
            document.getElementById('p-font-preview').style.fontSize = Math.round(fontSize * 0.9) + 'px';
            document.getElementById('p-font-name').textContent = 'Font: ' + font + ' · ' + fontSize + 'px';
        }

        // Init preview on load
        document.addEventListener('DOMContentLoaded', updatePreview);

        // Sync hex input → color swatch on manual type
        document.querySelectorAll('input[type="text"].tc-hex').forEach(inp => {
            inp.addEventListener('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                    const key = this.name;
                    document.getElementById('swatch_' + key).value = this.value;
                    updatePreview();
                }
            });
        });
    </script>
@endpush
