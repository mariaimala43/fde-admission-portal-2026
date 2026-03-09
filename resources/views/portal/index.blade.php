<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FDE Admission Portal 2026–27</title>
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
            overflow-x: hidden;
        }

        .urdu {
            font-family: 'Noto Nastaliq Urdu', serif;
            direction: rtl;
            line-height: 2.5;
        }

        [x-cloak] {
            display: none !important;
        }

        /* ── Dark mesh radial background ── */
        .page-bg {
            background:
                radial-gradient(ellipse 90% 70% at 0% 20%, rgba(60, 130, 90, 0.13) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 100% 60%, rgba(20, 60, 120, 0.12) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 50% 100%, rgba(60, 130, 90, 0.07) 0%, transparent 50%),
                var(--bg);
        }

        /* ── Navbar ── */
        .navbar {
            background: rgba(13, 17, 23, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
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
            transform: translateY(-3px);
            box-shadow: 0 16px 44px rgba(0, 0, 0, 0.4);
        }

        /* ── Icon box (green rounded square) ── */
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

        /* ── Search panel ── */
        .search-panel {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 18px;
            backdrop-filter: blur(16px);
        }

        .s-input {
            background: transparent;
            border: none;
            outline: none;
            font-family: inherit;
            font-size: 15px;
            color: var(--text);
            width: 100%;
        }

        .s-input::placeholder {
            color: #3a4a55;
        }

        .f-select {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 9px 32px 9px 12px;
            font-size: 13px;
            font-family: inherit;
            color: var(--text);
            width: 100%;
            appearance: none;
            outline: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%234aa06e' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            transition: border-color .2s;
        }

        .f-select:focus {
            border-color: var(--green);
        }

        .f-select option {
            background: #131c2e;
        }

        /* ── Facility pill ── */
        .f-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid var(--border);
            color: var(--muted);
            background: var(--surface);
            cursor: pointer;
            transition: all .2s;
            user-select: none;
        }

        .f-pill:hover {
            border-color: var(--border-g);
            color: var(--green-text);
            background: var(--green-soft);
        }

        .f-pill:has(input:checked) {
            border-color: var(--green);
            color: var(--green-text);
            background: var(--green-soft);
        }

        /* ── Badges ── */
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

        /* ── School card ── */
        .school-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all .25s ease;
        }

        .school-card:hover {
            background: var(--surface-h);
            border-color: var(--border-g);
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
        }

        .card-bar {
            height: 3px;
            background: linear-gradient(90deg, var(--green-d), var(--green));
        }

        .card-bar-dim {
            height: 3px;
            background: rgba(255, 255, 255, 0.06);
        }

        /* ── Class chip ── */
        .c-chip {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 6px 4px;
            text-align: center;
        }

        .c-chip-open {
            background: rgba(74, 160, 110, 0.1);
            border-color: rgba(74, 160, 110, 0.25);
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
            transition: all .25s;
        }

        .btn-g:hover {
            background: var(--green-d);
            box-shadow: 0 0 28px rgba(74, 160, 110, 0.45);
            transform: translateY(-1px);
        }

        /* ── Glow dot ── */
        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 10px var(--green);
            display: inline-block;
        }

        /* ── Stats ── */
        .s-num {
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1;
            color: var(--white);
        }

        .s-label {
            font-size: 12px;
            color: var(--muted);
            margin-top: 5px;
            font-weight: 500;
        }

        .v-sep {
            width: 1px;
            background: var(--border);
            align-self: stretch;
        }

        /* ── Login card ── */
        .l-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 26px 18px;
            text-align: center;
            display: block;
            transition: all .25s;
        }

        .l-card:hover {
            background: rgba(74, 160, 110, 0.1);
            border-color: var(--border-g);
            transform: translateY(-4px);
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.4);
        }

        /* ── Animations ── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
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
            animation-delay: .07s;
        }

        .au2 {
            animation-delay: .14s;
        }

        .au3 {
            animation-delay: .21s;
        }

        .au4 {
            animation-delay: .28s;
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

<body class="page-bg" x-data="{ lang: 'en' }" :dir="lang === 'ur' ? 'rtl' : 'ltr'">

    {{-- Portal notice --}}
    @if (!empty($settings['portal_notice']))
        <div style="background:rgba(234,179,8,0.12);border-bottom:1px solid rgba(234,179,8,0.25);"
            class="py-2 px-4 text-center text-xs font-semibold text-yellow-300">
            ⚠️ {{ $settings['portal_notice'] }}
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════
     NAVBAR
════════════════════════════════════════════════════ --}}
    <nav class="navbar">
        <div class="max-w-7xl mx-auto px-5 py-3.5 flex items-center justify-between gap-6">

            {{-- Brand --}}
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

            {{-- Nav links --}}
            <div class="hidden md:flex items-center gap-7">
                <a href="{{ route('portal.index') }}"
                    class="text-sm font-medium text-white/90 hover:text-white transition">Home</a>
                <a href="#schools-section" class="text-sm font-medium text-white/60 hover:text-white transition">Find
                    Schools</a>
                <a href="#staff-section" class="text-sm font-medium text-white/60 hover:text-white transition">Staff
                    Portal</a>
            </div>

            {{-- Actions --}}
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

    {{-- ════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden" style="background: linear-gradient(160deg, #0f1a20 0%, #0d1117 100%);">

        {{-- Ambient blobs --}}
        <div class="absolute pointer-events-none"
            style="width:700px;height:700px;top:-200px;left:-150px;background:radial-gradient(circle,rgba(60,130,90,0.14),transparent 65%);border-radius:50%;">
        </div>
        <div class="absolute pointer-events-none"
            style="width:500px;height:500px;top:0;right:-100px;background:radial-gradient(circle,rgba(20,60,140,0.1),transparent 65%);border-radius:50%;">
        </div>
        <div class="absolute pointer-events-none"
            style="width:400px;height:400px;bottom:-100px;right:20%;background:radial-gradient(circle,rgba(60,130,90,0.08),transparent 65%);border-radius:50%;">
        </div>

        <div class="max-w-7xl mx-auto px-5 pt-16 pb-20 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                {{-- ── LEFT: Headline ── --}}
                <div>
                    <div class="flex items-center gap-2 mb-5 au">
                        <span class="dot"></span>
                        <span class="text-xs font-semibold tracking-widest uppercase" style="color:var(--green-text);">
                            Admissions Open — Academic Year 2026–27
                        </span>
                    </div>

                    <h1 class="font-extrabold leading-[1.08] mb-6 au au1"
                        style="font-size:clamp(2.6rem,5.5vw,4rem);color:var(--white);">
                        <span x-show="lang === 'en'">
                            Your Gateway to<br>
                            <span style="color:var(--green-text);">Quality Education</span>
                        </span>
                        <span x-show="lang === 'ur'" x-cloak class="urdu"
                            style="font-size:clamp(2rem,4vw,3rem);line-height:1.9;">
                            معیاری تعلیم کا راستہ
                        </span>
                    </h1>

                    <p class="text-base leading-relaxed mb-8 max-w-lg au au2" style="color:var(--muted);"
                        x-show="lang === 'en'">
                        Discover <strong class="text-white">{{ $openInstitutions }}</strong> government schools with
                        open admissions across Islamabad Capital Territory. Free quality education
                        from ECE to Class XII — no tuition fee.
                        @if ($academicYear && !empty($academicYear->admission_end))
                            <br><span class="text-white/70 text-sm">Applications close
                                <strong
                                    class="text-white">{{ \Carbon\Carbon::parse($academicYear->admission_end)->format('d M Y') }}</strong>.</span>
                        @endif
                    </p>
                    <p class="urdu text-base leading-loose mb-8 max-w-lg au au2" style="color:var(--muted);"
                        x-show="lang === 'ur'" x-cloak>
                        اسلام آباد میں وفاقی حکومت کے اسکولوں میں مفت معیاری تعلیم۔ ای سی ای سے بارہویں تک داخلہ لیں۔
                    </p>

                    {{-- CTA row --}}
                    <div class="flex flex-wrap items-center gap-3 mb-12 au au3">
                        <button onclick="document.getElementById('schools-section').scrollIntoView({behavior:'smooth'})"
                            class="btn-g">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.35-4.35" />
                            </svg>
                            <span x-show="lang === 'en'">Find Schools</span>
                            <span x-show="lang === 'ur'" x-cloak class="urdu">اسکول تلاش کریں</span>
                        </button>
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-sm font-semibold border transition"
                            style="border-color:var(--border);color:var(--text);"
                            onmouseover="this.style.background='rgba(255,255,255,0.05)'"
                            onmouseout="this.style.background='transparent'">
                            Staff Portal <span style="opacity:.5">→</span>
                        </a>
                    </div>

                    {{-- Stats --}}
                    <div class="flex items-stretch gap-0 flex-wrap au au4"
                        style="border-top:1px solid var(--border);padding-top:28px;">
                        @foreach ([[$totalInstitutions, 'Total Schools', 'کل اسکول'], [$openInstitutions, 'Admissions Open', 'کھلے داخلے'], [$totalSeatsAvailable, 'Seats Available', 'نشستیں دستیاب'], [$totalAdmittedThisYear, 'Admitted This Year', 'اس سال داخلے']] as $i => [$val, $en, $ur])
                            @if ($i > 0)
                                <div class="v-sep mx-5"></div>
                            @endif
                            <div>
                                <p class="s-num">{{ number_format($val) }}<span
                                        style="color:var(--green);font-size:1.6rem;font-weight:800;">+</span></p>
                                <p class="s-label" x-show="lang === 'en'">{{ $en }}</p>
                                <p class="s-label urdu" x-show="lang === 'ur'" x-cloak>{{ $ur }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ── RIGHT: Glass feature cards ── --}}
                <div class="grid grid-cols-2 gap-4 au au2">

                    {{-- Banner image if set --}}
                    @if (!empty($settings['banner_enabled']) && !empty($settings['banner_image']))
                        <div class="col-span-2">
                            <img src="{{ asset('storage/' . $settings['banner_image']) }}"
                                class="w-full object-cover rounded-2xl" style="max-height:220px;" alt="Banner" />
                        </div>
                    @else
                        {{-- Feature cards --}}
                        @php $cards = [['🏫', 'Government Schools', 'All schools are official FDE institutions'], ['🎓', 'Free Education', 'No tuition fee from ECE to Class XII'], ['📋', 'Open Admissions', 'Check live seat availability instantly'], ['📞', 'Easy to Apply', 'Visit any school directly to enroll']]; @endphp
                        @foreach ($cards as $c)
                            <div class="glass p-5">
                                <div class="icon-box mb-3">{{ $c[0] }}</div>
                                <p class="text-sm font-semibold text-white mb-1">{{ $c[1] }}</p>
                                <p class="text-xs leading-relaxed" style="color:var(--muted);">{{ $c[2] }}
                                </p>
                            </div>
                        @endforeach

                        {{-- Banner text if set --}}
                        @if (!empty($settings['banner_enabled']) && !empty($settings['banner_text']))
                            <div class="glass col-span-2 p-5" style="border-color:rgba(74,160,110,0.3);">
                                <div class="flex items-start gap-3">
                                    <div class="icon-box shrink-0">📢</div>
                                    <p class="text-sm text-white leading-relaxed">{{ $settings['banner_text'] }}</p>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Help card --}}
                    <div class="glass col-span-2 p-5">
                        <div class="flex items-center gap-4">
                            <div class="icon-box shrink-0">❓</div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-white">Need Help?</p>
                                <p class="text-xs mt-0.5" style="color:var(--muted);">Contact FDE for admission
                                    queries and guidance</p>
                            </div>
                            <a href="{{ route('login') }}"
                                class="text-xs font-semibold whitespace-nowrap transition shrink-0"
                                style="color:var(--green-text);" onmouseover="this.style.color='white'"
                                onmouseout="this.style.color='var(--green-text)'">
                                Staff Login →
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════
     SEARCH + SCHOOLS
════════════════════════════════════════════════════ --}}
    <section id="schools-section" class="max-w-7xl mx-auto px-5 py-12">

        {{-- Search panel --}}
        <div class="search-panel mb-8">
            <form id="filter-form" method="GET" action="{{ route('portal.index') }}">

                {{-- Search row --}}
                <div class="flex items-stretch" style="border-bottom:1px solid var(--border);">
                    <div class="flex-1 flex items-center gap-3 px-5 py-4">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3a4a55"
                            stroke-width="2" stroke-linecap="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search school name or area..." class="s-input" />
                    </div>
                    <button type="submit" class="btn-g m-2.5"
                        style="border-radius:12px;padding:9px 22px;font-size:13px;">
                        <span x-show="lang === 'en'">Search</span>
                        <span x-show="lang === 'ur'" x-cloak class="urdu">تلاش</span>
                    </button>
                </div>

                {{-- Filters --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 px-5 py-4"
                    style="border-bottom:1px solid var(--border);">
                    <select name="sector_id" class="f-select">
                        <option value="">All Sectors</option>
                        @foreach ($sectors as $s)
                            <option value="{{ $s->id }}"
                                {{ request('sector_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <select name="class_id" class="f-select">
                        <option value="">Any Class</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}"
                                {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <select name="gender" class="f-select">
                        <option value="">Boys &amp; Girls</option>
                        <option value="boys" {{ request('gender') == 'boys' ? 'selected' : '' }}>Boys
                            Schools</option>
                        <option value="girls" {{ request('gender') == 'girls' ? 'selected' : '' }}>Girls
                            Schools</option>
                        <option value="co_education" {{ request('gender') == 'co_education' ? 'selected' : '' }}>
                            Co-Education</option>
                    </select>
                    <select name="type" class="f-select">
                        <option value="">All Types</option>
                        @foreach (['I-V', 'I-VIII', 'I-X', 'I-XII', 'VI-VIII', 'VI-X', 'VI-XII', 'Model_College'] as $t)
                            <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>
                                {{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Facility pills --}}
                <div class="px-5 py-3.5 flex flex-wrap gap-2 items-center">
                    @foreach ([
        'has_transport' => ['🚌', 'Transport'],
        'has_meal_program' => ['🍱', 'Meal Program'],
        'has_matric_tech' => ['⚙️', 'Matric Tech'],
        'has_evening_classes' => ['🌙', 'Evening'],
        'is_cambridge' => ['🎓', 'Cambridge'],
        'has_ece' => ['👶', 'ECE'],
    ] as $key => [$icon, $label])
                        <label class="f-pill">
                            <input type="checkbox" name="{{ $key }}" value="1"
                                {{ request($key) ? 'checked' : '' }} class="sr-only"
                                onchange="this.closest('form').submit()" />
                            {{ $icon }} {{ $label }}
                        </label>
                    @endforeach
                    @if (request()->hasAny([
                            'search',
                            'sector_id',
                            'class_id',
                            'gender',
                            'type',
                            'has_transport',
                            'has_meal_program',
                            'has_matric_tech',
                            'has_evening_classes',
                            'is_cambridge',
                            'has_ece',
                        ]))
                        <a href="{{ route('portal.index') }}" class="text-xs font-medium ml-1 transition"
                            style="color:#f87171;" onmouseover="this.style.color='#fca5a5'"
                            onmouseout="this.style.color='#f87171'">
                            ✕ Clear filters
                        </a>
                    @endif
                </div>

            </form>
        </div>

        {{-- Results heading --}}
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-white">
                    <span x-show="lang === 'en'">{{ $institutions->count() }} Schools with Open Admissions</span>
                    <span x-show="lang === 'ur'" x-cloak class="urdu text-lg">{{ $institutions->count() }} اسکول
                        دستیاب</span>
                </h2>
                <p class="text-xs mt-1" style="color:var(--muted);">
                    {{ $academicYear?->name }} · Showing all schools with available seats
                </p>
            </div>
            <div class="flex items-center gap-2 hidden sm:flex">
                <span class="dot"></span>
                <span class="text-xs font-medium" style="color:var(--green-text);">Live data</span>
            </div>
        </div>

        {{-- School grid --}}
        @if ($institutions->isEmpty())
            <div class="text-center py-24 rounded-2xl"
                style="background:var(--surface);border:1px solid var(--border);">
                <div class="text-5xl mb-4">🏫</div>
                <h3 class="text-xl font-bold text-white mb-2">No schools found</h3>
                <p class="text-sm" style="color:var(--muted);">Try adjusting your filters above.</p>
                <a href="{{ route('portal.index') }}" class="btn-g inline-flex mt-6">Show All Schools</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($institutions as $inst)
                    @php
                        $instSeats = $seatData[$inst->id] ?? collect();
                        $ttlSeats = $instSeats->sum('total_seats');
                        $ttlExist = $instSeats->sum('existing_enrollment');
                        $ttlAdmit = $admissionTotals[$inst->id]?->total_admitted ?? 0;
                        $ttlAvail = max(0, $ttlSeats - $ttlExist - $ttlAdmit);
                    @endphp

                    <div class="school-card">
                        <div class="{{ $ttlAvail > 0 ? 'card-bar' : 'card-bar-dim' }}"></div>

                        <div class="p-5 flex-1 flex flex-col">
                            {{-- Name + availability badge --}}
                            <div class="flex justify-between items-start gap-3 mb-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-sm text-white leading-snug">{{ $inst->name }}</h3>
                                    <p class="text-xs mt-0.5" style="color:var(--muted);">
                                        {{ $inst->sector?->name }} Sector
                                        @if ($inst->address)
                                            · {{ Str::limit($inst->address, 30) }}
                                        @endif
                                    </p>
                                </div>
                                <span class="bdg {{ $ttlAvail > 0 ? 'bdg-open' : 'bdg-full' }} shrink-0">
                                    {{ $ttlAvail > 0 ? number_format($ttlAvail) . ' open' : 'Full' }}
                                </span>
                            </div>

                            {{-- Tags --}}
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                <span class="bdg bdg-tag">{{ $inst->type }}</span>
                                <span class="bdg bdg-tag">{{ ucfirst(str_replace('_', ' ', $inst->gender)) }}</span>
                                <span class="bdg bdg-tag">{{ ucfirst($inst->shift) }}</span>
                                @if ($inst->is_cambridge)
                                    <span class="bdg bdg-purple">🎓 Cambridge</span>
                                @endif
                                @if ($inst->has_transport)
                                    <span class="bdg bdg-tag">🚌</span>
                                @endif
                                @if ($inst->has_meal_program)
                                    <span class="bdg bdg-tag">🍱</span>
                                @endif
                                @if ($inst->has_evening_classes)
                                    <span class="bdg bdg-tag">🌙</span>
                                @endif
                                @if ($inst->has_ece)
                                    <span class="bdg bdg-pink">👶 ECE</span>
                                @endif
                            </div>

                            {{-- Class chips --}}
                            @if ($instSeats->isNotEmpty())
                                <div class="mt-auto">
                                    <p class="text-xs font-semibold mb-2 uppercase tracking-wider"
                                        style="color:var(--green-text);">Seats by Class</p>
                                    <div class="grid grid-cols-4 gap-1.5">
                                        @foreach ($instSeats->sortBy('class_id')->take(8) as $ic)
                                            @php $av = max(0, $ic->total_seats - $ic->existing_enrollment - $ttlAdmit); @endphp
                                            <div class="c-chip {{ $av > 0 ? 'c-chip-open' : '' }}">
                                                <p class="truncate" style="font-size:10px;color:var(--muted);">
                                                    {{ $ic->classModel?->name }}</p>
                                                <p class="font-bold mt-0.5"
                                                    style="font-size:13px;color:{{ $av > 0 ? 'var(--green-text)' : '#2a3540' }}">
                                                    {{ $av > 0 ? $av : '—' }}
                                                </p>
                                            </div>
                                        @endforeach
                                        @if ($instSeats->count() > 8)
                                            <div class="c-chip flex items-center justify-center">
                                                <span
                                                    style="font-size:11px;color:var(--muted);">+{{ $instSeats->count() - 8 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Card footer --}}
                        <div class="px-5 py-3 flex justify-between items-center"
                            style="border-top:1px solid var(--border);">
                            <span class="text-xs" style="color:var(--muted);">
                                @if ($inst->contact_number)
                                    📞 {{ $inst->contact_number }}
                                @endif
                            </span>
                            <a href="{{ route('portal.show', $inst) }}"
                                class="text-xs font-semibold flex items-center gap-1.5 transition"
                                style="color:var(--green-text);" onmouseover="this.style.color='white'"
                                onmouseout="this.style.color='var(--green-text)'">
                                View Details
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </section>

    {{-- ════════════════════════════════════════════════════
     STAFF LOGIN
════════════════════════════════════════════════════ --}}
    <section id="staff-section" style="background:var(--bg2);border-top:1px solid var(--border);" class="py-20 px-5">
        <div class="max-w-4xl mx-auto text-center">
            <div class="flex items-center justify-center gap-2 mb-3">
                <span class="dot"></span>
                <span class="text-xs font-semibold tracking-widest uppercase" style="color:var(--green-text);">Portal
                    Access</span>
            </div>
            <h3 class="text-3xl font-bold text-white mb-2">Staff Login</h3>
            <p class="text-sm mb-10" style="color:var(--muted);">Select your role to access the administration portal
            </p>
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

    {{-- ════════════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════════════ --}}
    <footer style="background:var(--bg);border-top:1px solid var(--border);" class="py-8 px-5">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
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
