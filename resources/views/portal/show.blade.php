<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $institution->name }} — FDE Admission Portal</title>
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

<body class="page-bg" x-data="{ lang: 'en' }" :dir="lang === 'ur' ? 'rtl' : 'ltr'">

    {{-- Portal notice --}}
    @if (!empty($settings['portal_notice']))
        <div style="background:rgba(234,179,8,0.12);border-bottom:1px solid rgba(234,179,8,0.25);"
            class="py-2 px-4 text-center text-xs font-semibold text-yellow-300">
            ⚠️ {{ $settings['portal_notice'] }}
        </div>
    @endif

    {{-- ── Navbar ── --}}
    <nav class="navbar">
        <div class="max-w-5xl mx-auto px-5 py-3.5 flex items-center justify-between gap-6">
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
        $totalSeats = $seatData->sum('total_seats');
        $totalExist = $seatData->sum('existing_enrollment');
        $totalAdmit = $admissionTotal->sum('total_admitted');
        $totalAvail = max(0, $totalSeats - $totalExist - $totalAdmit);
        $fillPct = $totalSeats > 0 ? min(100, round((($totalExist + $totalAdmit) / $totalSeats) * 100)) : 0;
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
                    <p class="text-xs mb-4" style="color:var(--muted);">of {{ number_format($totalSeats) }} total seats
                    </p>
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
            <div class="glass p-6 flex flex-col gap-5">
                <div class="flex items-center gap-2 mb-1">
                    <div class="icon-box" style="width:36px;height:36px;border-radius:10px;font-size:16px;">📊</div>
                    <p class="text-sm font-semibold text-white">Quick Stats</p>
                </div>
                @foreach ([['Total Capacity', number_format($totalSeats), 'var(--text)'], ['Existing Students', number_format($totalExist), '#fb923c'], ['Newly Admitted', number_format($totalAdmit), '#60a5fa'], ['Seats Available', number_format($totalAvail), $totalAvail > 0 ? 'var(--green-text)' : '#fca5a5']] as [$label, $val, $color])
                    <div class="flex justify-between items-center py-2.5"
                        style="border-bottom:1px solid var(--border);">
                        <p class="text-xs" style="color:var(--muted);">{{ $label }}</p>
                        <p class="text-sm font-bold" style="color:{{ $color }};">{{ $val }}</p>
                    </div>
                @endforeach
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

        {{-- Class table --}}
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
                                $admitted = $admissionTotal[$ic->class_id]?->total_admitted ?? 0;
                                $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                                $totalEnrl = $ic->existing_enrollment + $admitted;
                                $rFill =
                                    $ic->total_seats > 0
                                        ? min(
                                            100,
                                            round((($ic->existing_enrollment + $admitted) / $ic->total_seats) * 100),
                                        )
                                        : 0;
                            @endphp
                            <tr>
                                <td>
                                    {{ $ic->classModel?->name }}
                                    @if ($ic->classModel?->is_ece)
                                        <span class="bdg bdg-pink ml-1" style="font-size:10px;">ECE</span>
                                    @endif
                                </td>
                                <td class="font-semibold" style="color:#fb923c;">
                                    {{ number_format($ic->existing_enrollment) }}</td>
                                <td style="color:var(--muted);">{{ number_format($ic->total_seats) }}</td>
                                <td>
                                    <span class="bdg {{ $available > 0 ? 'bdg-open' : 'bdg-full' }}">
                                        {{ $available > 0 ? number_format($available) : 'Full' }}
                                    </span>
                                </td>
                                <td class="font-semibold" style="color:#60a5fa;">{{ number_format($admitted) }}</td>
                                <td>
                                    <p class="font-bold text-white">{{ number_format($totalEnrl) }}</p>
                                    <div class="prog w-14 mx-auto">
                                        <div class="prog-fill"
                                            style="width:{{ $rFill }}%;background:{{ $rFill < 80 ? 'var(--green)' : ($rFill < 95 ? '#f97316' : '#ef4444') }};">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="color:var(--muted);">TOTAL</td>
                            <td style="color:#fb923c;">{{ number_format($totalExist) }}</td>
                            <td style="color:var(--muted);">{{ number_format($totalSeats) }}</td>
                            <td><span
                                    class="bdg {{ $totalAvail > 0 ? 'bdg-open' : 'bdg-full' }}">{{ number_format($totalAvail) }}</span>
                            </td>
                            <td style="color:#60a5fa;">{{ number_format($totalAdmit) }}</td>
                            <td class="text-white">{{ number_format($totalExist + $totalAdmit) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

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
                    <span style="font-size:15px;">🏛️</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">Federal Directorate of Education</p>
                    <p class="text-xs" style="color:var(--muted);">© {{ now()->year }} FDE Admissions Portal ·
                        Islamabad Capital Territory</p>
                </div>
            </div>
            <p class="text-xs" style="color:var(--muted);">Academic Year 2026–27</p>
        </div>
    </footer>

</body>

</html>
