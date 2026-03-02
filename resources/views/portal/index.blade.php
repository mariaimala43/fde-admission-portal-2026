<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FDE Admission Portal 2026–27</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600&family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --navy: #0a1628;
            --gold: #c9a84c;
            --gold2: #e8c97a;
            --cream: #fdf8f0;
            --slate: #1e3a5f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--navy);
        }

        .display {
            font-family: 'Playfair Display', serif;
        }

        .urdu {
            font-family: 'Noto Nastaliq Urdu', serif;
            direction: rtl;
            line-height: 2.2;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Geometric background pattern */
        .geo-bg {
            background-color: var(--navy);
            background-image:
                linear-gradient(30deg, rgba(201, 168, 76, 0.06) 12%, transparent 12.5%, transparent 87%, rgba(201, 168, 76, 0.06) 87.5%, rgba(201, 168, 76, 0.06)),
                linear-gradient(150deg, rgba(201, 168, 76, 0.06) 12%, transparent 12.5%, transparent 87%, rgba(201, 168, 76, 0.06) 87.5%, rgba(201, 168, 76, 0.06)),
                linear-gradient(30deg, rgba(201, 168, 76, 0.06) 12%, transparent 12.5%, transparent 87%, rgba(201, 168, 76, 0.06) 87.5%, rgba(201, 168, 76, 0.06)),
                linear-gradient(150deg, rgba(201, 168, 76, 0.06) 12%, transparent 12.5%, transparent 87%, rgba(201, 168, 76, 0.06) 87.5%, rgba(201, 168, 76, 0.06)),
                linear-gradient(60deg, rgba(201, 168, 76, 0.08) 25%, transparent 25.5%, transparent 75%, rgba(201, 168, 76, 0.08) 75%, rgba(201, 168, 76, 0.08)),
                linear-gradient(60deg, rgba(201, 168, 76, 0.08) 25%, transparent 25.5%, transparent 75%, rgba(201, 168, 76, 0.08) 75%, rgba(201, 168, 76, 0.08));
            background-size: 80px 140px;
            background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px;
        }

        /* Gold shimmer line */
        .gold-line {
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), var(--gold2), var(--gold), transparent);
        }

        /* Animated gradient border on stat cards */
        .stat-card {
            position: relative;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(201, 168, 76, 0.25);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            background: rgba(201, 168, 76, 0.08);
            border-color: rgba(201, 168, 76, 0.6);
            transform: translateY(-4px);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(201, 168, 76, 0.1), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        /* School card */
        .school-card {
            transition: all 0.25s ease;
            border: 1px solid #e8e0d0;
        }

        .school-card:hover {
            border-color: var(--gold);
            box-shadow: 0 8px 32px rgba(10, 22, 40, 0.12), 0 0 0 1px var(--gold);
            transform: translateY(-3px);
        }

        /* Floating orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
        }

        /* Count up animation */
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

        .fade-up {
            animation: fadeUp 0.6s ease forwards;
        }

        /* Search input */
        .search-input {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--gold);
            background: #fff;
        }

        /* Checkbox pill */
        .filter-pill {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.2s;
            cursor: pointer;
        }

        .filter-pill:hover {
            background: rgba(201, 168, 76, 0.15);
            border-color: var(--gold);
        }

        .filter-pill:has(input:checked) {
            background: rgba(201, 168, 76, 0.2);
            border-color: var(--gold);
            color: var(--gold2);
        }

        /* Seat badge */
        .seat-open {
            background: #dcfce7;
            color: #15803d;
        }

        .seat-full {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Login card */
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(201, 168, 76, 0.2);
            transition: all 0.25s;
        }

        .login-card:hover {
            background: rgba(201, 168, 76, 0.12);
            border-color: var(--gold);
            transform: translateY(-4px);
        }

        /* Banner placeholder */
        .banner-slot {
            background: linear-gradient(135deg, rgba(201, 168, 76, 0.1), rgba(30, 58, 95, 0.3));
            border: 2px dashed rgba(201, 168, 76, 0.3);
        }

        /* Divider ornament */
        .ornament {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ornament::before,
        .ornament::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        /* Tag badges */
        .tag {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--cream);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gold);
            border-radius: 3px;
        }
    </style>
</head>

