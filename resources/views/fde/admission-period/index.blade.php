{{-- SAVE AS: resources/views/fde/admission-period/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Admission Period — FDE Cell')

@section('content')

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Period Management</h2>
            <p class="text-sm text-gray-500 mt-1">Configure admission window dates and daily data entry cut-off time</p>
        </div>
        @if ($activeYear)
            @if ($stats->is_open)
                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-xl text-sm font-semibold">
                    🟢 Window Open
                </span>
            @else
                <span class="px-4 py-2 bg-red-100 text-red-700 rounded-xl text-sm font-semibold">
                    🔴 Window Closed
                </span>
            @endif
        @endif
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">❌ {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $e)
                <p>❌ {{ $e }}</p>
            @endforeach
        </div>
    @endif

    @if (!$activeYear)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-6 text-center text-yellow-800 text-sm">
            <p class="text-2xl mb-2">⚠️</p>
            <p class="font-semibold">No active academic year found.</p>
            <p class="mt-1">
                <a href="{{ route('admin.academic-years.index') }}" class="underline hover:text-yellow-900">
                    Create and activate an academic year first →
                </a>
            </p>
        </div>
    @else
        {{-- ── Live Status Cards ────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
                <p class="text-2xl font-bold {{ $stats->is_open ? 'text-green-600' : 'text-red-500' }}">
                    {{ $stats->is_open ? 'Open' : 'Closed' }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">Window Status</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
                <p class="text-2xl font-bold text-blue-700">
                    {{ $stats->days_elapsed !== null ? max(0, $stats->days_elapsed) : '—' }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">Days Elapsed</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
                <p class="text-2xl font-bold {{ ($stats->days_remaining ?? 0) <= 3 ? 'text-red-500' : 'text-gray-700' }}">
                    {{ $stats->days_remaining !== null ? max(0, $stats->days_remaining) : '—' }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">Days Remaining</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 text-center">
                <p class="text-2xl font-bold text-purple-700">{{ $stats->submitted_today }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Schools Submitted Today</p>
            </div>
        </div>

        {{-- ── Daily Cutoff Status ───────────────────────────────────────── --}}
        @if ($stats->is_open)
            <div
                class="mb-5 px-5 py-3 rounded-xl text-sm font-medium
                {{ $stats->is_cutoff_passed
                    ? 'bg-red-50 border border-red-200 text-red-700'
                    : 'bg-blue-50 border border-blue-200 text-blue-700' }}">
                @if ($stats->is_cutoff_passed)
                    🔒 Daily cut-off has passed for today
                    ({{ \Carbon\Carbon::parse($activeYear->daily_cutoff_time)->format('g:i A') }}).
                    HOIs can no longer submit new entries until tomorrow.
                @else
                    ⏰ Daily cut-off is at
                    <strong>{{ \Carbon\Carbon::parse($activeYear->daily_cutoff_time)->format('g:i A') }}</strong>
                    — HOIs can still submit entries for today.
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Edit Form ────────────────────────────────────────────────── --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-800">
                            Edit Period — <span class="text-blue-700">{{ $activeYear->name }}</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Changes take effect immediately for all HOIs</p>
                    </div>

                    <form method="POST" action="{{ route('fde.admission-period.update', $activeYear) }}">
                        @csrf
                        @method('PUT')

                        <div class="px-5 py-5 space-y-5">

                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                        Admission Start Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="admission_start"
                                        value="{{ old('admission_start', $activeYear->admission_start?->format('Y-m-d')) }}"
                                        required
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <p class="text-xs text-gray-400 mt-1">First day HOIs can submit entries</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                        Admission End Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="admission_end"
                                        value="{{ old('admission_end', $activeYear->admission_end?->format('Y-m-d')) }}"
                                        required
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <p class="text-xs text-gray-400 mt-1">Last day the window stays open</p>
                                </div>
                            </div>

                            <div class="max-w-xs">
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Daily Data Entry Cut-off Time <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="daily_cutoff_time"
                                    value="{{ old(
                                        'daily_cutoff_time',
                                        $activeYear->daily_cutoff_time ? \Carbon\Carbon::parse($activeYear->daily_cutoff_time)->format('H:i') : '14:00',
                                    ) }}"
                                    required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <p class="text-xs text-gray-400 mt-1">
                                    HOIs cannot submit or edit entries after this time each day.
                                    Entries in DRAFT at cut-off are auto-locked.
                                </p>
                            </div>

                            <div class="bg-yellow-50 border border-yellow-100 rounded-lg px-4 py-3 text-xs text-yellow-800">
                                ⚠️ <strong>Warning:</strong> Changing these dates affects all
                                {{ $stats->total_institutions }}
                                institutions immediately. HOIs currently in the middle of data entry will be affected if the
                                window is shortened.
                            </div>

                        </div>

                        <div class="px-5 py-4 border-t border-gray-100 flex justify-end">
                            <button type="submit"
                                onclick="return confirm('Update admission period settings? This affects all schools immediately.')"
                                class="px-6 py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                                💾 Save Period Settings
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── Other Academic Years ─────────────────────────────────── --}}
                @if ($allYears->count() > 1)
                    <div class="mt-5 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="text-sm font-bold text-gray-800">All Academic Years</h3>
                        </div>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Year</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Admission
                                        Window</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cut-off
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Edit
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allYears as $year)
                                    <tr
                                        class="border-b border-gray-50 hover:bg-gray-50 {{ $year->is_active ? 'bg-blue-50/40' : '' }}">
                                        <td class="px-4 py-3 font-medium text-gray-800">
                                            {{ $year->name }}
                                            @if ($year->is_active)
                                                <span
                                                    class="ml-1.5 text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-semibold">Active</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600">
                                            @if ($year->admission_start && $year->admission_end)
                                                {{ $year->admission_start->format('d M Y') }} →
                                                {{ $year->admission_end->format('d M Y') }}
                                            @else
                                                <span class="text-gray-400 italic">Not set</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600">
                                            {{ $year->daily_cutoff_time ? \Carbon\Carbon::parse($year->daily_cutoff_time)->format('g:i A') : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($year->isAdmissionOpen())
                                                <span
                                                    class="text-xs px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-semibold">Open</span>
                                            @else
                                                <span
                                                    class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 font-semibold">Closed</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button
                                                onclick="openEditModal({{ $year->id }}, '{{ $year->name }}', '{{ $year->admission_start?->format('Y-m-d') }}', '{{ $year->admission_end?->format('Y-m-d') }}', '{{ $year->daily_cutoff_time ? \Carbon\Carbon::parse($year->daily_cutoff_time)->format('H:i') : '' }}')"
                                                class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                                                ✏️ Edit
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- ── Info Panel ───────────────────────────────────────────────── --}}
            <div class="space-y-4">

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-4">Current Settings</h4>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Academic Year</dt>
                            <dd class="font-semibold text-blue-700">{{ $activeYear->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Year Dates</dt>
                            <dd class="font-medium text-gray-700">
                                {{ $activeYear->start_date?->format('d M Y') ?? '—' }}
                                → {{ $activeYear->end_date?->format('d M Y') ?? '—' }}
                            </dd>
                        </div>
                        <hr class="border-gray-100">
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Admission Opens</dt>
                            <dd class="font-medium text-gray-700">
                                {{ $activeYear->admission_start?->format('d M Y') ?? '⚠️ Not set' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Admission Closes</dt>
                            <dd class="font-medium text-gray-700">
                                {{ $activeYear->admission_end?->format('d M Y') ?? '⚠️ Not set' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Daily Cut-off</dt>
                            <dd class="font-medium text-gray-700">
                                {{ $activeYear->daily_cutoff_time
                                    ? \Carbon\Carbon::parse($activeYear->daily_cutoff_time)->format('g:i A')
                                    : '⚠️ Not set' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-700 space-y-1.5">
                    <p class="font-semibold text-blue-800">ℹ️ How the period works</p>
                    <p>HOIs can only submit daily admission entries between the start and end dates.</p>
                    <p>Each day, entries are editable until the cut-off time. After cut-off, DRAFT entries are auto-locked.
                    </p>
                    <p>The public portal only shows <strong>verified</strong> entries — not drafts.</p>
                    <p>OOSC and P2P entries are accepted even outside the window.</p>
                </div>

                <a href="{{ route('admin.academic-years.index') }}"
                    class="flex items-center gap-2 px-4 py-3 bg-white rounded-xl border border-gray-100 shadow-sm
                          text-sm text-gray-600 hover:bg-gray-50 transition">
                    📅 Manage Academic Years →
                </a>

            </div>
        </div>

    @endif

    {{-- ══════════════════════════════════════════════════════════════
         EDIT OTHER YEAR MODAL
    ══════════════════════════════════════════════════════════════ --}}
    <div id="editYearModal" class="fixed inset-0 bg-black/40 z-50 items-center justify-center p-4" style="display:none">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Edit Admission Period — <span id="modalYearName"></span></h3>
                <button onclick="document.getElementById('editYearModal').style.display='none'"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
            </div>
            <form id="editYearForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Admission Start Date</label>
                        <input type="date" id="modalStart" name="admission_start" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Admission End Date</label>
                        <input type="date" id="modalEnd" name="admission_end" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Daily Cut-off Time</label>
                        <input type="time" id="modalCutoff" name="daily_cutoff_time" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">
                        💾 Save
                    </button>
                    <button type="button" onclick="document.getElementById('editYearModal').style.display='none'"
                        class="px-5 py-2.5 text-sm text-gray-500 hover:text-gray-700 rounded-xl border border-gray-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const baseUpdateUrl = "{{ url('fde/admission-period') }}";

        function openEditModal(id, name, start, end, cutoff) {
            document.getElementById('modalYearName').textContent = name;
            document.getElementById('editYearForm').action = `${baseUpdateUrl}/${id}`;
            document.getElementById('modalStart').value = start;
            document.getElementById('modalEnd').value = end;
            document.getElementById('modalCutoff').value = cutoff;
            document.getElementById('editYearModal').style.display = 'flex';
        }

        document.getElementById('editYearModal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
    </script>
@endpush
