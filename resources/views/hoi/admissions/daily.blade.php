{{-- SAVE AS: resources/views/hoi/admissions/daily.blade.php --}}
@extends('layouts.app')
@section('title', 'Daily Admissions — ' . \Carbon\Carbon::parse($today)->format('d M Y'))

@section('content')

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
                    {{ \Carbon\Carbon::parse($today)->format('l, d M Y') }}
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

            @if ($isPastCutoff)
                <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                    ⏰ Entry Window Closed
                </span>
            @endif

            <a href="{{ route('hoi.admissions.report') }}"
                class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                📋 View Report
            </a>
        </div>
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
    @if (!$anyVerified && !$anyDraft && !$anySubmitted && !$isPastCutoff)
        <div
            class="bg-blue-50 border border-blue-100 rounded-xl px-5 py-3 mb-5 text-sm text-blue-700 flex items-start gap-2">
            <span class="mt-0.5">ℹ️</span>
            <div>
                <strong>How it works:</strong> Enter today's numbers →
                <strong>Save Draft</strong> to save progress →
                <strong>Submit &amp; Finalise</strong> to lock the record.
                Submit before
                <strong>{{ $academicYear?->daily_cutoff_time ? \Carbon\Carbon::createFromTimeString($academicYear->daily_cutoff_time)->format('g:i A') : '11:59 PM' }}</strong>.
            </div>
        </div>
    @endif
    @if ($anyVerified)
        <div
            class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 mb-5 text-sm text-green-800 flex items-center gap-2">
            ✅ Today's admissions have been <strong>verified and finalised</strong>. Contact <strong>FDE Cell</strong> for
            corrections.
        </div>
    @endif
    @if ($isPastCutoff && !$anyVerified)
        <div
            class="bg-orange-50 border border-orange-200 rounded-xl px-5 py-3 mb-5 text-sm text-orange-800 flex items-center gap-2">
            ⏰ Entry window closed. Data shown in <strong>read-only</strong> mode. Contact <strong>FDE Cell</strong> to
            correct.
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
                    </div>
                </template>


                {{-- ════════════════════════════════════════════════════
                     TABLE 1 — MORNING SHIFT
                ════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-5">
                    <div class="px-5 py-3 bg-blue-900 flex justify-between items-center">
                        <span class="text-white font-bold text-sm">🌅 Morning Shift — Enrollment Data</span>
                        <span class="text-blue-200 text-xs">Seats Available = Intake Capacity − Existing − Previous
                            Verified</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-gray-100">
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-28">
                                        Class</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-16">
                                        Sec.</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-orange-600 uppercase bg-orange-50">
                                        Existing<br>Enroll.</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50">
                                        Intake<br>Cap.</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-green-600 uppercase bg-green-50">
                                        Seats<br>Avail.</th>
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
                                    {{-- P2P --}}
                                    <th colspan="2"
                                        class="px-3 py-3 text-center text-xs font-semibold text-orange-700 uppercase bg-orange-50">
                                        P2P<br><span class="normal-case font-normal text-gray-400">Private→Public</span>
                                    </th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-blue-900 uppercase bg-blue-100">
                                        Today's<br>Total</th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-semibold text-gray-400 uppercase bg-gray-50">
                                        Status</th>
                                </tr>
                                <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-400">
                                    <th class="px-4 py-1 bg-gray-50"></th>
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
                                        :class="cls.available <= 0 ? 'bg-gray-100 opacity-60 pointer-events-none select-none' : (isOverLimit(cls) ? 'bg-red-50' : '')">

                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-gray-800" x-text="cls.class_name"></p>
                                            <span x-show="cls.is_ece"
                                                class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">ECE</span>
                                            <span x-show="cls.available <= 0"
                                                class="text-xs bg-gray-500 text-white px-2 py-0.5 rounded-full font-semibold mt-0.5 block w-fit">
                                                🚫 Full
                                            </span>
                                            <span x-show="isOverLimit(cls) && cls.available > 0"
                                                class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold mt-0.5 block w-fit">
                                                ⚠️ Over limit
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center text-gray-600 font-medium" x-text="cls.sections">
                                        </td>
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
                                        <td class="px-2 py-2 bg-blue-50">
                                            <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <input type="number" x-model.number="cls.morning_boys"
                                                    @input="cls.morning_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                    min="0" max="9999"
                                                    :class="isOverLimit(cls) ? 'border-red-400 ring-2 ring-red-200' :
                                                        'border-blue-200 focus:ring-blue-400'"
                                                    class="w-16 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 border">
                                            </template>
                                            <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <span class="font-bold text-blue-700" x-text="cls.morning_boys"></span>
                                            </template>
                                        </td>

                                        {{-- Regular Girls --}}
                                        <td class="px-2 py-2 bg-blue-50">
                                            <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <input type="number" x-model.number="cls.morning_girls"
                                                    @input="cls.morning_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                    min="0" max="9999"
                                                    :class="isOverLimit(cls) ? 'border-red-400 ring-2 ring-red-200' :
                                                        'border-pink-200 focus:ring-pink-400'"
                                                    class="w-16 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 border">
                                            </template>
                                            <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <span class="font-bold text-pink-700" x-text="cls.morning_girls"></span>
                                            </template>
                                        </td>

                                        {{-- OOSC Boys --}}
                                        <td class="px-2 py-2 bg-purple-50">
                                            <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <input type="number" x-model.number="cls.morning_oosc_boys"
                                                    @input="cls.morning_oosc_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                    min="0" max="9999"
                                                    class="w-16 border border-purple-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-400">
                                            </template>
                                            <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <span class="font-bold text-purple-700"
                                                    x-text="cls.morning_oosc_boys"></span>
                                            </template>
                                        </td>

                                        {{-- OOSC Girls --}}
                                        <td class="px-2 py-2 bg-purple-50">
                                            <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <input type="number" x-model.number="cls.morning_oosc_girls"
                                                    @input="cls.morning_oosc_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                    min="0" max="9999"
                                                    class="w-16 border border-purple-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-400">
                                            </template>
                                            <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <span class="font-bold text-purple-600"
                                                    x-text="cls.morning_oosc_girls"></span>
                                            </template>
                                        </td>

                                        {{-- P2P Boys --}}
                                        <td class="px-2 py-2 bg-orange-50">
                                            <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <input type="number" x-model.number="cls.morning_p2p_boys"
                                                    @input="cls.morning_p2p_boys = Math.max(0, parseInt($event.target.value)||0)"
                                                    min="0" max="9999"
                                                    class="w-16 border border-orange-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-400">
                                            </template>
                                            <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <span class="font-bold text-orange-700"
                                                    x-text="cls.morning_p2p_boys"></span>
                                            </template>
                                        </td>

                                        {{-- P2P Girls --}}
                                        <td class="px-2 py-2 bg-orange-50">
                                            <template x-if="!{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <input type="number" x-model.number="cls.morning_p2p_girls"
                                                    @input="cls.morning_p2p_girls = Math.max(0, parseInt($event.target.value)||0)"
                                                    min="0" max="9999"
                                                    class="w-16 border border-orange-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-400">
                                            </template>
                                            <template x-if="{{ $isPastCutoff ? 'true' : 'false' }}">
                                                <span class="font-bold text-orange-600"
                                                    x-text="cls.morning_p2p_girls"></span>
                                            </template>
                                        </td>

                                        {{-- Today's row total --}}
                                        <td class="px-3 py-3 text-center bg-blue-100">
                                            <span class="font-bold text-blue-900"
                                                x-text="(cls.morning_boys||0)+(cls.morning_girls||0)+(cls.morning_oosc_boys||0)+(cls.morning_oosc_girls||0)+(cls.morning_p2p_boys||0)+(cls.morning_p2p_girls||0)">
                                            </span>
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
                                <tr class="bg-blue-900 text-white font-bold text-sm">
                                    <td class="px-4 py-3" colspan="2">TOTAL</td>
                                    <td class="px-3 py-3 text-center bg-orange-800">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+c.existing,0).toLocaleString()"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+c.total_seats,0).toLocaleString()"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+c.available,0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+(c.morning_boys||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+(c.morning_girls||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center bg-purple-800">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+(c.morning_oosc_boys||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center bg-purple-800">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+(c.morning_oosc_girls||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center bg-orange-800">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+(c.morning_p2p_boys||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center bg-orange-800">
                                        <span x-text="(rows||[]).reduce((s,c)=>s+(c.morning_p2p_girls||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span
                                            x-text="(rows||[]).reduce((s,c)=>s+(c.morning_boys||0)+(c.morning_girls||0)+(c.morning_oosc_boys||0)+(c.morning_oosc_girls||0)+(c.morning_p2p_boys||0)+(c.morning_p2p_girls||0),0)"></span>
                                    </td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>


                {{-- ════════════════════════════════════════════════════
                     TABLE 2 — EVENING SHIFT (only for evening/both schools)
                ════════════════════════════════════════════════════ --}}
                @if ($hasEvening)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-5">
                        <div class="px-5 py-3 bg-indigo-900 flex justify-between items-center">
                            <span class="text-white font-bold text-sm">🌆 Evening Shift — Enrollment Data</span>
                            <span class="text-indigo-200 text-xs">Evening admissions share the same seat pool as
                                morning</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b-2 border-gray-100">
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-28">
                                            Class</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50 w-16">
                                            Sec.</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-orange-600 uppercase bg-orange-50">
                                            Existing<br>Enroll.</th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase bg-gray-50">
                                            Intake<br>Cap.</th>
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
                                            P2P<br><span
                                                class="normal-case font-normal text-gray-400">Private→Public</span>
                                        </th>
                                        <th
                                            class="px-3 py-3 text-center text-xs font-semibold text-indigo-900 uppercase bg-indigo-100">
                                            Today's<br>Total</th>
                                    </tr>
                                    <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-400">
                                        <th class="px-4 py-1 bg-gray-50"></th>
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
                                            :class="cls.available <= 0 ? 'bg-gray-100 opacity-60 pointer-events-none select-none' : (isOverLimit(cls) ? 'bg-red-50' : '')">

                                            <td class="px-4 py-3">
                                                <p class="font-semibold text-gray-800" x-text="cls.class_name"></p>
                                                <span x-show="cls.available <= 0"
                                                    class="text-xs bg-gray-500 text-white px-2 py-0.5 rounded-full font-semibold mt-0.5 block w-fit">
                                                    🚫 Full
                                                </span>
                                                <span x-show="isOverLimit(cls) && cls.available > 0"
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

                                            {{-- P2P Boys --}}
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

                                            {{-- P2P Girls --}}
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


                {{-- ── Action Buttons ──────────────────────────────────── --}}
                @if (!$isPastCutoff && !$anyVerified)
                    <div class="flex flex-wrap items-center gap-3 mt-2">

                        @can('admission.create')
                            <button type="button" @click="submitForm('draft')"
                                class="px-6 py-3 rounded-xl text-sm font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition border border-gray-200">
                                💾 Save Draft
                            </button>
                        @endcan

                        @can('admission.submit')
                            <button type="button" @click="submitForm('submit')"
                                class="px-8 py-3 rounded-xl text-sm font-bold bg-blue-900 text-white hover:bg-blue-800 transition shadow-sm">
                                ✅ Submit &amp; Finalise
                            </button>
                        @endcan

                        <a href="{{ route('dashboard') }}"
                            class="px-5 py-3 text-sm font-medium text-gray-400 hover:text-gray-600 transition">
                            Cancel
                        </a>

                        {{-- Live totals pill --}}
                        <div
                            class="ml-auto bg-blue-50 border border-blue-100 rounded-xl px-4 py-2 text-sm flex items-center gap-3">
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
                                <span class="text-gray-400 text-xs ml-1">P2P</span>
                            </span>
                        </div>

                    </div>
                @elseif($anyVerified)
                    <div
                        class="bg-green-50 border border-green-100 rounded-xl px-5 py-4 text-sm text-green-700 flex items-center gap-2">
                        ✅ Today's admissions are <strong>verified and finalised</strong>. For corrections, contact the
                        <strong>FDE Cell</strong>.
                    </div>
                @else
                    <div
                        class="bg-red-50 border border-red-100 rounded-xl px-5 py-4 text-sm text-red-700 flex items-center gap-2">
                        ⏰ <strong>Entry Closed.</strong> Contact the <strong>FDE Cell</strong> if data needs to be entered
                        or corrected.
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
                })),
                submitAction: 'draft',

                // Total entered today for a row (all 12 fields)
                rowTotal(cls) {
                    return (cls.morning_boys || 0) + (cls.morning_girls || 0) +
                        (cls.evening_boys || 0) + (cls.evening_girls || 0) +
                        (cls.morning_oosc_boys || 0) + (cls.morning_oosc_girls || 0) +
                        (cls.morning_p2p_boys || 0) + (cls.morning_p2p_girls || 0) +
                        (cls.evening_oosc_boys || 0) + (cls.evening_oosc_girls || 0) +
                        (cls.evening_p2p_boys || 0) + (cls.evening_p2p_girls || 0);
                },

                // True when today's entries exceed available seats
                isOverLimit(cls) {
                    return this.rowTotal(cls) > cls.available;
                },

                submitForm(action) {
                    this.submitAction = action;
                    if (action === 'submit') {
                        const overLimit = (this.rows || []).filter(c => this.isOverLimit(c));
                        if (overLimit.length > 0) {
                            const names = overLimit.map(c => c.class_name).join(', ');
                            if (!confirm(
                                    overLimit.length + ' class(es) exceed intake capacity: ' + names + '.\n\n' +
                                    'These classes will NOT be saved (hard limit). Others will submit normally.\n\nContinue?'
                                )) return;
                        } else if (!confirm(
                                'Are you sure you want to finalise today\'s admissions? This will lock the records.')) {
                            return;
                        }
                    }
                    document.getElementById('admissionFormEl').submit();
                }
            };
        }
    </script>
@endpush