<body x-data="{ lang: 'en', mobileMenu: false }" :dir="lang === 'ur' ? 'rtl' : 'ltr'">

    {{-- ══════════════════════════════════════════════════════
     TOP RIBBON
══════════════════════════════════════════════════════ --}}
    <div style="background: var(--navy);" class="py-2 px-4 text-xs">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <span class="text-gray-400" x-show="lang === 'en'">
                Government of Pakistan · Ministry of Federal Education & Professional Training
            </span>
            <span class="urdu text-gray-400 text-sm" x-show="lang === 'ur'" x-cloak>
                حکومتِ پاکستان · وزارتِ وفاقی تعلیم و پیشہ ورانہ تربیت
            </span>

            <div class="flex items-center gap-3">
                {{-- Language Toggle --}}
                <button @click="lang = lang === 'en' ? 'ur' : 'en'"
                    class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium
                           border transition"
                    style="border-color: rgba(201,168,76,0.4); color: #c9a84c;"
                    onmouseover="this.style.background='rgba(201,168,76,0.1)'"
                    onmouseout="this.style.background='transparent'">
                    <span x-show="lang === 'en'">🌐 اردو</span>
                    <span x-show="lang === 'ur'" x-cloak class="urdu">🌐 English</span>
                </button>

                {{-- Login Button --}}
                <a href="{{ route('login') }}"
                    class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold transition"
                    style="background: var(--gold); color: var(--navy);" onmouseover="this.style.background='#e8c97a'"
                    onmouseout="this.style.background='#c9a84c'">
                    🔐
                    <span x-show="lang === 'en'">Staff Login</span>
                    <span x-show="lang === 'ur'" x-cloak class="urdu">عملہ لاگ ان</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════ --}}
    <div class="geo-bg relative overflow-hidden">

        {{-- Decorative orbs --}}
        <div class="orb w-96 h-96 top-0 -right-20" style="background: rgba(201,168,76,0.07);"></div>
        <div class="orb w-64 h-64 bottom-0 left-10" style="background: rgba(30,58,95,0.4);"></div>

        <div class="max-w-7xl mx-auto px-4 pt-12 pb-0 relative z-10">

            {{-- Logos + Header --}}
            <div class="flex flex-col items-center text-center mb-10">

                {{-- Logo row --}}
                <div class="flex items-center justify-center gap-8 mb-8">

                    {{-- Ministry Logo Slot --}}
                    <div class="w-20 h-20 rounded-full flex items-center justify-center relative"
                        style="background: rgba(255,255,255,0.08); border: 2px solid rgba(201,168,76,0.3);">
                        {{-- Replace src with: {{ $settings->ministry_logo ? asset('storage/'.$settings->ministry_logo) : '' }} --}}
                        <span class="text-4xl">🇵🇰</span>
                        <span class="absolute -bottom-5 text-xs whitespace-nowrap"
                            style="color: rgba(201,168,76,0.6);">Ministry</span>
                    </div>

                    {{-- Center emblem --}}
                    <div class="w-28 h-28 rounded-full flex items-center justify-center"
                        style="background: linear-gradient(135deg, rgba(201,168,76,0.2), rgba(201,168,76,0.05));
                            border: 3px solid rgba(201,168,76,0.5);
                            box-shadow: 0 0 40px rgba(201,168,76,0.15);">
                        {{-- Replace with: <img src="{{ asset('storage/'.$settings->fde_logo) }}" class="w-20 h-20 object-contain" /> --}}
                        <span class="text-5xl">🏛️</span>
                    </div>

                    {{-- FDE Logo Slot --}}
                    <div class="w-20 h-20 rounded-full flex items-center justify-center relative"
                        style="background: rgba(255,255,255,0.08); border: 2px solid rgba(201,168,76,0.3);">
                        <span class="text-4xl">📚</span>
                        <span class="absolute -bottom-5 text-xs whitespace-nowrap"
                            style="color: rgba(201,168,76,0.6);">FDE</span>
                    </div>

                </div>

                {{-- Title --}}
                <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-[0.3em] mb-3" style="color: var(--gold);"
                        x-show="lang === 'en'">
                        Federal Directorate of Education · Islamabad Capital Territory
                    </p>
                    <p class="urdu text-base mb-3" style="color: var(--gold);" x-show="lang === 'ur'" x-cloak>
                        وفاقی ڈائریکٹوریٹ برائے تعلیم · وفاقی دارالحکومت اسلام آباد
                    </p>

                    <h1 class="display text-white mb-2" x-show="lang === 'en'"
                        style="font-size: clamp(2.5rem, 6vw, 4.5rem); line-height: 1.1;">
                        FDE Admission Portal
                        <em style="color: var(--gold);">2026–27</em>
                    </h1>
                    <h1 class="display text-white mb-2" x-show="lang === 'ur'" x-cloak
                        style="font-size: clamp(2rem, 5vw, 3.5rem); line-height: 1.5; font-family: 'Noto Nastaliq Urdu', serif;">
                        وفاقی ڈائریکٹوریٹ داخلہ پورٹل
                        <em style="color: var(--gold);">۲۰۲۶–۲۷</em>
                    </h1>
                </div>

                <div class="gold-line w-48 mt-6 mb-4"></div>

            </div>

            {{-- ── Banner Area (managed via admin) ── --}}
            <div class="banner-slot rounded-2xl p-6 mb-10 text-center" style="min-height: 120px;">
                {{--
                Admin panel replaces this with:
                @if ($settings->banner_image)
                    <img src="{{ asset('storage/'.$settings->banner_image) }}" class="w-full rounded-xl object-cover max-h-48" />
                @elseif($settings->banner_text)
                    <div>{{ $settings->banner_text }}</div>
                @endif
            --}}
                <div class="flex items-center justify-center gap-4 flex-wrap">
                    <div class="text-3xl">📢</div>
                    <div x-show="lang === 'en'" class="text-left">
                        <h2 class="font-semibold text-white text-lg">
                            Admissions Now Open — Academic Year 2026–27
                        </h2>
                        <p class="text-sm mt-1" style="color: rgba(255,255,255,0.7);">
                            Free quality education from ECE to Class XII in government schools across Islamabad.
                            No tuition fee. Apply today.
                        </p>
                        @if ($academicYear)
                            <p class="text-xs mt-2" style="color: var(--gold);">
                                📅 Admission open until:
                                {{ \Carbon\Carbon::parse($academicYear->admission_end)->format('d M Y') }}
                            </p>
                        @endif
                    </div>
                    <div x-show="lang === 'ur'" x-cloak class="text-right">
                        <h2 class="urdu font-semibold text-white text-lg">
                            داخلے کھل گئے — تعلیمی سال ۲۰۲۶–۲۷
                        </h2>
                        <p class="urdu text-sm mt-1" style="color: rgba(255,255,255,0.7);">
                            اسلام آباد کے سرکاری اسکولوں میں ای سی ای سے بارہویں تک مفت معیاری تعلیم۔ آج ہی درخواست دیں۔
                        </p>
                        @if ($academicYear)
                            <p class="urdu text-xs mt-2" style="color: var(--gold);">
                                📅 داخلے کی آخری تاریخ:
                                {{ \Carbon\Carbon::parse($academicYear->admission_end)->format('d M Y') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Live Stats ── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">

                @foreach ([[$totalInstitutions, 'Total Schools', 'کل اسکول', '🏫'], [$openInstitutions, 'Admissions Open', 'داخلے کھلے', '✅'], [$totalSeatsAvailable, 'Seats Available', 'نشستیں دستیاب', '💺'], [$totalAdmittedThisYear, 'Admitted This Year', 'اس سال داخلے', '🎓']] as [$val, $labelEn, $labelUr, $icon])
                    <div class="stat-card rounded-2xl p-5 text-center">
                        <div class="text-2xl mb-2">{{ $icon }}</div>
                        <p class="display text-white font-bold" style="font-size: 2rem;">
                            {{ number_format($val) }}
                        </p>
                        <p class="text-xs mt-1 font-medium" x-show="lang === 'en'" style="color: rgba(201,168,76,0.8);">
                            {{ $labelEn }}</p>
                        <p class="urdu text-sm mt-1" x-show="lang === 'ur'" x-cloak
                            style="color: rgba(201,168,76,0.8);">{{ $labelUr }}</p>
                    </div>
                @endforeach

            </div>

            {{-- ── Search Box ── --}}
            <form method="GET" action="{{ route('portal.index') }}"
                class="rounded-3xl overflow-hidden shadow-2xl mb-0"
                style="background: rgba(255,255,255,0.97); border: 1px solid rgba(201,168,76,0.2);">

                {{-- Search row --}}
                <div class="flex items-stretch gap-0 border-b" style="border-color: #e8e0d0;">
                    <div class="flex-1 flex items-center px-5 gap-3">
                        <span class="text-gray-400 text-lg">🔍</span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            :placeholder="lang === 'en' ? 'Search school name, area...' : 'اسکول کا نام تلاش کریں...'"
                            class="w-full py-5 text-sm bg-transparent border-none outline-none text-gray-800 placeholder-gray-400" />
                    </div>
                    <button type="submit" class="px-8 py-5 font-semibold text-sm transition"
                        style="background: var(--navy); color: white;"
                        onmouseover="this.style.background='var(--slate)'"
                        onmouseout="this.style.background='var(--navy)'">
                        <span x-show="lang === 'en'">Find Schools</span>
                        <span x-show="lang === 'ur'" x-cloak class="urdu">تلاش کریں</span>
                    </button>
                </div>

                {{-- Filters row --}}
                <div class="px-5 py-4 grid grid-cols-2 md:grid-cols-4 gap-3 border-b" style="border-color: #e8e0d0;">

                    <select name="sector_id"
                        class="border rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:outline-none transition"
                        style="border-color: #d4c9b0;" onfocus="this.style.borderColor='var(--gold)'"
                        onblur="this.style.borderColor='#d4c9b0'">
                        <option value="" x-text="lang === 'en' ? 'All Sectors' : 'تمام سیکٹر'"></option>
                        @foreach ($sectors as $s)
                            <option value="{{ $s->id }}"
                                {{ request('sector_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="class_id"
                        class="border rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:outline-none transition"
                        style="border-color: #d4c9b0;" onfocus="this.style.borderColor='var(--gold)'"
                        onblur="this.style.borderColor='#d4c9b0'">
                        <option value="" x-text="lang === 'en' ? 'Any Class' : 'کوئی بھی جماعت'"></option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}"
                                {{ request('class_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="gender"
                        class="border rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:outline-none transition"
                        style="border-color: #d4c9b0;" onfocus="this.style.borderColor='var(--gold)'"
                        onblur="this.style.borderColor='#d4c9b0'">
                        <option value="" x-text="lang === 'en' ? 'Boys & Girls' : 'لڑکے اور لڑکیاں'"></option>
                        <option value="boys" {{ request('gender') == 'boys' ? 'selected' : '' }}
                            x-text="lang === 'en' ? 'Boys Schools' : 'لڑکوں کے اسکول'"></option>
                        <option value="girls" {{ request('gender') == 'girls' ? 'selected' : '' }}
                            x-text="lang === 'en' ? 'Girls Schools' : 'لڑکیوں کے اسکول'"></option>
                        <option value="co_education" {{ request('gender') == 'co_education' ? 'selected' : '' }}
                            x-text="lang === 'en' ? 'Co-Education'  : 'مشترک تعلیم'"></option>
                    </select>

                    <select name="type"
                        class="border rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:outline-none transition"
                        style="border-color: #d4c9b0;" onfocus="this.style.borderColor='var(--gold)'"
                        onblur="this.style.borderColor='#d4c9b0'">
                        <option value="" x-text="lang === 'en' ? 'All Types' : 'تمام اقسام'"></option>
                        @foreach (['I-V', 'I-VIII', 'I-X', 'I-XII', 'VI-VIII', 'VI-X', 'VI-XII', 'Model_College'] as $t)
                            <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>
                                {{ $t }}</option>
                        @endforeach
                    </select>

                </div>

                {{-- Facility pills --}}
                <div class="px-5 py-4 flex flex-wrap gap-2">
                    @foreach ([
        'has_transport' => ['🚌', 'Transport', 'ٹرانسپورٹ'],
        'has_meal_program' => ['🍱', 'Meal Program', 'کھانا پروگرام'],
        'has_matric_tech' => ['⚙️', 'Matric Tech', 'میٹرک ٹیک'],
        'has_evening_classes' => ['🌙', 'Evening Classes', 'شام کلاسز'],
        'is_cambridge' => ['🎓', 'Cambridge', 'کیمبرج'],
        'has_ece' => ['👶', 'ECE Center', 'ای سی ای مرکز'],
    ] as $key => [$icon, $en, $ur])
                        <label
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs cursor-pointer
                              font-medium border transition select-none"
                            style="border-color: #d4c9b0; color: #555;"
                            onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--navy)'"
                            onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='#d4c9b0'; this.style.color='#555';}">
                            <input type="checkbox" name="{{ $key }}" value="1"
                                {{ request($key) ? 'checked' : '' }} class="w-3.5 h-3.5 rounded"
                                style="accent-color: var(--gold)" />
                            {{ $icon }}
                            <span x-show="lang === 'en'">{{ $en }}</span>
                            <span x-show="lang === 'ur'" x-cloak class="urdu">{{ $ur }}</span>
                        </label>
                    @endforeach

                    <a href="{{ route('portal.index') }}"
                        class="flex items-center px-3 py-1.5 rounded-full text-xs font-medium border transition"
                        style="border-color: #d4c9b0; color: #888;"
                        onmouseover="this.style.borderColor='#999'; this.style.color='#333'"
                        onmouseout="this.style.borderColor='#d4c9b0'; this.style.color='#888'">
                        <span x-show="lang === 'en'">✕ Clear all</span>
                        <span x-show="lang === 'ur'" x-cloak class="urdu">✕ صاف کریں</span>
                    </a>
                </div>

            </form>

        </div>

        {{-- Curved bottom --}}
        <div
            style="height: 60px; background: var(--cream); clip-path: ellipse(55% 100% at 50% 100%);
                margin-top: -1px; position: relative; z-index: 5;">
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
     SCHOOL LISTINGS
══════════════════════════════════════════════════════ --}}
    <div class="max-w-7xl mx-auto px-4 py-8">

        {{-- Section header --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <div class="ornament mb-3" style="width: 200px;">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em]"
                        style="color: var(--gold); white-space: nowrap;">
                        <span x-show="lang === 'en'">Available Schools</span>
                        <span x-show="lang === 'ur'" x-cloak class="urdu">دستیاب اسکول</span>
                    </span>
                </div>
                <h2 class="display text-3xl font-bold" style="color: var(--navy);" x-show="lang === 'en'">
                    Schools with Open Admissions
                </h2>
                <h2 class="display text-2xl font-bold urdu" style="color: var(--navy);" x-show="lang === 'ur'"
                    x-cloak>
                    کھلے داخلوں والے اسکول
                </h2>
                <p class="text-sm mt-1 text-gray-500">
                    <span x-show="lang === 'en'">{{ $institutions->count() }} schools found</span>
                    <span x-show="lang === 'ur'" x-cloak class="urdu">{{ $institutions->count() }} اسکول ملے</span>
                </p>
            </div>
        </div>

        @if ($institutions->isEmpty())
            {{-- Empty state --}}
            <div class="text-center py-24 rounded-3xl"
                style="background: rgba(201,168,76,0.04); border: 1px dashed rgba(201,168,76,0.3);">
                <div class="text-6xl mb-4">🏫</div>
                <h3 class="display text-2xl font-bold mb-2" style="color: var(--navy);">
                    <span x-show="lang === 'en'">No schools found</span>
                    <span x-show="lang === 'ur'" x-cloak class="urdu">کوئی اسکول نہیں ملا</span>
                </h3>
                <p class="text-gray-500 text-sm">
                    <span x-show="lang === 'en'">Try adjusting your search filters above</span>
                    <span x-show="lang === 'ur'" x-cloak class="urdu">اوپر فلٹر تبدیل کریں</span>
                </p>
                <a href="{{ route('portal.index') }}"
                    class="mt-6 inline-block px-6 py-3 rounded-full text-sm font-semibold transition"
                    style="background: var(--navy); color: white;">
                    <span x-show="lang === 'en'">Show All Schools</span>
                    <span x-show="lang === 'ur'" x-cloak class="urdu">تمام اسکول دکھائیں</span>
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($institutions as $inst)
                    @php
                        $instSeats = $seatData[$inst->id] ?? collect();
                        $totalSeats = $instSeats->sum('total_seats');
                        $totalExist = $instSeats->sum('existing_enrollment');
                        $totalAdmit = $admissionTotals[$inst->id]?->total_admitted ?? 0;
                        $totalAvail = max(0, $totalSeats - $totalExist - $totalAdmit);
                    @endphp

                    <div class="school-card bg-white rounded-2xl overflow-hidden flex flex-col">

                        {{-- Top accent bar --}}
                        <div class="h-1"
                            style="background: {{ $totalAvail > 0 ? 'linear-gradient(90deg, var(--gold), var(--gold2))' : '#e5e7eb' }};">
                        </div>

                        {{-- Card body --}}
                        <div class="p-5 flex-1">

                            {{-- School name + availability --}}
                            <div class="flex justify-between items-start gap-3 mb-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold leading-snug text-sm" style="color: var(--navy);">
                                        {{ $inst->name }}
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $inst->sector?->name }} Sector
                                        @if ($inst->address)
                                            &nbsp;·&nbsp; {{ Str::limit($inst->address, 35) }}
                                        @endif
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 px-2.5 py-1 rounded-full text-xs font-bold
                        {{ $totalAvail > 0 ? 'seat-open' : 'seat-full' }}">
                                    <span x-show="lang === 'en'">
                                        {{ $totalAvail > 0 ? number_format($totalAvail) . ' open' : 'Full' }}
                                    </span>
                                    <span x-show="lang === 'ur'" x-cloak class="urdu">
                                        {{ $totalAvail > 0 ? number_format($totalAvail) . ' نشستیں' : 'بھرا' }}
                                    </span>
                                </span>
                            </div>

                            {{-- Info tags --}}
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                <span class="tag"
                                    style="background: rgba(10,22,40,0.06); color: var(--navy);">{{ $inst->type }}</span>
                                <span class="tag" style="background: rgba(10,22,40,0.06); color: var(--navy);">
                                    {{ ucfirst(str_replace('_', ' ', $inst->gender)) }}
                                </span>
                                <span class="tag" style="background: rgba(10,22,40,0.06); color: var(--navy);">
                                    {{ ucfirst($inst->shift) }}
                                </span>
                                @if ($inst->is_cambridge)
                                    <span class="tag" style="background: #ede9fe; color: #5b21b6;">🎓
                                        Cambridge</span>
                                @endif
                                @if ($inst->has_transport)
                                    <span class="tag" style="background: #fef9c3; color: #854d0e;">🚌</span>
                                @endif
                                @if ($inst->has_meal_program)
                                    <span class="tag" style="background: #dcfce7; color: #166534;">🍱</span>
                                @endif
                                @if ($inst->has_matric_tech)
                                    <span class="tag" style="background: #fff7ed; color: #9a3412;">⚙️</span>
                                @endif
                                @if ($inst->has_evening_classes)
                                    <span class="tag" style="background: #f3e8ff; color: #6b21a8;">🌙</span>
                                @endif
                                @if ($inst->has_ece)
                                    <span class="tag" style="background: #fce7f3; color: #9d174d;">👶 ECE</span>
                                @endif
                            </div>

                            {{-- Class seats grid --}}
                            @if ($instSeats->count())
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider mb-2"
                                        style="color: var(--gold);">
                                        <span x-show="lang === 'en'">Available by Class</span>
                                        <span x-show="lang === 'ur'" x-cloak class="urdu">جماعت وار نشستیں</span>
                                    </p>
                                    <div class="grid grid-cols-4 gap-1.5">
                                        @foreach ($instSeats->sortBy('class_id')->take(8) as $ic)
                                            @php $avail = max(0, $ic->total_seats - $ic->existing_enrollment - $totalAdmit); @endphp
                                            <div class="rounded-xl p-2 text-center transition"
                                                style="{{ $avail > 0
                                                    ? 'background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.2);'
                                                    : 'background: #f9fafb; border: 1px solid #e5e7eb;' }}">
                                                <p class="leading-none truncate"
                                                    style="font-size: 10px; color: #9ca3af;">
                                                    {{ $ic->classModel?->name }}
                                                </p>
                                                <p class="font-bold mt-1"
                                                    style="font-size: 13px; color: {{ $avail > 0 ? 'var(--gold)' : '#d1d5db' }}">
                                                    {{ $avail > 0 ? $avail : '—' }}
                                                </p>
                                            </div>
                                        @endforeach
                                        @if ($instSeats->count() > 8)
                                            <div class="rounded-xl p-2 text-center flex items-center justify-center"
                                                style="background: #f9fafb; border: 1px solid #e5e7eb;">
                                                <span
                                                    style="font-size: 11px; color: #9ca3af;">+{{ $instSeats->count() - 8 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                        </div>

                        {{-- Card footer --}}
                        <div class="px-5 py-3 flex justify-between items-center"
                            style="background: #fafaf8; border-top: 1px solid #e8e0d0;">
                            @if ($inst->contact_number)
                                <span class="text-xs text-gray-400">📞 {{ $inst->contact_number }}</span>
                            @else
                                <span></span>
                            @endif
                            <a href="{{ route('portal.show', $inst) }}"
                                class="text-xs font-semibold transition hover:opacity-70" style="color: var(--navy);">
                                <span x-show="lang === 'en'">View Details →</span>
                                <span x-show="lang === 'ur'" x-cloak class="urdu">تفصیل دیکھیں ←</span>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </div>

    {{-- ══════════════════════════════════════════════════════
     STAFF LOGIN
══════════════════════════════════════════════════════ --}}
    <div class="geo-bg py-16 px-4 mt-16 relative overflow-hidden">
        <div class="orb w-64 h-64 top-0 right-0" style="background: rgba(201,168,76,0.06);"></div>

        <div class="max-w-4xl mx-auto text-center relative z-10">

            <p class="text-xs font-semibold uppercase tracking-[0.3em] mb-2" style="color: var(--gold);">
                <span x-show="lang === 'en'">Portal Access</span>
                <span x-show="lang === 'ur'" x-cloak class="urdu">پورٹل تک رسائی</span>
            </p>
            <h3 class="display text-3xl font-bold text-white mb-2" x-show="lang === 'en'">Staff Login</h3>
            <h3 class="display text-2xl font-bold text-white mb-2 urdu" x-show="lang === 'ur'" x-cloak>عملہ لاگ ان
            </h3>

            <div class="gold-line w-32 mx-auto mb-8"></div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([['HoI', 'Head of Institution', 'پرنسپل', '🏫'], ['AEO', 'Area Education Officer', 'ایریا ایجوکیشن افسر', '👤'], ['FDE Cell', 'FDE Admission Cell', 'ایف ڈی ای سیل', '🏢'], ['Admin', 'System Administrator', 'ایڈمن', '⚙️']] as [$title, $roleEn, $roleUr, $icon])
                    <a href="{{ route('login') }}" class="login-card rounded-2xl p-5 text-center block">
                        <div class="text-3xl mb-3">{{ $icon }}</div>
                        <p class="font-bold text-white text-sm mb-1">{{ $title }}</p>
                        <p class="text-xs" x-show="lang === 'en'" style="color: rgba(201,168,76,0.7);">
                            {{ $roleEn }}</p>
                        <p class="urdu text-xs" x-show="lang === 'ur'" x-cloak style="color: rgba(201,168,76,0.7);">
                            {{ $roleUr }}</p>
                    </a>
                @endforeach
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════ --}}
    <footer style="background: var(--navy);" class="py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="gold-line mb-6"></div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <p class="text-sm font-medium text-white" x-show="lang === 'en'">
                        Federal Directorate of Education &nbsp;·&nbsp; Islamabad Capital Territory
                    </p>
                    <p class="urdu text-sm font-medium text-white" x-show="lang === 'ur'" x-cloak>
                        وفاقی ڈائریکٹوریٹ برائے تعلیم · اسلام آباد
                    </p>
                    <p class="text-xs mt-1" style="color: rgba(255,255,255,0.4);">
                        © {{ now()->year }} FDE Admissions Portal · All rights reserved
                    </p>
                </div>
                <div class="text-xs" style="color: rgba(201,168,76,0.6);">
                    <span x-show="lang === 'en'">Academic Year 2026–27</span>
                    <span x-show="lang === 'ur'" x-cloak class="urdu">تعلیمی سال ۲۰۲۶–۲۷</span>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
