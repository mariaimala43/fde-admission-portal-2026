<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Merit Lists — {{ $settings['portal_title'] ?? 'FDE Admission Portal' }}</title>
    @if (!empty($settings['portal_favicon']))
        <link rel="icon" type="image/png" href="{{ Storage::url($settings['portal_favicon']) }}">
    @elseif(!empty($settings['app_favicon']))
        <link rel="icon" type="image/png" href="{{ Storage::url($settings['app_favicon']) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
          rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117;
            --bg2: #0f1520;
            --surface: rgba(255,255,255,0.035);
            --surface-h: rgba(255,255,255,0.065);
            --border: rgba(255,255,255,0.07);
            --border-g: rgba(74,160,110,0.4);
            --green: #4aa06e;
            --green-d: #3a8a5c;
            --green-text: #74c99a;
            --muted: #7a8a96;
            --text: #dde4ee;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); -webkit-font-smoothing: antialiased; }
        .page-bg {
            background:
                radial-gradient(ellipse 80% 60% at 0% 20%, rgba(60,130,90,0.11) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 100% 60%, rgba(20,60,120,0.1) 0%, transparent 55%),
                var(--bg);
            min-height: 100vh;
        }
        .navbar {
            background: rgba(13,17,23,0.88);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 100;
        }
        .glass {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            backdrop-filter: blur(10px);
            transition: all 0.28s ease;
        }
        .school-row {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            transition: all 0.25s ease;
        }
        .school-row:hover { background: var(--surface-h); border-color: var(--border-g); }
        .btn-g {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--green); color: white;
            padding: 10px 26px; border-radius: 50px;
            font-size: 14px; font-weight: 600; font-family: inherit;
            border: none; cursor: pointer; text-decoration: none; transition: all .25s;
        }
        .btn-g:hover { background: var(--green-d); box-shadow: 0 0 28px rgba(74,160,110,0.4); transform: translateY(-1px); }
        .search-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-family: inherit;
            padding: 10px 16px 10px 42px;
            font-size: 14px;
            width: 100%;
            outline: none;
            transition: border-color .2s, background .2s;
        }
        .search-input:focus { border-color: rgba(74,160,110,0.5); background: rgba(255,255,255,0.07); }
        .search-input::placeholder { color: var(--muted); }
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--green-d); border-radius: 3px; }
    </style>
