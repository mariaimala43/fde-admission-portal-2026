<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Available Seats — FDE Admission Portal</title>
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

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        [x-cloak] { display: none !important; }

        .page-bg {
            background:
                radial-gradient(ellipse 90% 70% at 0% 20%, rgba(60, 130, 90, 0.13) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 100% 60%, rgba(20, 60, 120, 0.12) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 50% 100%, rgba(60, 130, 90, 0.07) 0%, transparent 50%),
                var(--bg);
        }

        .navbar {
            background: rgba(13, 17, 23, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

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

        .bdg {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 3px 9px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }

        .bdg-open {
            background: rgba(74, 160, 110, 0.16);
            color: var(--green-text);
            border: 1px solid rgba(74, 160, 110, 0.28);
        }

        .bdg-tag {
            background: rgba(255, 255, 255, 0.05);
            color: var(--muted);
            border: 1px solid var(--border);
        }

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
            transition: all .25s;
            text-decoration: none;
        }

        .btn-g:hover {
            background: var(--green-d);
            box-shadow: 0 0 28px rgba(74, 160, 110, 0.45);
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

        .l-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 26px 18px;
            text-align: center;
            display: block;
            transition: all .25s;
            text-decoration: none;
        }

        .l-card:hover {
            background: rgba(74, 160, 110, 0.1);
            border-color: var(--border-g);
            transform: translateY(-4px);
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.4);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .au  { animation: fadeUp .5s ease both; }
        .au1 { animation-delay: .07s; }
        .au2 { animation-delay: .14s; }
        .au3 { animation-delay: .21s; }
        .au4 { animation-delay: .28s; }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--green-d); border-radius: 3px; }

        /* ── Area cards ── */
        .area-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px 32px;
            display: block;
            text-decoration: none;
            transition: all .3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .area-card:hover {
            background: var(--surface-h);
            border-color: var(--border-g);
            transform: translateY(-4px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.5);
        }

        .area-card.active {
            border-color: var(--green);
            box-shadow: 0 0 0 1px var(--green), 0 24px 60px rgba(74, 160, 110, 0.2);
        }

        .area-card.active:hover {
            transform: translateY(-2px);
        }

        .area-card-rural:hover {
            border-color: rgba(59, 110, 165, 0.5);
        }

        .area-card-rural.active {
            border-color: #3b6ea5;
            box-shadow: 0 0 0 1px #3b6ea5, 0 24px 60px rgba(59, 110, 165, 0.2);
        }

        .seat-count {
            font-size: 4rem;
            font-weight: 900;
            color: var(--white);
            line-height: 1;
            letter-spacing: -2px;
        }

        .seat-count-zero { color: #2a3540; }

        /* ── School rows ── */
        .school-row {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            transition: all .2s ease;
        }

        .school-row:hover {
            background: var(--surface-h);
            border-color: var(--border-g);
        }

        .avail-num {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--green-text);
            line-height: 1;
        }
    </style>
</head>

