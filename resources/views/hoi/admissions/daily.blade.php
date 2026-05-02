{{-- SAVE AS: resources/views/hoi/admissions/daily.blade.php --}}
@extends('layouts.app')
@section('title', 'Daily Admissions — ' . \Carbon\Carbon::parse($selectedDate)->format('d M Y'))

@section('content')

    {{-- ── Daily Admission Reminder Popup ─────────────────────────────── --}}
    @if ($showReminder ?? false)
        <div x-data="{ open: true }" x-show="open" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center px-4"
            style="background: rgba(0,0,0,0.45); display:none;" @keydown.escape.window="open = false">
            {{-- Popup card --}}
            <div x-show="open" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-90 translate-y-4"
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto overflow-hidden"
                @click.outside="open = false">
                {{-- Top accent bar --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-amber-400 to-orange-500"></div>

                {{-- Content --}}
                <div class="p-6">
                    {{-- Icon + Title --}}
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-amber-50 flex items-center justify-center">
                            <svg class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0
                                         00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0
                                         .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-gray-900 leading-snug">
                                Today's Admission Entry Pending
                            </h3>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ now()->format('l, d F Y') }}
                            </p>
                        </div>
                        {{-- Close X --}}
                        <button @click="open = false"
                            class="flex-shrink-0 p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                            aria-label="Dismiss">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Message --}}
                    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <p class="text-sm text-amber-800 font-medium leading-relaxed">
                            📋 Don't forget to update today's admission data.
                        </p>
                        <p class="text-xs text-amber-700 mt-1">
                            {{ $reminderMessage }}
                        </p>
                    </div>

                    {{-- School name --}}
                    <p class="text-xs text-gray-400 mt-3 text-center">
                        {{ Auth::user()->institution?->name ?? '' }}
                    </p>

                    {{-- Action buttons --}}
                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
                        {{-- Primary: scroll down to form --}}
                        <button
                            @click="open = false;
                            $nextTick(() => {
                                document.getElementById('admissionFormEl')
                                    ?.scrollIntoView({ behavior: 'smooth' });
                            })"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5
                           bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm
                           font-semibold transition shadow-sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2
                                         2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Update Now
                        </button>
                        {{-- Secondary: dismiss --}}
                        <button @click="open = false"
                            class="flex-1 inline-flex items-center justify-center px-4 py-2.5
                           border border-gray-300 text-gray-600 rounded-xl text-sm
                           font-medium hover:bg-gray-50 transition">
                            Remind Me Later
                        </button>
                    </div>
                </div>

                {{-- Bottom urgency note --}}
                <div class="px-6 pb-4">
                    <p class="text-xs text-center text-gray-400">
                        Daily entry window closes at
                        <span class="font-semibold text-gray-600">
                            {{ \Carbon\Carbon::createFromTimeString($academicYear->daily_cutoff_time ?? '23:59:00')->format('h:i A') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Page Header ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Daily Admissions Entry</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->name }}
                @if ($institution->sector)
                    <span class="mx-2 text-gray-300">·</span>
                    <span class="text-gray-500">{{ $institution->sector->name }}</span>
                @endif
                <span class="mx-2 text-gray-300">·</span>
                <span class="font-semibold text-blue-900">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}
                </span>
                @if ($academicYear)
                    <span class="mx-2 text-gray-300">·</span>
                    <span class="text-gray-500">{{ $academicYear->name }}</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            @if ($anyVerified)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Verified &amp; Finalised
                </span>
            @elseif($anySubmitted)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></span>Submitted
                </span>
            @elseif($anyDraft)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Draft Saved
                </span>
            @else
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Not Started
                </span>
            @endif

            @if ($isToday && $academicYear?->daily_cutoff_time)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    Editable until
                    {{ \Carbon\Carbon::createFromTimeString($academicYear->daily_cutoff_time)->format('g:i A') }}
                </span>
            @endif

            <a href="{{ route('hoi.admissions.report') }}"
                class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                📋 View Report
            </a>
        </div>
    </div>

    {{-- ── Date Picker Navigation ─────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3 mb-4 bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-2.5">
        <span class="text-sm font-medium text-gray-500">📅 Date:</span>
        <form method="GET" action="{{ route('hoi.admissions.daily') }}" class="flex items-center gap-2" id="dateNavForm">
            <input type="date" name="date" value="{{ $selectedDate }}" max="{{ $today }}"
                @if ($academicYear) min="{{ $academicYear->start_date }}" @endif
                onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            {{-- Preserve active shift when navigating dates (updated by shift buttons below) --}}
            <input type="hidden" name="shift" id="dateNavShift" value="{{ $defaultShift }}">
            @if (!$isToday)
                <a href="{{ route('hoi.admissions.daily', ['shift' => $defaultShift]) }}"
                    class="px-3 py-1.5 rounded-lg text-sm bg-blue-100 text-blue-700 hover:bg-blue-200 transition font-medium whitespace-nowrap">
                    ↩ Today
                </a>
            @endif
        </form>
        @if (!$isToday)
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                ✏️ Editing past date: {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
            </span>
        @endif
    </div>

    {{-- ── Flash Messages ───────────────────────────────────────────── --}}
    @if (session('success'))
        <div
            class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ⚠️ {{ session('warning') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ── Banners ──────────────────────────────────────────────────── --}}

    {{-- Closed banner — highest priority --}}
    @if ($admissionStatus === 'closed')
        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3 mb-5 text-sm text-red-800 flex items-start gap-2">
            <span class="mt-0.5">🚫</span>
            <div>
                <strong>Admissions Closed.</strong>
                The FDE Cell has closed admissions for your school. No new data can be submitted.
                Contact the FDE Cell if you believe this is an error.
            </div>
        </div>
    @endif

    {{-- By-approval mode banner --}}
    @if ($admissionStatus === 'by_approval' && !$anyVerified)
        <div
            class="bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-3 mb-5 text-sm text-yellow-800 flex items-start gap-2">
            <span class="mt-0.5">⏳</span>
            <div>
                <strong>Approval Required.</strong>
                Your school is in <em>approval mode</em>. Submissions will be sent to the FDE Cell
                for verification before being finalised. Click <strong>Submit for Approval</strong> when ready.
            </div>
        </div>
    @endif

    @if (!$anyVerified && !$anyDraft && !$anySubmitted && $isToday && $admissionStatus !== 'closed')
        <div
            class="bg-blue-50 border border-blue-100 rounded-xl px-5 py-3 mb-5 text-sm text-blue-700 flex items-start gap-2">
            <span class="mt-0.5">ℹ️</span>
            <div>
                <strong>How it works:</strong> Enter today's numbers →
                <strong>Save Draft</strong> to save progress →
                @if ($admissionStatus === 'by_approval')
                    <strong>Submit for Approval</strong> — FDE Cell will verify before finalising.
                @else
                    <strong>Submit &amp; Finalise</strong> to finalise today's admissions.
                    Submit before
                    <strong>{{ $academicYear?->daily_cutoff_time ? \Carbon\Carbon::createFromTimeString($academicYear->daily_cutoff_time)->format('g:i A') : '11:59 PM' }}</strong>.
                @endif
            </div>
        </div>
    @endif
    @if ($anyVerified)
        <div
            class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 mb-5 text-sm text-green-800 flex items-center gap-2">
            ✅ Admissions for <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
            have been <strong>verified and finalised</strong>. Contact <strong>FDE Cell</strong> for corrections.
        </div>
    @endif
    @if ($anySubmitted && !$anyVerified && $admissionStatus === 'by_approval')
        <div
            class="bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-3 mb-5 text-sm text-yellow-800 flex items-center gap-2">
            ⏳ Data for <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
            has been <strong>submitted for FDE approval</strong>. Awaiting verification.
        </div>
    @endif

    {{-- ── Matric Tech + New Rooms Summary Strip ──────────────────────── --}}
    @if ($hasMatricTech || $newRoomsTotal > 0)
        <div
            class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-3 mb-5 flex flex-wrap gap-5 items-center text-sm">

            @if ($hasMatricTech)
                <div class="flex items-center gap-2">
                    <span class="text-base">⚙️</span>
                    <span class="text-gray-500">Matric Tech Today:</span>
                    <span class="font-bold text-teal-700">{{ number_format($matricTechToday) }}</span>
                    <span class="text-gray-300 mx-1">|</span>
                    <span class="text-gray-500">This Year:</span>
                    <span class="font-bold text-teal-600">{{ number_format($matricTechYear) }}</span>
                </div>
            @endif

            @if ($newRoomsTotal > 0)
                @if ($hasMatricTech)
                    <span class="text-gray-200 hidden md:inline">|</span>
                @endif
                <div class="flex items-center gap-2">
                    <span class="text-base">🏗️</span>
                    <span class="text-gray-500">New Rooms:</span>
                    <span class="font-bold text-emerald-700">{{ $newRoomsTotal }}</span>
                    <span class="text-gray-400 text-xs">built</span>
                    <span class="text-gray-300 mx-1">|</span>
                    <span class="font-semibold text-emerald-600">{{ $newRoomsAllocated }}</span>
                    <span class="text-gray-400 text-xs">allocated</span>
                    <span class="text-gray-300 mx-1">|</span>
                    <span class="font-semibold text-green-600">{{ $newRoomsRemaining }}</span>
                    <span class="text-gray-400 text-xs">available</span>
                </div>
            @endif

        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         ALPINE DATA — injected as global var to avoid JSON/attribute issues
    ════════════════════════════════════════════════════════════════════ --}}
    <script>
        var admissionData = @json($classesData, JSON_UNESCAPED_SLASHES);
    </script>

    <div x-data="dailyAdmission()">

        @if (count($classesData) === 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center my-6">
                <p class="text-yellow-800 font-semibold text-base mb-2">⚠️ No active classes found.</p>
                <p class="text-yellow-700 text-sm mb-4">Set up classes and sections before entering admissions.</p>
                <a href="{{ route('hoi.classes.setup') }}"
                    class="px-5 py-2 bg-blue-900 text-white rounded-lg text-sm font-semibold">
                    Go to Classes &amp; Sections Setup →
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('hoi.admissions.save') }}" id="admissionFormEl">
                @csrf
                <input type="hidden" name="action" :value="submitAction">
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                {{-- Hidden inputs synced by Alpine --}}
                <template x-for="(cls, i) in rows" :key="cls.class_id">
                    <div>
                        <input type="hidden" :name="`admissions[${i}][class_id]`" :value="cls.class_id">
                        <input type="hidden" :name="`admissions[${i}][morning_boys]`" :value="cls.morning_boys">
                        <input type="hidden" :name="`admissions[${i}][morning_girls]`" :value="cls.morning_girls">
                        <input type="hidden" :name="`admissions[${i}][evening_boys]`" :value="cls.evening_boys">
                        <input type="hidden" :name="`admissions[${i}][evening_girls]`" :value="cls.evening_girls">
                        <input type="hidden" :name="`admissions[${i}][morning_oosc_boys]`"
                            :value="cls.morning_oosc_boys">
                        <input type="hidden" :name="`admissions[${i}][morning_oosc_girls]`"
                            :value="cls.morning_oosc_girls">
                        <input type="hidden" :name="`admissions[${i}][morning_p2p_boys]`" :value="cls.morning_p2p_boys">
                        <input type="hidden" :name="`admissions[${i}][morning_p2p_girls]`"
                            :value="cls.morning_p2p_girls">
                        <input type="hidden" :name="`admissions[${i}][evening_oosc_boys]`"
                            :value="cls.evening_oosc_boys">
                        <input type="hidden" :name="`admissions[${i}][evening_oosc_girls]`"
                            :value="cls.evening_oosc_girls">
                        <input type="hidden" :name="`admissions[${i}][evening_p2p_boys]`" :value="cls.evening_p2p_boys">
                        <input type="hidden" :name="`admissions[${i}][evening_p2p_girls]`"
                            :value="cls.evening_p2p_girls">
                        <input type="hidden" :name="`admissions[${i}][matric_tech_count]`"
                            :value="cls.matric_tech_count">
                        <input type="hidden" :name="`admissions[${i}][existing_enrollment]`" :value="cls.existing">
                    </div>
                </template>


                {{-- ════════════════════════════════════════════════════
                     UNIFIED TABLE — MORNING / EVENING SHIFT TOGGLE
                ════════════════════════════════════════════════════ --}}

                {{-- Shift Toggle pill (only for evening/both schools) --}}
                @if ($hasEvening)
                    <div
                        class="flex items-center gap-1 mb-3 bg-white rounded-xl border border-gray-100 shadow-sm p-1 w-fit">
                        <button type="button"
                            @click="activeShift='morning'; document.getElementById('dateNavShift') && (document.getElementById('dateNavShift').value='morning')"
                            :class="activeShift === 'morning' ? 'bg-blue-900 text-white shadow-sm' :
                                'text-gray-500 hover:bg-gray-100'"
                            class="px-5 py-2 rounded-lg text-sm font-semibold transition">
                            🌅 Morning Shift
                        </button>
                        <button type="button"
                            @click="activeShift='evening'; document.getElementById('dateNavShift') && (document.getElementById('dateNavShift').value='evening')"
                            :class="activeShift === 'evening' ? 'bg-indigo-800 text-white shadow-sm' :
                                'text-gray-500 hover:bg-gray-100'"
                            class="px-5 py-2 rounded-lg text-sm font-semibold transition">
                            🌆 Evening Shift
                        </button>
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-5">
                    <div class="px-5 py-3 flex justify-between items-center"
                        :class="activeShift === 'morning' ? 'bg-blue-900' : 'bg-indigo-900'">
                        <span class="text-white font-bold text-sm"
                            x-text="activeShift==='morning' ? '🌅 Morning Shift — Enrollment Data' : '🌆 Evening Shift — Enrollment Data'"></span>
                        <span :class="activeShift === 'morning' ? 'text-blue-200' : 'text-indigo-200'"
                            class="text-xs hidden sm:inline">Available = Total Seats − Existing − Admitted So Far</span>
                    </div>
                    <p class="block sm:hidden text-xs text-gray-400 mb-2 px-4 pt-2 flex items-center gap-1">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        Swipe right to see all columns
                    </p>
                    <div class="overflow-x-auto -mx-4 sm:mx-0">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-gray-100">
                                    <th
                                        class="sticky left-0 z-20 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-28 border-r border-gray-200">
                                        Class</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-16">
                                        Sec.</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-orange-600 uppercase bg-orange-50">
                                        Existing<br>Enroll.</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50">
                                        Total<br>Seats</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-green-600 uppercase bg-green-50">
                                        Available<br><span class="normal-case font-normal text-gray-400">Seats Left</span>
                                    </th>
                                    {{-- Regular --}}
                                    <th colspan="2"
                                        class="px-3 py-3 text-center text-xs font-semibold text-blue-700 uppercase bg-blue-50">
                                        Regular<br><span class="normal-case font-normal text-gray-400">New Admitted</span>
                                    </th>
                                    {{-- OOSC --}}
                                    <th colspan="2"
                                        class="px-3 py-3 text-center text-xs font-semibold text-purple-700 uppercase bg-purple-50">
                                        OOSC<br><span class="normal-case font-normal text-gray-400">Out-of-School</span>
                                    </th>
                                    {{-- P2G --}}
                                    <th colspan="2"
                                        class="px-3 py-3 text-center text-xs font-semibold text-orange-700 uppercase bg-orange-50">
                                        P2G<br><span class="normal-case font-normal text-gray-400">Private to
                                            Government</span>
                                    </th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-blue-900 uppercase bg-blue-100">
                                        Today's<br>Total</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-gray-400 uppercase bg-gray-50">
                                        Status</th>
                                </tr>
                                <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-400">
                                    <th class="sticky left-0 z-20 px-4 py-1 bg-gray-50 border-r border-gray-200"></th>
                                    <th class="px-3 py-1 bg-gray-50"></th>
                                    <th class="px-3 py-1 bg-orange-50"></th>
                                    <th class="px-3 py-1 bg-gray-50"></th>
                                    <th class="px-3 py-1 bg-green-50"></th>
                                    <th class="px-3 py-1 text-center text-blue-500 bg-blue-50">Boys</th>
                                    <th class="px-3 py-1 text-center text-pink-500 bg-blue-50">Girls</th>
                                    <th class="px-3 py-1 text-center text-blue-500 bg-purple-50">Boys</th>
                                    <th class="px-3 py-1 text-center text-pink-500 bg-purple-50">Girls</th>
                                    <th class="px-3 py-1 text-center text-blue-500 bg-orange-50">Boys</th>
                                    <th class="px-3 py-1 text-center text-pink-500 bg-orange-50">Girls</th>
                                    <th class="px-3 py-1 bg-blue-100"></th>
                                    <th class="px-3 py-1 bg-gray-50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(cls, i) in rows" :key="cls.class_id">
                                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors"
                                        :class="isFull(cls) ? 'bg-gray-100 opacity-60 pointer-events-none select-none' :
                                            (isNotConfigured(cls) ? 'bg-yellow-50' :
                                                (isOverLimit(cls) ? 'bg-red-50' : ''))">

                                        <td class="sticky left-0 z-10 px-3 py-2.5 border-r border-gray-200 min-w-[90px] shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)]"
                                            :class="isFull(cls) ? 'bg-gray-100' : (isNotConfigured(cls) ? 'bg-yellow-50' : (
                                                isOverLimit(cls) ? 'bg-red-50' : 'bg-white'))">
                                            <div class="font-semibold text-gray-800 text-sm" x-text="cls.class_name">
                                            </div>
                                            <span x-show="cls.is_ece"
                                                class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">ECE</span>
                                            <span x-show="isNotConfigured(cls)"
                                                class="text-xs px-2 py-0.5 rounded-full font-semibold mt-0.5 block w-fit"
                                                style="background:#fef3c7;color:#92400e;">
                                                ⚙️ Not Set
                                            </span>
                                            <span x-show="isFull(cls)"
                                                class="text-xs bg-gray-500 text-white px-2 py-0.5 rounded-full font-semibold mt-0.5 block w-fit">
                                                🚫 Full
                                            </span>
                                            <span x-show="isOverLimit(cls) && !isFull(cls)"
                                                class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold mt-0.5 block w-fit">
                                                ⚠️ Over limit
                                            </span>
                                            {{-- Live running total, mobile only --}}
                                            <div class="text-xs text-blue-600 mt-0.5 sm:hidden"
                                                x-text="'∑ ' + shiftRowTotal(cls)">
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center text-gray-600 font-medium" x-text="cls.sections">
                                        </td>
                                        {{-- Existing Enrollment (shift-aware for evening schools) --}}
                                        <td class="px-3 py-3 text-center bg-orange-50">
                                            @if (!$anyLocked)
                                                @if (!$hasEvening)
                                                    <input type="number" x-model.number="cls.existing"
                                                        @input="cls.existing = Math.max(0, parseInt($event.target.value)||0); cls.available = Math.max(0, cls.total_seats - cls.existing - (cls.cum_prior || 0))"
                                                        :disabled="isFull(cls)" min="0" max="99999"
                                                        class="w-20 text-center border border-orange-300 rounded-lg px-2 py-1.5 text-sm font-semibold text-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                @else
                                                    <span class="font-semibold text-orange-700"
                                                        x-text="(activeShift==='morning' ? cls.morning_existing : cls.evening_existing).toLocaleString()"></span>
                                                @endif
                                            @else
                                                <span class="font-semibold text-orange-700"
                                                    x-text="{{ $hasEvening ? '(activeShift===\'morning\' ? cls.morning_existing : cls.evening_existing).toLocaleString()' : 'cls.existing.toLocaleString()' }}"></span>
                                            @endif
                                        </td>

                                        {{-- Total Seats (shift-aware for evening schools) --}}
                                        <td class="px-3 py-3 text-center font-medium text-gray-700"
                                            x-text="{{ $hasEvening ? '(activeShift===\'morning\' ? cls.morning_seats : cls.evening_seats).toLocaleString()' : 'cls.total_seats.toLocaleString()' }}">
                                        </td>

                                        {{-- Available (live — decrements as user types) --}}
                                        <td class="px-3 py-3 text-center bg-green-50">
                                            <span class="font-bold text-lg"
                                                :class="isOverLimit(cls) ? 'text-red-600' : (shiftAvail(cls) === 0 ?
                                                    'text-red-500' : (shiftAvail(cls) <= 3 ? 'text-amber-500' :
                                                        'text-green-600'))"
                                                x-text="shiftAvail(cls)"></span>
                                            <span x-show="isOverLimit(cls)"
                                                class="block text-xs font-semibold text-red-500 leading-none mt-0.5">over
                                                limit</span>
                                        </td>

                                        {{-- Regular Boys --}}
                                        <td class="px-2 py-2 bg-blue-50">
                                            @if (!$anyLocked)
                                                <div>
                                                    <input x-show="activeShift==='morning'" type="number"
                                                        x-model.number="cls.morning_boys"
                                                        @input="cls.morning_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                        :disabled="isFull(cls)" min="0" max="9999"
                                                        :class="isOverLimit(cls) ? 'border-red-400 ring-2 ring-red-200' :
                                                            'border-blue-200 focus:ring-blue-400'"
                                                        class="w-16 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 border disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @if ($hasEvening)
                                                        <input x-show="activeShift==='evening'" type="number"
                                                            x-model.number="cls.evening_boys"
                                                            @input="cls.evening_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                            :disabled="isFull(cls)" min="0" max="9999"
                                                            :class="isOverLimit(cls) ? 'border-red-400 ring-2 ring-red-200' :
                                                                'border-indigo-200 focus:ring-indigo-400'"
                                                            class="w-16 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 border disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @endif
                                                </div>
                                            @else
                                                <span class="font-bold"
                                                    :class="activeShift === 'morning' ? 'text-blue-700' : 'text-indigo-700'"
                                                    x-text="activeShift==='morning' ? cls.morning_boys : cls.evening_boys"></span>
                                            @endif
                                        </td>

                                        {{-- Regular Girls --}}
                                        <td class="px-2 py-2 bg-blue-50">
                                            @if (!$anyLocked)
                                                <div>
                                                    <input x-show="activeShift==='morning'" type="number"
                                                        x-model.number="cls.morning_girls"
                                                        @input="cls.morning_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                        :disabled="isFull(cls)" min="0" max="9999"
                                                        :class="isOverLimit(cls) ? 'border-red-400 ring-2 ring-red-200' :
                                                            'border-pink-200 focus:ring-pink-400'"
                                                        class="w-16 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 border disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @if ($hasEvening)
                                                        <input x-show="activeShift==='evening'" type="number"
                                                            x-model.number="cls.evening_girls"
                                                            @input="cls.evening_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                            :disabled="isFull(cls)" min="0" max="9999"
                                                            :class="isOverLimit(cls) ? 'border-red-400 ring-2 ring-red-200' :
                                                                'border-pink-200 focus:ring-pink-400'"
                                                            class="w-16 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 border disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @endif
                                                </div>
                                            @else
                                                <span class="font-bold text-pink-700"
                                                    x-text="activeShift==='morning' ? cls.morning_girls : cls.evening_girls"></span>
                                            @endif
                                        </td>

                                        {{-- OOSC Boys --}}
                                        <td class="px-2 py-2 bg-purple-50">
                                            @if (!$anyLocked)
                                                <div>
                                                    <input x-show="activeShift==='morning'" type="number"
                                                        x-model.number="cls.morning_oosc_boys"
                                                        @input="cls.morning_oosc_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                        :disabled="isFull(cls)" min="0" max="9999"
                                                        class="w-16 border border-purple-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @if ($hasEvening)
                                                        <input x-show="activeShift==='evening'" type="number"
                                                            x-model.number="cls.evening_oosc_boys"
                                                            @input="cls.evening_oosc_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                            :disabled="isFull(cls)" min="0" max="9999"
                                                            class="w-16 border border-purple-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @endif
                                                </div>
                                            @else
                                                <span class="font-bold text-purple-700"
                                                    x-text="activeShift==='morning' ? cls.morning_oosc_boys : cls.evening_oosc_boys"></span>
                                            @endif
                                        </td>

                                        {{-- OOSC Girls --}}
                                        <td class="px-2 py-2 bg-purple-50">
                                            @if (!$anyLocked)
                                                <div>
                                                    <input x-show="activeShift==='morning'" type="number"
                                                        x-model.number="cls.morning_oosc_girls"
                                                        @input="cls.morning_oosc_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                        :disabled="isFull(cls)" min="0" max="9999"
                                                        class="w-16 border border-purple-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @if ($hasEvening)
                                                        <input x-show="activeShift==='evening'" type="number"
                                                            x-model.number="cls.evening_oosc_girls"
                                                            @input="cls.evening_oosc_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                            :disabled="isFull(cls)" min="0" max="9999"
                                                            class="w-16 border border-purple-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @endif
                                                </div>
                                            @else
                                                <span class="font-bold text-purple-600"
                                                    x-text="activeShift==='morning' ? cls.morning_oosc_girls : cls.evening_oosc_girls"></span>
                                            @endif
                                        </td>

                                        {{-- P2G Boys --}}
                                        <td class="px-2 py-2 bg-orange-50">
                                            @if (!$anyLocked)
                                                <div>
                                                    <input x-show="activeShift==='morning'" type="number"
                                                        x-model.number="cls.morning_p2p_boys"
                                                        @input="cls.morning_p2p_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                        :disabled="isFull(cls)" min="0" max="9999"
                                                        class="w-16 border border-orange-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @if ($hasEvening)
                                                        <input x-show="activeShift==='evening'" type="number"
                                                            x-model.number="cls.evening_p2p_boys"
                                                            @input="cls.evening_p2p_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                            :disabled="isFull(cls)" min="0" max="9999"
                                                            class="w-16 border border-orange-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @endif
                                                </div>
                                            @else
                                                <span class="font-bold text-orange-700"
                                                    x-text="activeShift==='morning' ? cls.morning_p2p_boys : cls.evening_p2p_boys"></span>
                                            @endif
                                        </td>

                                        {{-- P2G Girls --}}
                                        <td class="px-2 py-2 bg-orange-50">
                                            @if (!$anyLocked)
                                                <div>
                                                    <input x-show="activeShift==='morning'" type="number"
                                                        x-model.number="cls.morning_p2p_girls"
                                                        @input="cls.morning_p2p_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                        :disabled="isFull(cls)" min="0" max="9999"
                                                        class="w-16 border border-orange-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @if ($hasEvening)
                                                        <input x-show="activeShift==='evening'" type="number"
                                                            x-model.number="cls.evening_p2p_girls"
                                                            @input="cls.evening_p2p_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                            :disabled="isFull(cls)" min="0" max="9999"
                                                            class="w-16 border border-orange-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100">
                                                    @endif
                                                </div>
                                            @else
                                                <span class="font-bold text-orange-600"
                                                    x-text="activeShift==='morning' ? cls.morning_p2p_girls : cls.evening_p2p_girls"></span>
                                            @endif
                                        </td>

                                        {{-- Today's row total (shift-aware) --}}
                                        <td class="px-3 py-3 text-center bg-blue-100">
                                            <span class="font-bold text-blue-900" x-text="shiftRowTotal(cls)"></span>
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-3 py-3 text-center">
                                            <template x-if="cls.status_label">
                                                <span class="text-xs px-2 py-0.5 rounded-full" :class="cls.badge_class"
                                                    x-text="cls.status_label"></span>
                                            </template>
                                            <template x-if="!cls.status_label">
                                                <span class="text-xs text-gray-400">—</span>
                                            </template>
                                        </td>

                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="text-white font-bold text-sm"
                                    :class="activeShift === 'morning' ? 'bg-blue-900' : 'bg-indigo-900'">
                                    <td class="px-4 py-3" colspan="2">TOTAL</td>
                                    <td class="px-3 py-3 text-center bg-orange-800">
                                        <span
                                            x-text="{{ $hasEvening
                                                ? "(activeShift==='morning' ? (rows||[]).reduce((s,c)=>s+(c.morning_existing||0),0) : (rows||[]).reduce((s,c)=>s+(c.evening_existing||0),0)).toLocaleString()"
                                                : '(rows||[]).reduce((s,c)=>s+c.existing,0).toLocaleString()' }}"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span
                                            x-text="{{ $hasEvening
                                                ? "(activeShift==='morning' ? (rows||[]).reduce((s,c)=>s+(c.morning_seats||0),0) : (rows||[]).reduce((s,c)=>s+(c.evening_seats||0),0)).toLocaleString()"
                                                : '(rows||[]).reduce((s,c)=>s+(c.total_seats||0),0).toLocaleString()' }}"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+shiftAvail(c),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span
                                            x-text="activeShift==='morning' ? (rows||[]).reduce((s,c)=>s+(c.morning_boys||0),0) : (rows||[]).reduce((s,c)=>s+(c.evening_boys||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span
                                            x-text="activeShift==='morning' ? (rows||[]).reduce((s,c)=>s+(c.morning_girls||0),0) : (rows||[]).reduce((s,c)=>s+(c.evening_girls||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center bg-purple-800">
                                        <span
                                            x-text="activeShift==='morning' ? (rows||[]).reduce((s,c)=>s+(c.morning_oosc_boys||0),0) : (rows||[]).reduce((s,c)=>s+(c.evening_oosc_boys||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center bg-purple-800">
                                        <span
                                            x-text="activeShift==='morning' ? (rows||[]).reduce((s,c)=>s+(c.morning_oosc_girls||0),0) : (rows||[]).reduce((s,c)=>s+(c.evening_oosc_girls||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center bg-orange-800">
                                        <span
                                            x-text="activeShift==='morning' ? (rows||[]).reduce((s,c)=>s+(c.morning_p2p_boys||0),0) : (rows||[]).reduce((s,c)=>s+(c.evening_p2p_boys||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center bg-orange-800">
                                        <span
                                            x-text="activeShift==='morning' ? (rows||[]).reduce((s,c)=>s+(c.morning_p2p_girls||0),0) : (rows||[]).reduce((s,c)=>s+(c.evening_p2p_girls||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+shiftRowTotal(c),0)"></span>
                                    </td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>


                {{-- ════════════════════════════════════════════════════
                     TABLE 2 — EVENING SHIFT — MERGED INTO UNIFIED TABLE ABOVE
                     Evening inputs are toggled via activeShift Alpine variable.
                     The @if ($hasEvening) block below is kept for reference only.
                ════════════════════════════════════════════════════ --}}
                @if (false)
                    {{-- TABLE 2 merged into unified table; kept for reference --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-5">
                        <div class="px-5 py-3 bg-indigo-900 flex justify-between items-center">
                            <span class="text-white font-bold text-sm">🌆 Evening Shift — Enrollment Data</span>
                            <span class="text-indigo-200 text-xs hidden sm:inline">Evening admissions share the same seat
                                pool as morning</span>
                        </div>
                        <p class="block sm:hidden text-xs text-gray-400 mb-2 px-4 pt-2 flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Swipe right to see all columns
                        </p>
                        <div class="overflow-x-auto -mx-4 sm:mx-0">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b-2 border-gray-100">
                                        <th
                                            class="sticky left-0 z-20 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-28 border-r border-gray-200">
                                            Class</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-16">
                                            Sec.</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-orange-600 uppercase bg-orange-50">
                                            Existing<br>Enroll.</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50">
                                            Total<br>Seats</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-green-600 uppercase bg-green-50">
                                            Seats<br>Avail.</th>
                                        <th colspan="2"
                                            class="px-3 py-3 text-center text-xs font-semibold text-indigo-700 uppercase bg-indigo-50">
                                            Regular<br><span class="normal-case font-normal text-gray-400">New
                                                Admitted</span>
                                        </th>
                                        <th colspan="2"
                                            class="px-3 py-3 text-center text-xs font-semibold text-purple-700 uppercase bg-purple-50">
                                            OOSC<br><span
                                                class="normal-case font-normal text-gray-400">Out-of-School</span>
                                        </th>
                                        <th colspan="2"
                                            class="px-3 py-3 text-center text-xs font-semibold text-orange-700 uppercase bg-orange-50">
                                            P2G<br><span class="normal-case font-normal text-gray-400">Private to
                                                Government</span>
                                        </th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-indigo-900 uppercase bg-indigo-100">
                                            Today's<br>Total</th>
                                    </tr>
                                    <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-400">
                                        <th class="sticky left-0 z-20 px-4 py-1 bg-gray-50 border-r border-gray-200"></th>
                                        <th class="px-3 py-1 bg-gray-50"></th>
                                        <th class="px-3 py-1 bg-orange-50"></th>
                                        <th class="px-3 py-1 bg-gray-50"></th>
                                        <th class="px-3 py-1 bg-green-50"></th>
                                        <th class="px-3 py-1 text-center text-blue-500 bg-indigo-50">Boys</th>
                                        <th class="px-3 py-1 text-center text-pink-500 bg-indigo-50">Girls</th>
                                        <th class="px-3 py-1 text-center text-blue-500 bg-purple-50">Boys</th>
                                        <th class="px-3 py-1 text-center text-pink-500 bg-purple-50">Girls</th>
                                        <th class="px-3 py-1 text-center text-blue-500 bg-orange-50">Boys</th>
                                        <th class="px-3 py-1 text-center text-pink-500 bg-orange-50">Girls</th>
                                        <th class="px-3 py-1 bg-indigo-100"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(cls, i) in rows" :key="'eve-' + cls.class_id">
                                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors"
                                            :class="isFull(cls) ?
                                                'bg-gray-100 opacity-60 pointer-events-none select-none' : (isOverLimit(
                                                    cls) ? 'bg-red-50' : '')">

                                            <td class="sticky left-0 z-10 px-3 py-2.5 border-r border-gray-200 min-w-[90px] shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)]"
                                                :class="isFull(cls) ? 'bg-gray-100' : (isOverLimit(cls) ? 'bg-red-50' :
                                                    'bg-white')">
                                                <div class="font-semibold text-gray-800 text-sm" x-text="cls.class_name">
                                                </div>
                                                <span x-show="isFull(cls)"
                                                    class="text-xs bg-gray-500 text-white px-2 py-0.5 rounded-full font-semibold mt-0.5 block w-fit">
                                                    🚫 Full
                                                </span>
                                                <span x-show="isOverLimit(cls) && !isFull(cls)"
                                                    class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold mt-0.5 block w-fit">
                                                    ⚠️ Over limit
                                                </span>
                                            </td>
                                            <td class="px-3 py-3 text-center text-gray-600 font-medium"
                                                x-text="cls.sections"></td>
                                            <td class="px-3 py-3 text-center bg-orange-50">
                                                <span class="font-semibold text-orange-700"
                                                    x-text="cls.existing.toLocaleString()"></span>
                                            </td>
                                            <td class="px-3 py-3 text-center font-medium text-gray-700"
                                                x-text="cls.total_seats.toLocaleString()"></td>
                                            <td class="px-3 py-3 text-center bg-green-50">
                                                <span class="font-bold text-lg"
                                                    :class="cls.available > 0 ? 'text-green-600' : 'text-red-500'"
                                                    x-text="cls.available"></span>
                                            </td>

                                            {{-- Regular Boys --}}
                                            <td class="px-2 py-2 bg-indigo-50">
                                                <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <input type="number" x-model.number="cls.evening_boys"
                                                        @input="cls.evening_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                        min="0" max="9999"
                                                        :class="isOverLimit(cls) ? 'border-red-400 ring-2 ring-red-200' :
                                                            'border-indigo-200 focus:ring-indigo-400'"
                                                        class="w-16 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 border">
                                                </template>
                                                <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <span class="font-bold text-indigo-700"
                                                        x-text="cls.evening_boys"></span>
                                                </template>
                                            </td>

                                            {{-- Regular Girls --}}
                                            <td class="px-2 py-2 bg-indigo-50">
                                                <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <input type="number" x-model.number="cls.evening_girls"
                                                        @input="cls.evening_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                        min="0" max="9999"
                                                        :class="isOverLimit(cls) ? 'border-red-400 ring-2 ring-red-200' :
                                                            'border-pink-200 focus:ring-pink-400'"
                                                        class="w-16 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 border">
                                                </template>
                                                <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <span class="font-bold text-pink-700"
                                                        x-text="cls.evening_girls"></span>
                                                </template>
                                            </td>

                                            {{-- OOSC Boys --}}
                                            <td class="px-2 py-2 bg-purple-50">
                                                <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <input type="number" x-model.number="cls.evening_oosc_boys"
                                                        @input="cls.evening_oosc_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                        min="0" max="9999"
                                                        class="w-16 border border-purple-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-400">
                                                </template>
                                                <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <span class="font-bold text-purple-700"
                                                        x-text="cls.evening_oosc_boys"></span>
                                                </template>
                                            </td>

                                            {{-- OOSC Girls --}}
                                            <td class="px-2 py-2 bg-purple-50">
                                                <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <input type="number" x-model.number="cls.evening_oosc_girls"
                                                        @input="cls.evening_oosc_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                        min="0" max="9999"
                                                        class="w-16 border border-purple-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-400">
                                                </template>
                                                <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <span class="font-bold text-purple-600"
                                                        x-text="cls.evening_oosc_girls"></span>
                                                </template>
                                            </td>

                                            {{-- P2G Boys --}}
                                            <td class="px-2 py-2 bg-orange-50">
                                                <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <input type="number" x-model.number="cls.evening_p2p_boys"
                                                        @input="cls.evening_p2p_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                        min="0" max="9999"
                                                        class="w-16 border border-orange-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-400">
                                                </template>
                                                <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <span class="font-bold text-orange-700"
                                                        x-text="cls.evening_p2p_boys"></span>
                                                </template>
                                            </td>

                                            {{-- P2G Girls --}}
                                            <td class="px-2 py-2 bg-orange-50">
                                                <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <input type="number" x-model.number="cls.evening_p2p_girls"
                                                        @input="cls.evening_p2p_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                        min="0" max="9999"
                                                        class="w-16 border border-orange-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-400">
                                                </template>
                                                <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                    <span class="font-bold text-orange-600"
                                                        x-text="cls.evening_p2p_girls"></span>
                                                </template>
                                            </td>

                                            {{-- Row total --}}
                                            <td class="px-3 py-3 text-center bg-indigo-100">
                                                <span class="font-bold text-indigo-900"
                                                    x-text="(cls.evening_boys||0)+(cls.evening_girls||0)+(cls.evening_oosc_boys||0)+(cls.evening_oosc_girls||0)+(cls.evening_p2p_boys||0)+(cls.evening_p2p_girls||0)">
                                                </span>
                                            </td>

                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-indigo-900 text-white font-bold text-sm">
                                        <td class="px-4 py-3" colspan="2">TOTAL</td>
                                        <td class="px-3 py-3 text-center bg-orange-800">
                                            <span
                                                x-text="(rows||[]).reduce((s,c)=>s+c.existing,0).toLocaleString()"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span
                                                x-text="(rows||[]).reduce((s,c)=>s+c.total_seats,0).toLocaleString()"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span x-text="(rows||[]).reduce((s,c)=>s+c.available,0)"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span x-text="(rows||[]).reduce((s,c)=>s+(c.evening_boys||0),0)"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span x-text="(rows||[]).reduce((s,c)=>s+(c.evening_girls||0),0)"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center bg-purple-800">
                                            <span x-text="(rows||[]).reduce((s,c)=>s+(c.evening_oosc_boys||0),0)"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center bg-purple-800">
                                            <span x-text="(rows||[]).reduce((s,c)=>s+(c.evening_oosc_girls||0),0)"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center bg-orange-800">
                                            <span x-text="(rows||[]).reduce((s,c)=>s+(c.evening_p2p_boys||0),0)"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center bg-orange-800">
                                            <span x-text="(rows||[]).reduce((s,c)=>s+(c.evening_p2p_girls||0),0)"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span
                                                x-text="(rows||[]).reduce((s,c)=>s+(c.evening_boys||0)+(c.evening_girls||0)+(c.evening_oosc_boys||0)+(c.evening_oosc_girls||0)+(c.evening_p2p_boys||0)+(c.evening_p2p_girls||0),0)"></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif


                {{-- ════════════════════════════════════════════════════
                     TABLE 3 — MATRIC TECH (only if institution has_matric_tech)
                ════════════════════════════════════════════════════ --}}
                @if ($hasMatricTech)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-5">
                        <div class="px-5 py-3 bg-teal-800">
                            <div class="flex justify-between items-center">
                                <span class="text-white font-bold text-sm">⚙️ Matric Tech Program — Today's
                                    Enrolment</span>
                                <span class="text-teal-200 text-xs hidden sm:inline">Only classes with available seats are
                                    shown</span>
                            </div>
                            <p class="text-teal-200 text-xs mt-1">
                                Enter Matric Tech students for Class 9 &amp; 10. Entering a count here automatically records
                                it as today's admission for that class. Classes with no available seats are hidden.
                            </p>
                        </div>
                        <p class="block sm:hidden text-xs text-gray-400 mb-2 px-4 pt-2 flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Swipe right to see all columns
                        </p>
                        <div class="overflow-x-auto -mx-4 sm:mx-0">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b-2 border-gray-100">
                                        <th
                                            class="sticky left-0 z-20 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-28 border-r border-gray-200">
                                            Class</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50">
                                            Available Seats</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50">
                                            Today's Admitted</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-teal-700 uppercase bg-teal-50">
                                            Matric Tech Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- "All full" message when no Class 9/10 has available seats --}}
                                    <template
                                        x-if="(rows||[]).filter(r => (r.class_order === 9 || r.class_order === 10) && r.available > 0).length === 0">
                                        <tr>
                                            <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-400">
                                                🚫 Class 9 and Class 10 are both full — no Matric Tech seats available.
                                            </td>
                                        </tr>
                                    </template>

                                    {{-- Only show rows where available > 0 --}}
                                    <template
                                        x-for="(cls, i) in rows.filter(r => (r.class_order === 9 || r.class_order === 10) && r.available > 0)"
                                        :key="'mt-' + cls.class_id">
                                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">

                                            {{-- Class name --}}
                                            <td class="px-4 py-3 font-semibold text-gray-800" x-text="cls.class_name">
                                            </td>

                                            {{-- Available seats --}}
                                            <td class="px-3 py-3 text-center">
                                                <span class="font-bold text-green-600" x-text="cls.available"></span>
                                            </td>

                                            {{-- Today's admitted (rowTotal) --}}
                                            <td class="px-3 py-3 text-center">
                                                <span
                                                    :class="rowTotal(cls) > 0 ? 'font-bold text-blue-700' :
                                                        'text-gray-400 italic text-xs'"
                                                    x-text="rowTotal(cls) > 0 ? rowTotal(cls) : 'None yet'"></span>
                                            </td>

                                            {{-- Matric Tech input --}}
                                            <td class="px-2 py-2 bg-teal-50 text-center">
                                                @if (!$anyLocked)
                                                    {{-- Step 1: no admission entered yet → block with warning --}}
                                                    <template x-if="rowTotal(cls) === 0">
                                                        <div
                                                            class="text-xs text-amber-700 font-medium px-2 py-1 bg-amber-50 border border-amber-200 rounded-lg">
                                                            ⚠️ Enter daily admission for <span
                                                                x-text="cls.class_name"></span> first
                                                        </div>
                                                    </template>

                                                    {{-- Step 2: admission entered → show input capped at rowTotal --}}
                                                    <template x-if="rowTotal(cls) > 0">
                                                        <div>
                                                            <input type="number" x-model.number="cls.matric_tech_count"
                                                                @input="cls.matric_tech_count = Math.min(Math.max(0, parseInt($event.target.value)||0), rowTotal(cls))"
                                                                min="0" :max="rowTotal(cls)"
                                                                :class="cls.matric_tech_count > rowTotal(cls) ?
                                                                    'border-red-400 ring-2 ring-red-200' :
                                                                    'border-teal-200 focus:ring-teal-400'"
                                                                class="w-20 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 border">
                                                            <p class="text-xs text-gray-400 mt-0.5">
                                                                max <span x-text="rowTotal(cls)"></span>
                                                            </p>
                                                        </div>
                                                    </template>
                                                @else
                                                    <span class="font-bold text-teal-700"
                                                        x-text="cls.matric_tech_count"></span>
                                                @endif
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-teal-800 text-white font-bold text-sm">
                                        <td class="px-4 py-3">TOTAL</td>
                                        <td class="px-3 py-3 text-center">
                                            <span
                                                x-text="(rows||[]).filter(r => r.class_order === 9 || r.class_order === 10).reduce((s,c)=>s+c.available,0)"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span
                                                x-text="(rows||[]).filter(r => r.class_order === 9 || r.class_order === 10).reduce((s,c)=>s+rowTotal(c),0)"></span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span
                                                x-text="(rows||[]).filter(r => r.class_order === 9 || r.class_order === 10).reduce((s,c)=>s+(c.matric_tech_count||0),0)"></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif


                {{-- ── Action Buttons ──────────────────────────────────── --}}
                @if (!$anyLocked && $admissionStatus !== 'closed')
                    {{-- Live totals pill — desktop only (mobile sees live ∑ in sticky class column) --}}
                    <div
                        class="hidden sm:flex items-center gap-3 mt-2 bg-blue-50 border border-blue-100 rounded-xl px-4 py-2 text-sm">
                        <span class="text-gray-500">Today's Total:</span>
                        <span>
                            <span class="font-bold text-blue-900"
                                x-text="(rows||[]).reduce((s,c)=>s+(c.morning_boys||0)+(c.morning_girls||0)+(c.evening_boys||0)+(c.evening_girls||0),0)">
                            </span>
                            <span class="text-gray-400 text-xs ml-1">regular</span>
                        </span>
                        <span>
                            <span class="font-bold text-purple-700"
                                x-text="(rows||[]).reduce((s,c)=>s+(c.morning_oosc_boys||0)+(c.morning_oosc_girls||0)+(c.evening_oosc_boys||0)+(c.evening_oosc_girls||0),0)">
                            </span>
                            <span class="text-gray-400 text-xs ml-1">OOSC</span>
                        </span>
                        <span>
                            <span class="font-bold text-orange-600"
                                x-text="(rows||[]).reduce((s,c)=>s+(c.morning_p2p_boys||0)+(c.morning_p2p_girls||0)+(c.evening_p2p_boys||0)+(c.evening_p2p_girls||0),0)">
                            </span>
                            <span class="text-gray-400 text-xs ml-1">P2G</span>
                        </span>
                        @if ($hasMatricTech)
                            <span>
                                <span class="font-bold text-teal-700"
                                    x-text="(rows||[]).filter(r=>r.class_order===9||r.class_order===10).reduce((s,c)=>s+(c.matric_tech_count||0),0)">
                                </span>
                                <span class="text-gray-400 text-xs ml-1">Matric Tech</span>
                            </span>
                        @endif
                    </div>
                    {{-- Sticky bottom action bar (mobile); static row (desktop) --}}
                    <div
                        class="sticky bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-200 -mx-4 px-4 py-3
                                sm:static sm:bg-transparent sm:border-0 sm:-mx-0 sm:px-0 sm:mt-2">
                        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2 sm:gap-3">
                            @can('admission.create')
                                <button type="button" @click="submitForm('draft')"
                                    class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-semibold bg-gray-100 text-gray-700
                                           hover:bg-gray-200 transition border border-gray-200 order-2 sm:order-1">
                                    💾 Save Draft
                                </button>
                            @endcan
                            @can('admission.submit')
                                <button type="button" @click="submitForm('submit')"
                                    class="w-full sm:w-auto px-8 py-3 rounded-xl text-sm font-bold bg-blue-900 text-white
                                           hover:bg-blue-800 transition shadow-sm order-1 sm:order-2">
                                    @if ($admissionStatus === 'by_approval')
                                        ⏳ Submit for Approval
                                    @else
                                        ✅ Submit &amp; Finalise
                                    @endif
                                </button>
                            @endcan
                            <a href="{{ route('dashboard') }}"
                                class="hidden sm:inline-flex px-5 py-3 text-sm font-medium text-gray-400
                                      hover:text-gray-600 transition order-3">
                                Cancel
                            </a>
                        </div>
                    </div>
                @elseif($anyLocked)
                    <div
                        class="bg-red-50 border border-red-100 rounded-xl px-5 py-4 text-sm text-red-700 flex items-center gap-2">
                        🔒 Admissions for <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
                        have been <strong>locked by the FDE Cell</strong>. Contact the <strong>FDE Cell</strong> for
                        corrections.
                    </div>
                @elseif($admissionStatus === 'closed')
                    <div
                        class="bg-red-50 border border-red-100 rounded-xl px-5 py-4 text-sm text-red-700 flex items-center gap-2">
                        🚫 <strong>Admissions Closed.</strong> The FDE Cell has closed admissions for your school.
                    </div>
                @endif

            </form>
        @endif

    </div>

@endsection

@push('scripts')
    <script>
        var admissionData = admissionData || [];

        function dailyAdmission() {
            return {
                rows: (window.admissionData || []).map(cls => ({
                    ...cls,
                    morning_boys: parseInt(cls.morning_boys) || 0,
                    morning_girls: parseInt(cls.morning_girls) || 0,
                    evening_boys: parseInt(cls.evening_boys) || 0,
                    evening_girls: parseInt(cls.evening_girls) || 0,
                    morning_oosc_boys: parseInt(cls.morning_oosc_boys) || 0,
                    morning_oosc_girls: parseInt(cls.morning_oosc_girls) || 0,
                    morning_p2p_boys: parseInt(cls.morning_p2p_boys) || 0,
                    morning_p2p_girls: parseInt(cls.morning_p2p_girls) || 0,
                    evening_oosc_boys: parseInt(cls.evening_oosc_boys) || 0,
                    evening_oosc_girls: parseInt(cls.evening_oosc_girls) || 0,
                    evening_p2p_boys: parseInt(cls.evening_p2p_boys) || 0,
                    evening_p2p_girls: parseInt(cls.evening_p2p_girls) || 0,
                    matric_tech_count: parseInt(cls.matric_tech_count) || 0,
                    // Per-shift capacity fields (always present; 0 for non-evening schools)
                    morning_seats: parseInt(cls.morning_seats) || 0,
                    evening_seats: parseInt(cls.evening_seats) || 0,
                    morning_existing: parseInt(cls.morning_existing) || 0,
                    evening_existing: parseInt(cls.evening_existing) || 0,
                    morning_available: parseInt(cls.morning_available) || 0,
                    evening_available: parseInt(cls.evening_available) || 0,
                })),
                submitAction: 'draft',
                activeShift: '{{ $defaultShift }}',

                // Total entered today for a row (all 12 fields — used for capacity checks & Matric Tech)
                rowTotal(cls) {
                    return (cls.morning_boys || 0) + (cls.morning_girls || 0) +
                        (cls.evening_boys || 0) + (cls.evening_girls || 0) +
                        (cls.morning_oosc_boys || 0) + (cls.morning_oosc_girls || 0) +
                        (cls.morning_p2p_boys || 0) + (cls.morning_p2p_girls || 0) +
                        (cls.evening_oosc_boys || 0) + (cls.evening_oosc_girls || 0) +
                        (cls.evening_p2p_boys || 0) + (cls.evening_p2p_girls || 0);
                },

                // Shift-specific today's total (for Today's Total column display)
                shiftRowTotal(cls) {
                    if (this.activeShift === 'morning') {
                        return (cls.morning_boys || 0) + (cls.morning_girls || 0) +
                            (cls.morning_oosc_boys || 0) + (cls.morning_oosc_girls || 0) +
                            (cls.morning_p2p_boys || 0) + (cls.morning_p2p_girls || 0);
                    } else {
                        return (cls.evening_boys || 0) + (cls.evening_girls || 0) +
                            (cls.evening_oosc_boys || 0) + (cls.evening_oosc_girls || 0) +
                            (cls.evening_p2p_boys || 0) + (cls.evening_p2p_girls || 0);
                    }
                },

                // Display label for ECE classes with age eligibility
                eceLabel(name) {
                    if (name === 'ECE-I') return 'ECE-I\n(Age 3–4 yrs+)';
                    if (name.includes('ECE-II') || name.includes('Prep')) return 'ECE-II/Prep.\n(Age 4–5 yrs)';
                    return name;
                },

                // Returns the LIVE remaining seats for the active shift.
                // Subtracts ALL today's admissions (regular + OOSC + P2P) so the
                // counter decrements in real-time as the user types any field.
                shiftAvail(cls) {
                    @if ($hasEvening)
                        if (this.activeShift === 'morning') {
                            const morningAll = (cls.morning_boys || 0) + (cls.morning_girls || 0) +
                                (cls.morning_oosc_boys || 0) + (cls.morning_oosc_girls || 0) +
                                (cls.morning_p2p_boys || 0) + (cls.morning_p2p_girls || 0);
                            const trueAvail = Math.max(0, (cls.morning_seats || 0) - (cls.morning_existing || 0) - (cls
                                .cum_morning_prior || 0));
                            return Math.max(0, trueAvail - morningAll);
                        } else {
                            const eveningAll = (cls.evening_boys || 0) + (cls.evening_girls || 0) +
                                (cls.evening_oosc_boys || 0) + (cls.evening_oosc_girls || 0) +
                                (cls.evening_p2p_boys || 0) + (cls.evening_p2p_girls || 0);
                            const trueAvail = Math.max(0, (cls.evening_seats || 0) - (cls.evening_existing || 0) - (cls
                                .cum_evening_prior || 0));
                            return Math.max(0, trueAvail - eveningAll);
                        }
                    @else
                        // Non-evening (includes ECE): all types consume seats
                        const allTypes = (cls.morning_boys || 0) + (cls.morning_girls || 0) +
                            (cls.morning_oosc_boys || 0) + (cls.morning_oosc_girls || 0) +
                            (cls.morning_p2p_boys || 0) + (cls.morning_p2p_girls || 0);
                        const trueAvail = Math.max(0, (cls.total_seats || 0) - (cls.existing || 0) - (cls.cum_prior || 0));
                        return Math.max(0, trueAvail - allTypes);
                    @endif
                },

                // True when today's entries (regular + OOSC + P2P) exceed available seats
                // for the active shift. All three types consume seats.
                // Uses cum_prior (from server) so isOverLimit is correct even if cls.existing is changed.
                isOverLimit(cls) {
                    @if ($hasEvening)
                        const morningAll = (cls.morning_boys || 0) + (cls.morning_girls || 0) +
                            (cls.morning_oosc_boys || 0) + (cls.morning_oosc_girls || 0) +
                            (cls.morning_p2p_boys || 0) + (cls.morning_p2p_girls || 0);
                        const eveningAll = (cls.evening_boys || 0) + (cls.evening_girls || 0) +
                            (cls.evening_oosc_boys || 0) + (cls.evening_oosc_girls || 0) +
                            (cls.evening_p2p_boys || 0) + (cls.evening_p2p_girls || 0);
                        if (this.activeShift === 'morning') {
                            const trueAvail = Math.max(0, (cls.morning_seats || 0) - (cls.morning_existing || 0) - (cls
                                .cum_morning_prior || 0));
                            return cls.morning_seats > 0 && morningAll > trueAvail;
                        }
                        const trueAvail = Math.max(0, (cls.evening_seats || 0) - (cls.evening_existing || 0) - (cls
                            .cum_evening_prior || 0));
                        return cls.evening_seats > 0 && eveningAll > trueAvail;
                    @else
                        // All types consume seats in non-evening schools
                        const allTypes = (cls.morning_boys || 0) + (cls.morning_girls || 0) +
                            (cls.morning_oosc_boys || 0) + (cls.morning_oosc_girls || 0) +
                            (cls.morning_p2p_boys || 0) + (cls.morning_p2p_girls || 0);
                        const trueAvail = Math.max(0, (cls.total_seats || 0) - (cls.existing || 0) - (cls.cum_prior || 0));
                        return cls.total_seats > 0 && allTypes > trueAvail;
                    @endif
                },

                // True when the active shift's seats were never configured (stored as 0)
                isNotConfigured(cls) {
                    @if ($hasEvening)
                        return this.activeShift === 'morning' ?
                            cls.morning_seats === 0 :
                            cls.evening_seats === 0;
                    @else
                        return cls.total_seats === 0;
                    @endif
                },

                // True when configured but genuinely no seats left for the active shift
                // BEFORE today's input (used to grey-out / lock the row entirely).
                isFull(cls) {
                    @if ($hasEvening)
                        if (this.activeShift === 'morning') {
                            const trueAvail = Math.max(0, (cls.morning_seats || 0) - (cls.morning_existing || 0) - (cls
                                .cum_morning_prior || 0));
                            return cls.morning_seats > 0 && trueAvail <= 0;
                        }
                        const trueAvail = Math.max(0, (cls.evening_seats || 0) - (cls.evening_existing || 0) - (cls
                            .cum_evening_prior || 0));
                        return cls.evening_seats > 0 && trueAvail <= 0;
                    @else
                        const trueAvail = Math.max(0, (cls.total_seats || 0) - (cls.existing || 0) - (cls.cum_prior || 0));
                        return cls.total_seats > 0 && trueAvail <= 0;
                    @endif
                },

                submitForm(action) {
                    this.submitAction = action;

                    // Hard block — overlimit classes cannot be submitted or drafted
                    const overLimit = (this.rows || []).filter(c => this.isOverLimit(c));
                    if (overLimit.length > 0) {
                        const names = overLimit.map(c => c.class_name).join('\n  • ');
                        alert(
                            '❌ Cannot save — ' + overLimit.length + ' class(es) exceed available seats:\n\n' +
                            '  • ' + names + '\n\n' +
                            'Please reduce the numbers to within the available seats before saving.'
                        );
                        return; // hard stop — no bypass allowed
                    }

                    if (action === 'submit') {
                        if (!confirm('Are you sure you want to submit today\'s admissions?')) return;
                    }
                    document.getElementById('admissionFormEl').submit();
                }
            };
        }
    </script>
@endpush