</head>
<body class="page-bg">

    {{-- ── Navbar ── --}}
    <nav class="navbar">
        <div class="max-w-5xl mx-auto px-5 py-3.5 flex items-center justify-between gap-6">
            <a href="{{ route('portal.index') }}" class="flex items-center gap-3 no-underline shrink-0">
                @if (!empty($settings['portal_logo']))
                    <img src="{{ Storage::url($settings['portal_logo']) }}"
                         alt="{{ $settings['portal_title'] ?? 'FDE' }}"
                         style="height:36px;width:auto;object-fit:contain;">
                @elseif(!empty($settings['app_logo']))
                    <img src="{{ Storage::url($settings['app_logo']) }}"
                         alt="{{ $settings['app_name'] ?? 'FDE' }}"
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
                <a href="{{ route('portal.index') }}"
                   class="text-sm px-4 py-2 rounded-full border font-medium transition"
                   style="border-color:var(--border);color:var(--text);"
                   onmouseover="this.style.borderColor='rgba(255,255,255,0.2)'"
                   onmouseout="this.style.borderColor='var(--border)'">
                    ← Back to Portal
                </a>
                <a href="{{ route('login') }}" class="btn-g" style="padding:8px 20px;font-size:13px;">Sign In</a>
            </div>
        </div>
    </nav>

    {{-- ── Page Header ── --}}
    <div class="max-w-5xl mx-auto px-5 pt-10 pb-6">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
                 style="background:rgba(37,99,235,0.2);border:1px solid rgba(37,99,235,0.3);">
                📋
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white leading-tight">Merit Lists</h1>
                <p class="text-sm mt-1" style="color:var(--muted);">
                    Download merit list files published by FDE schools
                    @if($institutions->isNotEmpty())
                        &mdash; <span class="text-white font-semibold">{{ $institutions->count() }}</span>
                        {{ $institutions->count() === 1 ? 'school' : 'schools' }} available
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ── Search + List ── --}}
    <div class="max-w-5xl mx-auto px-5 pb-16"
         x-data="{ search: '' }">

        @if($institutions->isEmpty())
            <div class="school-row p-16 text-center">
                <div class="text-5xl mb-4">📋</div>
                <h3 class="text-lg font-bold text-white mb-2">No merit lists published yet</h3>
                <p class="text-sm" style="color:var(--muted);">
                    Schools will appear here once they upload their merit list files.
                </p>
                <a href="{{ route('portal.index') }}" class="btn-g inline-flex mt-6">Browse Schools</a>
            </div>
        @else
            {{-- Search bar --}}
            <div class="relative mb-6">
                <div class="pointer-events-none absolute inset-y-0 left-0 pl-4 flex items-center">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         style="color:var(--muted);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </div>
                <input type="text" x-model="search"
                       placeholder="Search by school name…"
                       class="search-input" />
                <button x-show="search" x-cloak @click="search=''"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center"
                        style="color:var(--muted);">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Filtering hint (shown when search active) --}}
            <p x-show="search" x-cloak
               class="text-center text-sm py-4 mb-2"
               style="color:var(--muted);">
                Filtering {{ $institutions->count() }} {{ $institutions->count() === 1 ? 'school' : 'schools' }}…
            </p>

            <div class="space-y-4">
                @foreach($institutions as $institution)
                <div class="school-row p-5"
                     x-show="!search || '{{ strtolower($institution->name) }}'.includes(search.toLowerCase())">

                    {{-- School header --}}
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center text-base"
                                 style="background:rgba(74,160,110,0.15);border:1px solid rgba(74,160,110,0.25);">
                                🏫
                            </div>
                            <div>
                                <a href="{{ route('portal.show', $institution) }}"
                                   class="text-sm font-bold text-white leading-snug"
                                   style="text-decoration:none;"
                                   onmouseover="this.style.textDecoration='underline'"
                                   onmouseout="this.style.textDecoration='none'">
                                    {{ $institution->name }}
                                </a>
                                <p class="text-xs mt-0.5" style="color:var(--muted);">
                                    {{ $institution->sector?->name }} Sector
                                    &middot; {{ $institution->type }}
                                    &middot; {{ ucfirst($institution->gender) }}
                                </p>
                            </div>
                        </div>
                        <span class="flex-shrink-0 text-xs px-2.5 py-1 rounded-full font-semibold"
                              style="background:rgba(37,99,235,0.2);color:#93c5fd;border:1px solid rgba(37,99,235,0.3);">
                            {{ $institution->meritLists->count() }}
                            {{ $institution->meritLists->count() === 1 ? 'file' : 'files' }}
                        </span>
                    </div>

                    {{-- Files list --}}
                    <div class="space-y-0 rounded-xl overflow-hidden"
                         style="border:1px solid rgba(255,255,255,0.06);">
                        @foreach($institution->meritLists as $ml)
                        <div class="flex items-center justify-between gap-3 px-4 py-3
                                    border-b last:border-0"
                             style="border-color:rgba(255,255,255,0.05);">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-lg flex-shrink-0">{{ $ml->fileIcon() }}</span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <p class="text-sm font-medium text-white leading-snug">
                                            {{ $ml->title ?: $ml->original_name }}
                                        </p>
                                        @if($ml->isNew())
                                            <span class="text-xs font-semibold px-1.5 py-0 rounded"
                                                  style="background:rgba(74,160,110,0.25);color:#74c99a;
                                                         border:1px solid rgba(74,160,110,0.3);line-height:1.6;">
                                                New
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs mt-0.5" style="color:var(--muted);">
                                        {{ $ml->fileType() }}
                                        @if($ml->file_size)
                                            &middot; {{ $ml->formattedSize() }}
                                        @endif
                                        &middot; {{ $ml->created_at->format('d M Y') }}
                                        @if($ml->title)
                                            &middot; {{ $ml->original_name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <a href="{{ $ml->downloadUrl() }}"
                               target="_blank" rel="noopener"
                               class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg
                                      text-xs font-semibold transition"
                               style="background:rgba(37,99,235,0.2);color:#93c5fd;border:1px solid rgba(37,99,235,0.3);"
                               onmouseover="this.style.background='rgba(37,99,235,0.35)';this.style.color='#bfdbfe'"
                               onmouseout="this.style.background='rgba(37,99,235,0.2)';this.style.color='#93c5fd'">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        </div>
                        @endforeach
                    </div>

                    {{-- Link to school detail --}}
                    <div class="mt-3 text-right">
                        <a href="{{ route('portal.show', $institution) }}"
                           class="text-xs font-medium transition"
                           style="color:var(--green-text);"
                           onmouseover="this.style.color='white'"
                           onmouseout="this.style.color='var(--green-text)'">
                            View school details →
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

    </div>

    {{-- Footer --}}
    <div class="border-t py-6 text-center text-xs" style="border-color:var(--border);color:var(--muted);">
        {{ $settings['portal_title'] ?? 'FDE Admission Portal' }}
        &middot; {{ $settings['portal_tagline'] ?? 'Government of Pakistan' }}
    </div>

</body>
</html>