<body class="page-bg" x-data="{}">

    {{-- Portal notice --}}
    @if (!empty($settings['portal_notice']))
        <div style="background:rgba(234,179,8,0.12);border-bottom:1px solid rgba(234,179,8,0.25);"
            class="py-2 px-4 text-center text-xs font-semibold text-yellow-300">
            ⚠️ {{ $settings['portal_notice'] }}
        </div>
    @endif

    {{-- ════════════════════ NAVBAR ════════════════════ --}}
    <nav class="navbar">
        <div class="max-w-6xl mx-auto px-5 py-3.5 flex items-center justify-between gap-6">

            <a href="{{ route('portal.index') }}" class="flex items-center gap-3 no-underline shrink-0">
                <div class="w-9 h-9 rounded-full flex items-center justify-center"
                    style="background:rgba(74,160,110,0.18);border:1px solid rgba(74,160,110,0.35);">
                    <span style="font-size:18px;">🏛️</span>
                </div>
                <div class="hidden sm:block">
                    <p class="text-sm font-bold text-white leading-tight">FDE Admission Portal</p>
                    <p class="text-xs" style="color:var(--muted);">Government of Pakistan</p>
                </div>
            </a>

            <div class="hidden md:flex items-center gap-7">
                <a href="{{ route('portal.index') }}"
                    class="text-sm font-medium text-white/60 hover:text-white transition">Home</a>
                <a href="{{ route('portal.seats') }}"
                    class="text-sm font-medium text-white transition">Available Seats</a>
                <a href="#staff-section"
                    class="text-sm font-medium text-white/60 hover:text-white transition">Staff Portal</a>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('login') }}" class="text-sm px-4 py-2 rounded-full border font-medium transition"
                    style="border-color:var(--border);color:var(--text);"
                    onmouseover="this.style.borderColor='rgba(255,255,255,0.2)'"
                    onmouseout="this.style.borderColor='var(--border)'">Sign In</a>
                <a href="{{ route('login') }}" class="btn-g" style="padding:8px 20px;font-size:13px;">Get Access</a>
            </div>
        </div>
    </nav>

    {{-- ════════════════════ HERO ════════════════════ --}}
    <section class="relative overflow-hidden" style="background:linear-gradient(160deg,#0f1a20 0%,#0d1117 100%);">

        <div class="absolute pointer-events-none"
            style="width:700px;height:700px;top:-200px;left:-150px;background:radial-gradient(circle,rgba(60,130,90,0.14),transparent 65%);border-radius:50%;"></div>
        <div class="absolute pointer-events-none"
            style="width:500px;height:500px;top:0;right:-100px;background:radial-gradient(circle,rgba(20,60,140,0.1),transparent 65%);border-radius:50%;"></div>

        <div class="max-w-6xl mx-auto px-5 pt-14 pb-16 relative z-10">

            {{-- Eyebrow --}}
            <div class="flex items-center gap-2 mb-4 au">
                <span class="dot"></span>
                <span class="text-xs font-semibold tracking-widest uppercase" style="color:var(--green-text);">
                    Live Seat Availability
                </span>
            </div>

            <h1 class="font-extrabold text-white mb-2 au au1" style="font-size:clamp(2rem,4.5vw,3rem);line-height:1.1;">
                Available Seats
            </h1>
            <p class="text-sm mb-10 au au2" style="color:var(--muted);">
                @if ($academicYear)
                    Academic Year {{ $academicYear->name }} ·
                @endif
                Select a sector to see schools with open seats.
            </p>

            {{-- ── Three area cards ── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 au au2">

                {{-- Urban card --}}
                <a href="{{ route('portal.seats', ['area' => 'urban']) }}"
                   class="area-card {{ $area === 'urban' ? 'active' : '' }}">

                    @if ($area === 'urban')
                        <span class="bdg bdg-open absolute top-5 right-5">Viewing ✓</span>
                    @endif

                    <div style="height:3px;background:linear-gradient(90deg,var(--green-d),var(--green));border-radius:2px;margin-bottom:24px;"></div>

                    <div class="flex items-center gap-3 mb-4">
                        <div class="icon-box">🏙️</div>
                        <div>
                            <p class="text-white font-bold text-xl leading-tight">Urban Sector</p>
                            <p class="text-xs" style="color:var(--muted);">Urban-I &amp; Urban-II</p>
                        </div>
                    </div>

                    <p class="seat-count {{ $urbanTotal === 0 ? 'seat-count-zero' : '' }}">
                        {{ number_format($urbanTotal) }}
                    </p>
                    <p class="text-xs mt-1 mb-4 font-semibold" style="color:var(--green-text);">total seats available</p>

                    {{-- Morning / Evening split --}}
                    <div class="grid grid-cols-2 gap-2 mb-5">
                        <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;padding:10px 12px;">
                            <p class="text-xs mb-1" style="color:var(--muted);">🌅 Morning</p>
                            <p class="text-sm font-bold" style="color:{{ $urbanBreakdown->morning_available > 0 ? 'var(--green-text)' : '#3a4a55' }};">
                                {{ number_format($urbanBreakdown->morning_available) }} <span class="text-xs font-normal" style="color:var(--muted);">avail</span>
                            </p>
                            <p class="text-xs" style="color:var(--muted);">{{ number_format($urbanBreakdown->morning_total) }} total · {{ number_format($urbanBreakdown->morning_existing) }} exist</p>
                        </div>
                        <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;padding:10px 12px;">
                            <p class="text-xs mb-1" style="color:var(--muted);">🌙 Evening</p>
                            <p class="text-sm font-bold" style="color:{{ $urbanBreakdown->evening_available > 0 ? 'var(--green-text)' : '#3a4a55' }};">
                                {{ number_format($urbanBreakdown->evening_available) }} <span class="text-xs font-normal" style="color:var(--muted);">avail</span>
                            </p>
                            <p class="text-xs" style="color:var(--muted);">{{ number_format($urbanBreakdown->evening_total) }} total · {{ number_format($urbanBreakdown->evening_existing) }} exist</p>
                        </div>
                    </div>

                    {{-- Per-sector breakdown --}}
                    <div style="border-top:1px solid var(--border);padding-top:14px;" class="flex flex-col gap-2.5">
                        @foreach ($urbanSectors as $s)
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium" style="color:var(--muted);">{{ $s->name }}</span>
                                <span class="text-sm font-bold"
                                      style="color:{{ $s->available > 0 ? 'var(--green-text)' : '#3a4a55' }};">
                                    {{ number_format($s->available) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </a>

                {{-- Rural card --}}
                <a href="{{ route('portal.seats', ['area' => 'rural']) }}"
                   class="area-card area-card-rural {{ $area === 'rural' ? 'active' : '' }}">

                    @if ($area === 'rural')
                        <span class="bdg absolute top-5 right-5"
                              style="background:rgba(59,110,165,0.16);color:#93c5fd;border:1px solid rgba(59,110,165,0.3);">Viewing ✓</span>
                    @endif

                    <div style="height:3px;background:linear-gradient(90deg,#2a5080,#3b6ea5);border-radius:2px;margin-bottom:24px;"></div>

                    <div class="flex items-center gap-3 mb-4">
                        <div class="icon-box" style="background:rgba(59,110,165,0.2);border-color:rgba(59,110,165,0.35);">🌿</div>
                        <div>
                            <p class="text-white font-bold text-xl leading-tight">Rural Sector</p>
                            <p class="text-xs" style="color:var(--muted);">Nilore, Tarnol, Sihala &amp; Bhara Kahu</p>
                        </div>
                    </div>

                    <p class="seat-count {{ $ruralTotal === 0 ? 'seat-count-zero' : '' }}"
                       style="{{ $ruralTotal > 0 ? 'color:#93c5fd;' : '' }}">
                        {{ number_format($ruralTotal) }}
                    </p>
                    <p class="text-xs mt-1 mb-4 font-semibold" style="color:#74a8e0;">total seats available</p>

                    {{-- Morning / Evening split --}}
                    <div class="grid grid-cols-2 gap-2 mb-5">
                        <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;padding:10px 12px;">
                            <p class="text-xs mb-1" style="color:var(--muted);">🌅 Morning</p>
                            <p class="text-sm font-bold" style="color:{{ $ruralBreakdown->morning_available > 0 ? '#93c5fd' : '#3a4a55' }};">
                                {{ number_format($ruralBreakdown->morning_available) }} <span class="text-xs font-normal" style="color:var(--muted);">avail</span>
                            </p>
                            <p class="text-xs" style="color:var(--muted);">{{ number_format($ruralBreakdown->morning_total) }} total · {{ number_format($ruralBreakdown->morning_existing) }} exist</p>
                        </div>
                        <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;padding:10px 12px;">
                            <p class="text-xs mb-1" style="color:var(--muted);">🌙 Evening</p>
                            <p class="text-sm font-bold" style="color:{{ $ruralBreakdown->evening_available > 0 ? '#93c5fd' : '#3a4a55' }};">
                                {{ number_format($ruralBreakdown->evening_available) }} <span class="text-xs font-normal" style="color:var(--muted);">avail</span>
                            </p>
                            <p class="text-xs" style="color:var(--muted);">{{ number_format($ruralBreakdown->evening_total) }} total · {{ number_format($ruralBreakdown->evening_existing) }} exist</p>
                        </div>
                    </div>

                    {{-- Per-sector breakdown --}}
                    <div style="border-top:1px solid var(--border);padding-top:14px;" class="flex flex-col gap-2.5">
                        @foreach ($ruralSectors as $s)
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium" style="color:var(--muted);">{{ $s->name }}</span>
                                <span class="text-sm font-bold"
                                      style="color:{{ $s->available > 0 ? '#93c5fd' : '#3a4a55' }};">
                                    {{ number_format($s->available) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </a>

                {{-- Model Colleges card --}}
                <a href="{{ route('portal.seats', ['area' => 'model']) }}"
                   class="area-card {{ $area === 'model' ? 'active' : '' }}"
                   style="{{ $area === 'model' ? 'border-color:#7c3aed;box-shadow:0 0 0 1px #7c3aed,0 24px 60px rgba(124,58,237,0.2);' : '' }}">

                    @if ($area === 'model')
                        <span class="bdg absolute top-5 right-5"
                              style="background:rgba(124,58,237,0.16);color:#c4b5fd;border:1px solid rgba(124,58,237,0.3);">Viewing ✓</span>
                    @endif

                    <div style="height:3px;background:linear-gradient(90deg,#5b21b6,#7c3aed);border-radius:2px;margin-bottom:24px;"></div>

                    <div class="flex items-center gap-3 mb-4">
                        <div class="icon-box" style="background:rgba(124,58,237,0.2);border-color:rgba(124,58,237,0.35);">🎓</div>
                        <div>
                            <p class="text-white font-bold text-xl leading-tight">Model Colleges</p>
                            <p class="text-xs" style="color:var(--muted);">Federal Model Colleges</p>
                        </div>
                    </div>

                    <p class="seat-count {{ $modelTotal === 0 ? 'seat-count-zero' : '' }}"
                       style="{{ $modelTotal > 0 ? 'color:#c4b5fd;' : '' }}">
                        {{ number_format($modelTotal) }}
                    </p>
                    <p class="text-xs mt-1 mb-4 font-semibold" style="color:#a78bfa;">total seats available</p>

                    {{-- Morning / Evening split --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;padding:10px 12px;">
                            <p class="text-xs mb-1" style="color:var(--muted);">🌅 Morning</p>
                            <p class="text-sm font-bold" style="color:{{ $modelBreakdown->morning_available > 0 ? '#c4b5fd' : '#3a4a55' }};">
                                {{ number_format($modelBreakdown->morning_available) }} <span class="text-xs font-normal" style="color:var(--muted);">avail</span>
                            </p>
                            <p class="text-xs" style="color:var(--muted);">{{ number_format($modelBreakdown->morning_total) }} total · {{ number_format($modelBreakdown->morning_existing) }} exist</p>
                        </div>
                        <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;padding:10px 12px;">
                            <p class="text-xs mb-1" style="color:var(--muted);">🌙 Evening</p>
                            <p class="text-sm font-bold" style="color:{{ $modelBreakdown->evening_available > 0 ? '#c4b5fd' : '#3a4a55' }};">
                                {{ number_format($modelBreakdown->evening_available) }} <span class="text-xs font-normal" style="color:var(--muted);">avail</span>
                            </p>
                            <p class="text-xs" style="color:var(--muted);">{{ number_format($modelBreakdown->evening_total) }} total · {{ number_format($modelBreakdown->evening_existing) }} exist</p>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </section>

    {{-- ════════════════════ SCHOOL LIST ════════════════════ --}}
    @if ($area && $schools->isNotEmpty())
        <section class="max-w-6xl mx-auto px-5 py-12">

            {{-- Heading --}}
            <div class="flex items-end justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-white">
                        {{ $schools->count() }} {{ $schools->count() === 1 ? 'School' : 'Schools' }} with Available Seats
                        — {{ $area === 'urban' ? 'Urban Sector' : ($area === 'rural' ? 'Rural Sector' : 'Model Colleges') }}
                    </h2>
                    <p class="text-xs mt-1" style="color:var(--muted);">
                        {{ $academicYear?->name }} · Only schools with at least 1 available seat shown
                    </p>
                </div>
                <div class="hidden sm:flex items-center gap-2">
                    <span class="dot"></span>
                    <span class="text-xs font-medium" style="color:var(--green-text);">Live data</span>
                </div>
            </div>

            {{-- Column headers --}}
            <div class="hidden md:grid gap-3 px-4 py-2 mb-1 text-xs font-semibold uppercase tracking-wider"
                 style="grid-template-columns:1fr 130px repeat(3,80px) 6px repeat(3,80px);color:var(--muted);">
                <div>School Name</div>
                <div>Sector</div>
                <div class="text-right" style="color:#86efac;">🌅 Total</div>
                <div class="text-right" style="color:#86efac;">Existing</div>
                <div class="text-right" style="color:#86efac;">Avail</div>
                <div></div>
                <div class="text-right" style="color:#93c5fd;">🌙 Total</div>
                <div class="text-right" style="color:#93c5fd;">Existing</div>
                <div class="text-right" style="color:#93c5fd;">Avail</div>
            </div>

            {{-- School rows --}}
            <div class="flex flex-col gap-2">
                @foreach ($schools as $school)

                    {{-- Desktop row --}}
                    <div class="school-row px-4 py-3 hidden md:grid gap-3 items-center"
                         style="grid-template-columns:1fr 130px repeat(3,80px) 6px repeat(3,80px);">

                        {{-- Name --}}
                        <div class="min-w-0">
                            <a href="{{ route('portal.show', $school) }}"
                               class="font-semibold text-sm text-white leading-snug transition hover:text-green-300 block truncate">
                                {{ $school->name }}
                            </a>
                            @if ($school->address)
                                <p class="text-xs mt-0.5 truncate" style="color:var(--muted);">
                                    {{ Str::limit($school->address, 50) }}
                                </p>
                            @endif
                        </div>

                        {{-- Sector --}}
                        <div><span class="bdg bdg-tag">{{ $school->sector?->name }}</span></div>

                        {{-- 🌅 Morning --}}
                        <div class="text-right"><span class="text-sm text-white">{{ number_format($school->morning_total) }}</span></div>
                        <div class="text-right"><span class="text-sm" style="color:var(--muted);">{{ number_format($school->morning_existing) }}</span></div>
                        <div class="text-right">
                            <span class="font-bold text-sm" style="color:{{ $school->morning_available > 0 ? '#86efac' : '#3a4a55' }};">
                                {{ number_format($school->morning_available) }}
                            </span>
                        </div>

                        {{-- Divider --}}
                        <div style="width:1px;height:32px;background:var(--border);margin:auto;"></div>

                        {{-- 🌙 Evening --}}
                        <div class="text-right"><span class="text-sm text-white">{{ number_format($school->evening_total) }}</span></div>
                        <div class="text-right"><span class="text-sm" style="color:var(--muted);">{{ number_format($school->evening_existing) }}</span></div>
                        <div class="text-right">
                            <span class="font-bold text-sm" style="color:{{ $school->evening_available > 0 ? '#93c5fd' : '#3a4a55' }};">
                                {{ number_format($school->evening_available) }}
                            </span>
                        </div>
                    </div>

                    {{-- Mobile card --}}
                    <div class="school-row px-4 py-4 md:hidden">
                        <a href="{{ route('portal.show', $school) }}"
                           class="font-semibold text-sm text-white leading-snug transition hover:text-green-300 block mb-1">
                            {{ $school->name }}
                        </a>
                        <span class="bdg bdg-tag mb-3 inline-block">{{ $school->sector?->name }}</span>
                        <div class="grid grid-cols-2 gap-2">
                            <div style="background:rgba(134,239,172,0.06);border:1px solid rgba(134,239,172,0.15);border-radius:8px;padding:8px 10px;">
                                <p class="text-xs font-semibold mb-1" style="color:#86efac;">🌅 Morning</p>
                                <p class="text-xs" style="color:var(--muted);">Total: <span class="text-white font-medium">{{ number_format($school->morning_total) }}</span></p>
                                <p class="text-xs" style="color:var(--muted);">Existing: <span class="text-white font-medium">{{ number_format($school->morning_existing) }}</span></p>
                                <p class="text-xs font-bold mt-1" style="color:{{ $school->morning_available > 0 ? '#86efac' : '#3a4a55' }};">
                                    Available: {{ number_format($school->morning_available) }}
                                </p>
                            </div>
                            <div style="background:rgba(147,197,253,0.06);border:1px solid rgba(147,197,253,0.15);border-radius:8px;padding:8px 10px;">
                                <p class="text-xs font-semibold mb-1" style="color:#93c5fd;">🌙 Evening</p>
                                <p class="text-xs" style="color:var(--muted);">Total: <span class="text-white font-medium">{{ number_format($school->evening_total) }}</span></p>
                                <p class="text-xs" style="color:var(--muted);">Existing: <span class="text-white font-medium">{{ number_format($school->evening_existing) }}</span></p>
                                <p class="text-xs font-bold mt-1" style="color:{{ $school->evening_available > 0 ? '#93c5fd' : '#3a4a55' }};">
                                    Available: {{ number_format($school->evening_available) }}
                                </p>
                            </div>
                        </div>
                    </div>

                @endforeach
            </div>

            {{-- Back link --}}
            <div class="mt-10 text-center">
                <a href="{{ route('portal.seats') }}"
                   class="text-sm font-medium transition"
                   style="color:var(--muted);"
                   onmouseover="this.style.color='white'"
                   onmouseout="this.style.color='var(--muted)'">
                    ← View Both Sectors
                </a>
            </div>

        </section>

    @elseif ($area && $schools->isEmpty())

        {{-- Empty state --}}
        <section class="max-w-6xl mx-auto px-5 py-12">
            <div class="text-center py-20 rounded-2xl"
                 style="background:var(--surface);border:1px solid var(--border);">
                <div class="text-5xl mb-4">🏫</div>
                <h3 class="text-xl font-bold text-white mb-2">No Available Seats</h3>
                <p class="text-sm mb-6" style="color:var(--muted);">
                    All schools in the {{ $area === 'urban' ? 'Urban' : ($area === 'rural' ? 'Rural' : 'Model Colleges') }} sector are currently full.
                </p>
                <a href="{{ route('portal.seats') }}" class="btn-g inline-flex">← View Both Sectors</a>
            </div>
        </section>

    @endif

    {{-- ════════════════════ STAFF LOGIN ════════════════════ --}}
    <section id="staff-section" style="background:var(--bg2);border-top:1px solid var(--border);" class="py-20 px-5">
        <div class="max-w-4xl mx-auto text-center">
            <div class="flex items-center justify-center gap-2 mb-3">
                <span class="dot"></span>
                <span class="text-xs font-semibold tracking-widest uppercase" style="color:var(--green-text);">Portal
                    Access</span>
            </div>
            <h3 class="text-3xl font-bold text-white mb-2">Staff Login</h3>
            <p class="text-sm mb-10" style="color:var(--muted);">Select your role to access the administration portal</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([['HoI', 'Head of Institution', '🏫'], ['AEO', 'Area Education Officer', '👤'], ['FDE Cell', 'FDE Admission Cell', '🏢'], ['Admin', 'System Administrator', '⚙️']] as [$title, $role, $icon])
                    <a href="{{ route('login') }}" class="l-card">
                        <div class="icon-box mx-auto mb-3">{{ $icon }}</div>
                        <p class="font-bold text-white text-sm mb-1">{{ $title }}</p>
                        <p class="text-xs" style="color:var(--muted);">{{ $role }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ════════════════════ FOOTER ════════════════════ --}}
    <footer style="background:var(--bg);border-top:1px solid var(--border);" class="py-8 px-5">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                    style="background:rgba(74,160,110,0.15);border:1px solid rgba(74,160,110,0.28);">
                    <span style="font-size:15px;">🏛️</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">Federal Directorate of Education</p>
                    <p class="text-xs" style="color:var(--muted);">© {{ now()->year }} FDE Admissions Portal ·
                        Islamabad Capital Territory</p>
                </div>
            </div>
            <p class="text-xs" style="color:var(--muted);">Academic Year 2026–27 · All rights reserved</p>
        </div>
    </footer>

</body>
</html>
