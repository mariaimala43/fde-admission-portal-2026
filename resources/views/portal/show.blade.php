<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $institution->name }} — {{ $settings['portal_title'] ?? 'FDE Admission Portal' }}</title>
    @if (!empty($settings['portal_favicon']))
        <link rel="icon" type="image/png" href="{{ Storage::url($settings['portal_favicon']) }}">
    @elseif(!empty($settings['app_favicon']))
        <link rel="icon" type="image/png" href="{{ Storage::url($settings['app_favicon']) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Nastaliq+Urdu:wght@400;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --bg: #0d1117;
            --bg2: #0f1520;
            --bg3: #131c2e;
            --surface: rgba(255, 255, 255, 0.035);
            --surface-h: rgba(255, 255, 255, 0.065);
            --border: rgba(255, 255, 255, 0.07);
            --border-g: rgba(74, 160, 110, 0.4);
            --green: #4aa06e;
            --green-d: #3a8a5c;
            --green-glow: rgba(74, 160, 110, 0.2);
            --green-soft: rgba(74, 160, 110, 0.12);
            --green-text: #74c99a;
            --muted: #7a8a96;
            --text: #dde4ee;
            --white: #ffffff;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .urdu {
            font-family: 'Noto Nastaliq Urdu', serif;
            direction: rtl;
            line-height: 2.5;
        }

        [x-cloak] {
            display: none !important;
        }

        .page-bg {
            background:
                radial-gradient(ellipse 80% 60% at 0% 20%, rgba(60, 130, 90, 0.11) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 100% 60%, rgba(20, 60, 120, 0.1) 0%, transparent 55%),
                var(--bg);
        }

        .navbar {
            background: rgba(13, 17, 23, 0.88);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(160deg, #0f1a20 0%, #0d1117 100%);
            position: relative;
            overflow: hidden;
        }

        /* ── Glass card ── */
        .glass {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            backdrop-filter: blur(10px);
            transition: all 0.28s ease;
        }

        .glass:hover {
            background: var(--surface-h);
            border-color: var(--border-g);
        }

        /* ── Icon box ── */
        .icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(74, 160, 110, 0.2);
            border: 1px solid rgba(74, 160, 110, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        /* ── Badges ── */
        .bdg {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }

        .bdg-open {
            background: rgba(74, 160, 110, 0.16);
            color: var(--green-text);
            border: 1px solid rgba(74, 160, 110, 0.28);
        }

        .bdg-full {
            background: rgba(239, 68, 68, 0.12);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.22);
        }

        .bdg-tag {
            background: rgba(255, 255, 255, 0.05);
            color: var(--muted);
            border: 1px solid var(--border);
        }

        .bdg-purple {
            background: rgba(139, 92, 246, 0.14);
            color: #c4b5fd;
            border: 1px solid rgba(139, 92, 246, 0.24);
        }

        .bdg-pink {
            background: rgba(236, 72, 153, 0.12);
            color: #f9a8d4;
            border: 1px solid rgba(236, 72, 153, 0.22);
        }

        /* ── Info row ── */
        .info-lbl {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
            margin-bottom: 3px;
        }

        .info-val {
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
        }

        /* ── Table ── */
        .dt {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .dt thead tr {
            border-bottom: 1px solid var(--border);
        }

        .dt th {
            padding: 12px 16px;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
        }

        .dt th:first-child {
            text-align: left;
        }

        .dt td {
            padding: 13px 16px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .dt td:first-child {
            text-align: left;
            font-weight: 600;
            color: var(--white);
        }

        .dt tbody tr:hover {
            background: rgba(255, 255, 255, 0.025);
        }

        .dt tfoot tr td {
            font-weight: 700;
            background: rgba(255, 255, 255, 0.03);
            border-top: 1px solid var(--border);
        }

        /* ── Progress bar ── */
        .prog {
            height: 4px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 2px;
            overflow: hidden;
            margin-top: 5px;
        }

        .prog-fill {
            height: 100%;
            border-radius: 2px;
        }

        /* ── Green button ── */
        .btn-g {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--green);
            color: white;
            padding: 10px 26px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .25s;
        }

        .btn-g:hover {
            background: var(--green-d);
            box-shadow: 0 0 28px rgba(74, 160, 110, 0.4);
            transform: translateY(-1px);
        }

        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 10px var(--green);
            display: inline-block;
        }

        /* ── Animations ── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .au {
            animation: fadeUp .5s ease both;
        }

        .au1 {
            animation-delay: .08s;
        }

        .au2 {
            animation-delay: .16s;
        }

        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--green-d);
            border-radius: 3px;
        }
    </style>
</head>

@php
    $bannerEnabled  = !empty($settings['banner_enabled']);
    $bannerMsg      = $settings['banner_text'] ?? null;
    $defaultBanner  = 'Admissions are open for Academic Year ' .
        ($academicYear?->name ?? '2026–27') .
        '. Visit the school directly to complete enrollment.';
    $bannerDisplay  = $bannerMsg ?: $defaultBanner;
    $bannerColour   = $settings['banner_colour'] ?? 'amber';
    $bannerImageUrl = !empty($settings['banner_image'])
        ? asset('storage/' . $settings['banner_image']) : null;
    $bannerBg       = $bannerImageUrl
        ? "background:url('{$bannerImageUrl}') center/cover no-repeat;background-color:#000;"
        : match($bannerColour) {
            'blue'  => 'background:#2563EB;',
            'green' => 'background:#16a34a;',
            'red'   => 'background:#dc2626;',
            'navy'  => 'background:#1B3A6B;',
            default => 'background:#f59e0b;',
          };
@endphp

<body class="page-bg" x-data="{
    lang: 'en',
    bannerOpen: false,
    init() {
        this.bannerOpen = {{ $bannerEnabled ? 'true' : 'false' }} && !sessionStorage.getItem('fde_banner_dismissed');
    },
    dismissBanner() {
        this.bannerOpen = false;
        sessionStorage.setItem('fde_banner_dismissed', '1');
    }
}" :dir="lang === 'ur' ? 'rtl' : 'ltr'">

    {{-- Portal notice --}}
    @if (!empty($settings['portal_notice']))
        <div style="background:rgba(234,179,8,0.12);border-bottom:1px solid rgba(234,179,8,0.25);"
            class="py-2 px-4 text-center text-xs font-semibold text-yellow-300">
            ⚠️ {{ $settings['portal_notice'] }}
        </div>
    @endif

    {{-- ── Full-page Banner Overlay ── --}}
    <div x-show="bannerOpen" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click.self="dismissBanner()"
         style="position:fixed;inset:0;z-index:50;{{ $bannerBg }};
                display:flex;flex-direction:column;align-items:center;justify-content:center;">

        @if($bannerImageUrl)
            {{-- Image banner: full-screen image with dismiss bar at bottom --}}
            <div style="position:absolute;inset:0;overflow:hidden;">
                <img src="{{ $bannerImageUrl }}" alt="Banner"
                     style="width:100%;height:100%;object-fit:contain;background:#000;">
            </div>
            <div style="position:absolute;bottom:0;left:0;right:0;
                        background:linear-gradient(transparent,rgba(0,0,0,0.75));
                        padding:1.5rem 2rem;display:flex;flex-direction:column;align-items:center;gap:0.75rem;">
                @if(!empty($settings['banner_link_text']) && !empty($settings['banner_link_url']))
                <a href="{{ $settings['banner_link_url'] }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-full text-sm font-semibold"
                   style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.35);">
                    {{ $settings['banner_link_text'] }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endif
                <button @click="dismissBanner()"
                        class="px-8 py-2.5 rounded-full font-bold text-sm transition"
                        style="background:#fff;color:#1f2937;"
                        onmouseover="this.style.background='#f3f4f6'"
                        onmouseout="this.style.background='#fff'">
                    Continue to Portal →
                </button>
                <p style="font-size:0.65rem;color:rgba(255,255,255,0.4);">Tap anywhere on image to dismiss</p>
            </div>
        @else
            {{-- Text/colour banner: centred card --}}
            <div style="max-width:560px;width:100%;margin:1.5rem;background:rgba(0,0,0,0.2);
                        border:1px solid rgba(255,255,255,0.2);border-radius:1.5rem;
                        padding:2.5rem;text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:1rem;">📢</div>
                <p class="text-white font-bold text-xl leading-snug" style="margin-bottom:1.25rem;">
                    {{ $bannerDisplay }}
                </p>
                @if(!empty($settings['banner_link_text']) && !empty($settings['banner_link_url']))
                <div style="margin-bottom:1rem;">
                    <a href="{{ $settings['banner_link_url'] }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full text-sm font-semibold"
                       style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3);">
                        {{ $settings['banner_link_text'] }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                @endif
                <button @click="dismissBanner()"
                        class="px-8 py-3 rounded-full font-bold text-sm transition"
                        style="background:#fff;color:#1f2937;"
                        onmouseover="this.style.background='#f3f4f6'"
                        onmouseout="this.style.background='#fff'">
                    Continue to Portal →
                </button>
                <p style="font-size:0.7rem;margin-top:0.75rem;color:rgba(255,255,255,0.45);">
                    Click anywhere outside to dismiss
                </p>
            </div>
        @endif
    </div>

    {{-- ── Navbar ── --}}
    <nav class="navbar">
        <div class="max-w-5xl mx-auto px-5 py-3.5 flex items-center justify-between gap-6">
            <a href="{{ route('portal.index') }}" class="flex items-center gap-3 no-underline shrink-0">
                @if (!empty($settings['portal_logo']))
                    <img src="{{ Storage::url($settings['portal_logo']) }}"
                        alt="{{ $settings['portal_title'] ?? 'FDE' }}"
                        style="height:36px;width:auto;object-fit:contain;">
                @elseif(!empty($settings['app_logo']))
                    <img src="{{ Storage::url($settings['app_logo']) }}" alt="{{ $settings['app_name'] ?? 'FDE' }}"
                        style="height:36px;width:auto;object-fit:contain;">
                @else
                    <div class="w-9 h-9 rounded-full flex items-center justify-center"
                        style="background:rgba(74,160,110,0.18);border:1px solid rgba(74,160,110,0.35);">
                        <span style="font-size:18px;">🏛️</span>
                    </div>
                @endif
                <div class="hidden sm:block">
                    <p class="text-sm font-bold text-white leading-tight">
                        {{ $settings['portal_title'] ?? 'FDE Admission Portal' }}</p>
                    <p class="text-xs" style="color:var(--muted);">
                        {{ $settings['portal_tagline'] ?? 'Government of Pakistan' }}</p>
                </div>
            </a>
            <div class="flex items-center gap-2.5">
                <button @click="lang = lang === 'en' ? 'ur' : 'en'"
                    class="text-xs px-3 py-1.5 rounded-full border transition font-medium hidden sm:block"
                    style="border-color:var(--border);color:var(--muted);"
                    onmouseover="this.style.borderColor='var(--border-g)';this.style.color='var(--green-text)'"
                    onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
                    <span x-show="lang === 'en'">اردو</span>
                    <span x-show="lang === 'ur'" x-cloak class="urdu" style="font-size:11px;">English</span>
                </button>
                <a href="{{ route('login') }}" class="text-sm px-4 py-2 rounded-full border font-medium transition"
                    style="border-color:var(--border);color:var(--text);"
                    onmouseover="this.style.borderColor='rgba(255,255,255,0.2)'"
                    onmouseout="this.style.borderColor='var(--border)'">Sign In</a>
                <a href="{{ route('login') }}" class="btn-g" style="padding:8px 20px;font-size:13px;">Get Access</a>
            </div>
        </div>
    </nav>

    {{-- ── Hero ── --}}
    @php
        // Combined totals
        $totalSeats = $seatData->sum('total_seats');
        $totalExist = $seatData->sum('existing_enrollment');
        $totalAdmit = $admissionTotal->sum('total_admitted');
        $totalAvail = max(0, $totalSeats - $totalExist - $totalAdmit);
        $fillPct    = $totalSeats > 0 ? min(100, round((($totalExist + $totalAdmit) / $totalSeats) * 100)) : 0;

        // Morning / Evening totals (only used when $hasEveningShift)
        $mornSeats = $seatData->sum('morning_seats');
        $evenSeats = $seatData->sum('evening_seats');
        $mornExist = $seatData->sum('morning_existing');
        $evenExist = $seatData->sum('evening_existing');
        $mornAdmit = $admissionByShift->sum('morning_admitted');
        $evenAdmit = $admissionByShift->sum('evening_admitted');
        $mornAvail = max(0, $mornSeats - $mornExist - $mornAdmit);
        $evenAvail = max(0, $evenSeats - $evenExist - $evenAdmit);
    @endphp

    <section class="hero">
        <div class="absolute pointer-events-none"
            style="width:600px;height:600px;top:-180px;left:-100px;background:radial-gradient(circle,rgba(60,130,90,0.12),transparent 65%);border-radius:50%;">
        </div>
        <div class="absolute pointer-events-none"
            style="width:400px;height:400px;top:-50px;right:-80px;background:radial-gradient(circle,rgba(20,60,140,0.09),transparent 65%);border-radius:50%;">
        </div>

        <div class="max-w-5xl mx-auto px-5 pt-12 pb-20 relative z-10">

            {{-- Back --}}
            <a href="{{ route('portal.index') }}"
                class="inline-flex items-center gap-2 text-xs font-medium mb-8 transition" style="color:var(--muted);"
                onmouseover="this.style.color='var(--green-text)'" onmouseout="this.style.color='var(--muted)'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                <span x-show="lang === 'en'">Back to All Schools</span>
                <span x-show="lang === 'ur'" x-cloak class="urdu">تمام اسکولوں پر واپس</span>
            </a>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">

                {{-- School title --}}
                <div class="flex-1 au">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="dot"></span>
                        <span class="text-xs font-semibold tracking-widest uppercase" style="color:var(--green-text);">
                            {{ $institution->sector?->name }} Sector
                        </span>
                    </div>
                    <h1 class="font-extrabold text-white leading-tight mb-5" style="font-size:clamp(1.8rem,4vw,3rem);">
                        {{ $institution->name }}
                    </h1>
                    <div class="flex flex-wrap gap-2">
                        <span class="bdg bdg-tag">{{ $institution->type }}</span>
                        <span class="bdg bdg-tag">{{ ucfirst(str_replace('_', ' ', $institution->gender)) }}</span>
                        <span class="bdg bdg-tag">{{ ucfirst($institution->shift) }} Shift</span>
                        @if ($institution->is_cambridge)
                            <span class="bdg bdg-purple">🎓 Cambridge</span>
                        @endif
                        @if ($institution->has_ece)
                            <span class="bdg bdg-pink">👶 ECE</span>
                        @endif
                        <span class="bdg {{ $institution->admission_status === 'open' ? 'bdg-open' : 'bdg-full' }}"
                            style="font-size:12px;padding:4px 12px;">
                            {{ ucfirst($institution->admission_status) }}
                        </span>
                    </div>
                </div>

                {{-- Availability panel --}}
                <div class="shrink-0 glass p-6 text-center au au1" style="min-width:200px;">
                    <p class="text-xs font-semibold tracking-widest uppercase mb-3" style="color:var(--muted);">Seats
                        Available</p>
                    <p class="font-extrabold leading-none mb-1"
                        style="font-size:3.6rem;color:{{ $totalAvail > 0 ? 'var(--green-text)' : '#fca5a5' }};">
                        {{ number_format($totalAvail) }}
                    </p>
                    <p class="text-xs mb-4" style="color:var(--muted);">of {{ number_format($totalSeats) }} total seats</p>

                    @if ($hasEveningShift)
                        {{-- Morning / Evening split --}}
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <div style="background:rgba(134,239,172,0.07);border:1px solid rgba(134,239,172,0.18);border-radius:8px;padding:7px 8px;">
                                <p class="text-xs mb-0.5" style="color:var(--muted);">🌅 Morning</p>
                                <p class="text-base font-bold" style="color:{{ $mornAvail > 0 ? '#86efac' : '#fca5a5' }};">{{ number_format($mornAvail) }}</p>
                                <p class="text-xs" style="color:var(--muted);">/ {{ number_format($mornSeats) }}</p>
                            </div>
                            <div style="background:rgba(147,197,253,0.07);border:1px solid rgba(147,197,253,0.18);border-radius:8px;padding:7px 8px;">
                                <p class="text-xs mb-0.5" style="color:var(--muted);">🌙 Evening</p>
                                <p class="text-base font-bold" style="color:{{ $evenAvail > 0 ? '#93c5fd' : '#fca5a5' }};">{{ number_format($evenAvail) }}</p>
                                <p class="text-xs" style="color:var(--muted);">/ {{ number_format($evenSeats) }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Progress --}}
                    <div class="prog">
                        <div class="prog-fill"
                            style="width:{{ $fillPct }}%;background:{{ $fillPct < 80 ? 'var(--green)' : ($fillPct < 95 ? '#f97316' : '#ef4444') }};">
                        </div>
                    </div>
                    <p class="text-xs mt-1.5" style="color:var(--muted);">{{ $fillPct }}% filled</p>
                    @if ($academicYear && !empty($academicYear->admission_end))
                        <div class="mt-4 pt-4" style="border-top:1px solid var(--border);">
                            <p class="text-xs" style="color:var(--muted);">Open until</p>
                            <p class="text-sm font-bold text-white mt-0.5">
                                {{ \Carbon\Carbon::parse($academicYear->admission_end)->format('d M Y') }}
                            </p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>

    {{-- ── Per-school Merit List Files ─────────────────────────────────────── --}}
    @if($meritLists->isNotEmpty())
    <div class="max-w-5xl mx-auto px-5 pt-6">
        <div class="rounded-xl overflow-hidden"
             style="background:linear-gradient(90deg,#1e3a8a 0%,#2563eb 100%);
                    box-shadow:0 4px 16px rgba(37,99,235,0.35);">
            <div class="px-5 py-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-lg">📋</span>
                    <p class="font-bold text-white text-sm">Merit List</p>
                </div>
                @foreach($meritLists as $ml)
                <div class="flex items-center justify-between gap-3 py-2.5
                            border-b border-white/10 last:border-0">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="flex-shrink-0 text-base">{{ $ml->fileIcon() }}</span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <p class="text-sm text-white font-medium leading-snug">
                                    {{ $ml->title ?: $ml->original_name }}
                                </p>
                                @if($ml->isNew())
                                    <span class="text-xs font-semibold px-1.5 rounded"
                                          style="background:rgba(74,222,128,0.2);color:#86efac;
                                                 border:1px solid rgba(74,222,128,0.3);line-height:1.6;">
                                        New
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs mt-0.5" style="color:#bfdbfe;">
                                {{ $ml->fileType() }}
                                @if($ml->file_size) &middot; {{ $ml->formattedSize() }} @endif
                                &middot; {{ $ml->created_at->format('d M Y') }}
                                @if($ml->title) &middot; {{ $ml->original_name }} @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ $ml->downloadUrl() }}"
                       target="_blank" rel="noopener"
                       class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5
                              rounded-lg text-xs font-semibold transition"
                       style="background:#fff;color:#1d4ed8;"
                       onmouseover="this.style.background='#eff6ff'"
                       onmouseout="this.style.background='#fff'">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── Main Content ── --}}
    <div class="max-w-5xl mx-auto px-5 py-10 space-y-5">

        {{-- Info + Facilities row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 au">

            {{-- School Details --}}
            <div class="glass md:col-span-2 p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="icon-box" style="width:36px;height:36px;border-radius:10px;font-size:16px;">ℹ️</div>
                    <p class="text-sm font-semibold text-white">School Information</p>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    @if ($institution->address)
                        <div>
                            <p class="info-lbl">Address</p>
                            <p class="info-val">{{ $institution->address }}</p>
                        </div>
                    @endif
                    @if ($institution->contact_number)
                        <div>
                            <p class="info-lbl">Contact</p>
                            <p class="info-val">{{ $institution->contact_number }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="info-lbl">Level</p>
                        <p class="info-val">{{ $institution->type }}</p>
                    </div>
                    <div>
                        <p class="info-lbl">Shift</p>
                        <p class="info-val">{{ ucfirst($institution->shift) }}</p>
                    </div>
                    <div>
                        <p class="info-lbl">Gender</p>
                        <p class="info-val">{{ ucfirst(str_replace('_', ' ', $institution->gender)) }}</p>
                    </div>
                    <div>
                        <p class="info-lbl">Sector</p>
                        <p class="info-val">{{ $institution->sector?->name }}</p>
                    </div>
                    @if ($academicYear)
                        <div>
                            <p class="info-lbl">Academic Year</p>
                            <p class="info-val">{{ $academicYear->name }}</p>
                        </div>
                    @endif
                    @if ($academicYear && !empty($academicYear->admission_end))
                        <div>
                            <p class="info-lbl">Admissions Close</p>
                            <p class="info-val font-semibold" style="color:var(--green-text);">
                                {{ \Carbon\Carbon::parse($academicYear->admission_end)->format('d M Y') }}
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Facilities --}}
                @php
                    $facs = [
                        'has_transport' => ['🚌', 'Transport', 'bdg-tag'],
                        'has_meal_program' => ['🍱', 'Meal Program', 'bdg-tag'],
                        'has_matric_tech' => ['⚙️', 'Matric Tech', 'bdg-tag'],
                        'has_evening_classes' => ['🌙', 'Evening Classes', 'bdg-tag'],
                        'is_cambridge' => ['🎓', 'Cambridge', 'bdg-purple'],
                        'has_ece' => ['👶', 'ECE Center', 'bdg-pink'],
                    ];
                    $anyFac = collect($facs)->keys()->some(fn($k) => $institution->$k ?? false);
                @endphp
                @if ($anyFac)
                    <div class="mt-5 pt-5" style="border-top:1px solid var(--border);">
                        <p class="info-lbl mb-3">Facilities</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($facs as $key => [$icon, $label, $cls])
                                @if ($institution->$key ?? false)
                                    <span class="bdg {{ $cls }}">{{ $icon }}
                                        {{ $label }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Quick stats --}}
            <div class="glass p-6 flex flex-col gap-4">
                <div class="flex items-center gap-2 mb-1">
                    <div class="icon-box" style="width:36px;height:36px;border-radius:10px;font-size:16px;">📊</div>
                    <p class="text-sm font-semibold text-white">Quick Stats</p>
                </div>

                @if ($hasEveningShift)
                    {{-- Morning block --}}
                    <div style="background:rgba(134,239,172,0.05);border:1px solid rgba(134,239,172,0.15);border-radius:10px;padding:10px 12px;">
                        <p class="text-xs font-semibold mb-2" style="color:#86efac;">🌅 Morning Shift</p>
                        @foreach ([['Capacity', number_format($mornSeats), 'var(--text)'], ['Existing', number_format($mornExist), '#fb923c'], ['Admitted', number_format($mornAdmit), '#60a5fa'], ['Available', number_format($mornAvail), $mornAvail > 0 ? '#86efac' : '#fca5a5']] as [$lbl, $v, $c])
                            <div class="flex justify-between items-center py-1.5" style="border-bottom:1px solid var(--border);">
                                <p class="text-xs" style="color:var(--muted);">{{ $lbl }}</p>
                                <p class="text-sm font-bold" style="color:{{ $c }};">{{ $v }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Evening block --}}
                    <div style="background:rgba(147,197,253,0.05);border:1px solid rgba(147,197,253,0.15);border-radius:10px;padding:10px 12px;">
                        <p class="text-xs font-semibold mb-2" style="color:#93c5fd;">🌙 Evening Shift</p>
                        @foreach ([['Capacity', number_format($evenSeats), 'var(--text)'], ['Existing', number_format($evenExist), '#fb923c'], ['Admitted', number_format($evenAdmit), '#60a5fa'], ['Available', number_format($evenAvail), $evenAvail > 0 ? '#93c5fd' : '#fca5a5']] as [$lbl, $v, $c])
                            <div class="flex justify-between items-center py-1.5" style="border-bottom:1px solid var(--border);">
                                <p class="text-xs" style="color:var(--muted);">{{ $lbl }}</p>
                                <p class="text-sm font-bold" style="color:{{ $c }};">{{ $v }}</p>
                            </div>
                        @endforeach
                    </div>

                @else
                    {{-- Morning-only: original combined layout --}}
                    @foreach ([['Total Capacity', number_format($totalSeats), 'var(--text)'], ['Existing Students', number_format($totalExist), '#fb923c'], ['Newly Admitted', number_format($totalAdmit), '#60a5fa'], ['Seats Available', number_format($totalAvail), $totalAvail > 0 ? 'var(--green-text)' : '#fca5a5']] as [$label, $val, $color])
                        <div class="flex justify-between items-center py-2.5" style="border-bottom:1px solid var(--border);">
                            <p class="text-xs" style="color:var(--muted);">{{ $label }}</p>
                            <p class="text-sm font-bold" style="color:{{ $color }};">{{ $val }}</p>
                        </div>
                    @endforeach
                @endif

                <div class="pt-1">
                    <div class="flex justify-between text-xs mb-1.5" style="color:var(--muted);">
                        <span>Capacity used</span>
                        <span>{{ $fillPct }}%</span>
                    </div>
                    <div class="prog">
                        <div class="prog-fill"
                            style="width:{{ $fillPct }}%;background:{{ $fillPct < 80 ? 'var(--green)' : ($fillPct < 95 ? '#f97316' : '#ef4444') }};">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- HOI section (only shown when hoi_name or hoi_contact is set) --}}
        @if ($institution->hoi_name || $institution->hoi_contact)
        <div class="glass p-6 au au1">
            <div class="flex items-center gap-2 mb-5">
                <div class="icon-box" style="width:36px;height:36px;border-radius:10px;font-size:16px;">👤</div>
                <p class="text-sm font-semibold text-white">Head of Institution</p>
            </div>
            <div class="grid grid-cols-2 gap-5">
                @if ($institution->hoi_name)
                <div>
                    <p class="info-lbl">Name</p>
                    <p class="info-val">{{ $institution->hoi_name }}</p>
                </div>
                @endif
                @if ($institution->hoi_contact)
                <div>
                    <p class="info-lbl">Contact</p>
                    <p class="info-val">
                        <a href="tel:{{ $institution->hoi_contact }}" style="color:var(--green-text);">
                            {{ $institution->hoi_contact }}
                        </a>
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Class table --}}
        @if ($seatData->isNotEmpty())
        <div class="glass overflow-hidden au au1">
            <div class="px-6 py-4 flex items-center gap-3" style="border-bottom:1px solid var(--border);">
                <div class="icon-box" style="width:36px;height:36px;border-radius:10px;font-size:16px;">📋</div>
                <div>
                    <p class="text-sm font-semibold text-white">Class-wise Enrollment &amp; Availability</p>
                    <p class="text-xs mt-0.5" style="color:var(--muted);">Live data for {{ $academicYear?->name }}
                    </p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="dt">
                    <thead>
                        <tr>
                            <th>Class</th>
                            @if ($hasEveningShift)<th>Shift</th>@endif
                            <th>Existing</th>
                            <th>Capacity</th>
                            <th style="color:var(--green-text);">Available</th>
                            <th style="color:#60a5fa;">Newly Admitted</th>
                            <th style="color:var(--text);">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($seatData as $ic)
                            @php
                                $shiftRow = $admissionByShift[$ic->class_id] ?? null;
                                $mAdm = (int)($shiftRow?->morning_admitted ?? 0);
                                $eAdm = (int)($shiftRow?->evening_admitted ?? 0);

                                // Morning shift data
                                $mSeats = (int)($ic->morning_seats    ?? 0);
                                $mExist = (int)($ic->morning_existing ?? 0);
                                $mAvail = max(0, $mSeats - $mExist - $mAdm);
                                $mTotal = $mExist + $mAdm;
                                $mFill  = $mSeats > 0 ? min(100, round(($mTotal / $mSeats) * 100)) : 0;

                                // Evening shift data
                                $eSeats = (int)($ic->evening_seats    ?? 0);
                                $eExist = (int)($ic->evening_existing ?? 0);
                                $eAvail = max(0, $eSeats - $eExist - $eAdm);
                                $eTotal = $eExist + $eAdm;
                                $eFill  = $eSeats > 0 ? min(100, round(($eTotal / $eSeats) * 100)) : 0;

                                // Combined (used for morning-only or classes with no shift data e.g. ECE)
                                $totalAdmitted = $admissionTotal[$ic->class_id]?->total_admitted ?? 0;
                                $available     = max(0, $ic->total_seats - $ic->existing_enrollment - $totalAdmitted);
                                $totalEnrl     = $ic->existing_enrollment + $totalAdmitted;
                                $rFill         = $ic->total_seats > 0
                                    ? min(100, round(($totalEnrl / $ic->total_seats) * 100)) : 0;

                                // This class has shift-specific data only if at least one shift has seats
                                $classHasShiftData = ($mSeats > 0 || $eSeats > 0);
                            @endphp

                            @if ($hasEveningShift && $classHasShiftData)
                                {{-- Morning row --}}
                                <tr style="border-bottom:none;">
                                    <td rowspan="2" style="vertical-align:middle;border-right:1px solid rgba(255,255,255,0.05);">
                                        {{ $ic->classModel?->name }}
                                        @if ($ic->classModel?->is_ece)
                                            <span class="bdg bdg-pink ml-1" style="font-size:10px;">ECE</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-xs font-semibold" style="color:#86efac;">🌅 Morning</span>
                                    </td>
                                    <td class="font-semibold" style="color:#fb923c;">{{ number_format($mExist) }}</td>
                                    <td style="color:var(--muted);">{{ number_format($mSeats) }}</td>
                                    <td>
                                        <span class="bdg {{ $mAvail > 0 ? 'bdg-open' : 'bdg-full' }}" style="{{ $mAvail > 0 ? '' : '' }}">
                                            {{ $mAvail > 0 ? number_format($mAvail) : 'Full' }}
                                        </span>
                                    </td>
                                    <td class="font-semibold" style="color:#60a5fa;">{{ number_format($mAdm) }}</td>
                                    <td>
                                        <p class="font-bold text-white">{{ number_format($mTotal) }}</p>
                                        <div class="prog w-14 mx-auto">
                                            <div class="prog-fill" style="width:{{ $mFill }}%;background:{{ $mFill < 80 ? 'var(--green)' : ($mFill < 95 ? '#f97316' : '#ef4444') }};"></div>
                                        </div>
                                    </td>
                                </tr>
                                {{-- Evening row --}}
                                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                                    <td>
                                        <span class="text-xs font-semibold" style="color:#93c5fd;">🌙 Evening</span>
                                    </td>
                                    <td class="font-semibold" style="color:#fb923c;">{{ number_format($eExist) }}</td>
                                    <td style="color:var(--muted);">{{ number_format($eSeats) }}</td>
                                    <td>
                                        <span class="bdg {{ $eAvail > 0 ? '' : 'bdg-full' }}" style="{{ $eAvail > 0 ? 'background:rgba(147,197,253,0.14);color:#93c5fd;border:1px solid rgba(147,197,253,0.28);' : '' }}">
                                            {{ $eAvail > 0 ? number_format($eAvail) : 'Full' }}
                                        </span>
                                    </td>
                                    <td class="font-semibold" style="color:#60a5fa;">{{ number_format($eAdm) }}</td>
                                    <td>
                                        <p class="font-bold text-white">{{ number_format($eTotal) }}</p>
                                        <div class="prog w-14 mx-auto">
                                            <div class="prog-fill" style="width:{{ $eFill }}%;background:{{ $eFill < 80 ? '#3b82f6' : ($eFill < 95 ? '#f97316' : '#ef4444') }};"></div>
                                        </div>
                                    </td>
                                </tr>

                            @else
                                {{-- Combined row: morning-only school OR class without shift data (ECE etc.) --}}
                                <tr>
                                    <td>
                                        {{ $ic->classModel?->name }}
                                        @if ($ic->classModel?->is_ece)
                                            <span class="bdg bdg-pink ml-1" style="font-size:10px;">ECE</span>
                                        @endif
                                    </td>
                                    {{-- Shift column spacer so columns align in dual-shift schools --}}
                                    @if ($hasEveningShift && !$classHasShiftData)
                                        <td><span class="text-xs" style="color:var(--muted);">—</span></td>
                                    @endif
                                    <td class="font-semibold" style="color:#fb923c;">{{ number_format($ic->existing_enrollment) }}</td>
                                    <td style="color:var(--muted);">{{ number_format($ic->total_seats) }}</td>
                                    <td>
                                        <span class="bdg {{ $available > 0 ? 'bdg-open' : 'bdg-full' }}">
                                            {{ $available > 0 ? number_format($available) : 'Full' }}
                                        </span>
                                    </td>
                                    <td class="font-semibold" style="color:#60a5fa;">{{ number_format($totalAdmitted) }}</td>
                                    <td>
                                        <p class="font-bold text-white">{{ number_format($totalEnrl) }}</p>
                                        <div class="prog w-14 mx-auto">
                                            <div class="prog-fill" style="width:{{ $rFill }}%;background:{{ $rFill < 80 ? 'var(--green)' : ($rFill < 95 ? '#f97316' : '#ef4444') }};"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="color:var(--muted);">TOTAL</td>
                            @if ($hasEveningShift)<td></td>@endif
                            <td style="color:#fb923c;">{{ number_format($totalExist) }}</td>
                            <td style="color:var(--muted);">{{ number_format($totalSeats) }}</td>
                            <td><span class="bdg {{ $totalAvail > 0 ? 'bdg-open' : 'bdg-full' }}">{{ number_format($totalAvail) }}</span></td>
                            <td style="color:#60a5fa;">{{ number_format($totalAdmit) }}</td>
                            <td class="text-white">{{ number_format($totalExist + $totalAdmit) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @else
        {{-- Model Colleges without configured seats yet --}}
        <div class="glass p-8 text-center au au1">
            <div class="text-4xl mb-3">📋</div>
            <p class="text-sm font-semibold text-white mb-1">Seat details coming soon</p>
            <p class="text-xs" style="color:var(--muted);">Admission seat configuration will be published when available.</p>
        </div>
        @endif

        {{-- Back button --}}
        <div class="text-center pt-2 pb-6">
            <a href="{{ route('portal.index') }}" class="btn-g">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                <span x-show="lang === 'en'">Back to All Schools</span>
                <span x-show="lang === 'ur'" x-cloak class="urdu">تمام اسکول دیکھیں</span>
            </a>
        </div>

    </div>

    {{-- ── Footer ── --}}
    <footer style="background:var(--bg);border-top:1px solid var(--border);" class="py-8 px-5">
        <div class="max-w-5xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                    style="background:rgba(74,160,110,0.15);border:1px solid rgba(74,160,110,0.28);">
                    @if (!empty($settings['portal_logo']) || !empty($settings['app_logo']))
                        <img src="{{ Storage::url($settings['portal_logo'] ?? $settings['app_logo']) }}"
                            alt="" style="height:28px;width:auto;object-fit:contain;filter:brightness(0.7);">
                    @else
                        <span style="font-size:15px;">🏛️</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">
                        {{ $settings['app_name'] ?? 'Federal Directorate of Education' }}</p>
                    <p class="text-xs" style="color:var(--muted);">© {{ now()->year }}
                        {{ $settings['portal_title'] ?? 'FDE Admissions Portal' }} ·
                        Islamabad Capital Territory</p>
                </div>
            </div>
            <p class="text-xs" style="color:var(--muted);">Academic Year 2026–27</p>
        </div>
    </footer>

</body>

</html>
