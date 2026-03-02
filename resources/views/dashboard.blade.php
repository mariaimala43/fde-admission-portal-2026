@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- HOI DASHBOARD                                       --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @role('hoi')

        @php
            $user = Auth::user();
            $institution = $user->institution?->load(['sector']);
        @endphp

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $institution?->name ?? 'My Dashboard' }}</h2>
                <p class="text-gray-500 text-sm mt-1">
                    {{ $institution?->sector?->name }} Sector
                    &nbsp;·&nbsp; {{ $institution?->type }}
                    &nbsp;·&nbsp; {{ ucfirst($institution?->shift) }}
                </p>
            </div>
            @if ($institution)
                <span
                    class="px-3 py-1 rounded-full text-xs font-semibold uppercase
        {{ $institution->admission_status === 'open'
            ? 'bg-green-100 text-green-700'
            : ($institution->admission_status === 'closed'
                ? 'bg-red-100 text-red-700'
                : 'bg-yellow-100 text-yellow-700') }}">
                    Admissions: {{ ucfirst(str_replace('_', ' ', $institution->admission_status)) }}
                </span>
            @endif
        </div>

        @if ($institution)

            @php
                $classes = \App\Models\InstitutionClass::where('institution_id', $institution->id)
                    ->where('is_active', true)
                    ->with(['classModel'])
                    ->orderBy('class_id')
                    ->get();

                $sectionsMap = \App\Models\InstitutionSection::where('institution_id', $institution->id)
                    ->orderBy('order')
                    ->get()
                    ->groupBy('class_id');

                $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();
                $newlyAdmitted = \App\Models\DailyAdmission::where('institution_id', $institution->id)
                    ->where('academic_year_id', $academicYear?->id)
                    ->selectRaw('class_id, SUM(boys_count + girls_count + oosc_boys + oosc_girls + p2p_boys + p2p_girls) as total')
                    ->groupBy('class_id')
                    ->pluck('total', 'class_id');

                $totalSeats    = $classes->sum('total_seats');
                $totalEnrolled = $classes->sum('existing_enrollment');
                $totalNewAdmit = $newlyAdmitted->sum();
                $totalAvailable = $classes->sum(fn($c) => max(0, $c->total_seats - $c->existing_enrollment - ($newlyAdmitted[$c->class_id] ?? 0)));

                $todayTotal =
                    \App\Models\DailyAdmission::where('institution_id', $institution->id)
                        ->where('admission_date', now()->toDateString())
                        ->selectRaw('SUM(boys_count + girls_count + oosc_boys + oosc_girls + p2p_boys + p2p_girls) as total')
                        ->value('total') ?? 0;
            @endphp

            {{-- ── Quick Stats ─────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Intake Capacity</p>
                    <p class="text-2xl font-bold text-blue-900">{{ number_format($totalSeats) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-orange-100 p-5 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Existing Enrollment</p>
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($totalEnrolled) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-green-100 p-5 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Seats Available</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($totalAvailable) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-5 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Newly Admitted</p>
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($totalNewAdmit) }}</p>
                </div>
                <div class="bg-blue-900 rounded-xl shadow-sm p-5 text-center">
                    <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Total Enrollment</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($totalEnrolled + $totalNewAdmit) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Today's Admissions</p>
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($todayTotal) }}</p>
                </div>
            </div>

            {{-- ── Class-wise Table (Document Format) ─────────────── --}}
            @if ($classes->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Class-wise Enrollment Summary</h3>
                        <a href="{{ route('hoi.classes.setup') }}" class="text-xs text-blue-600 hover:underline">Edit Classes</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left">Class</th>
                                    <th class="px-4 py-3 text-center">No. of Sections</th>
                                    <th class="px-4 py-3 text-center">Existing Enrollment</th>
                                    <th class="px-4 py-3 text-center">Intake Capacity</th>
                                    <th class="px-4 py-3 text-center text-green-600">Seats Available</th>
                                    <th class="px-4 py-3 text-center text-blue-600">Newly Admitted</th>
                                    <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Enrollment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($classes as $ic)
                                    @php
                                        $secs      = $sectionsMap[$ic->class_id] ?? collect();
                                        $secCount  = $secs->count();
                                        $admitted  = $newlyAdmitted[$ic->class_id] ?? 0;
                                        $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                                        $totalEnrl = $ic->existing_enrollment + $admitted;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-gray-800">
                                            {{ $ic->classModel?->name }}
                                            @if ($ic->classModel?->is_ece)
                                                <span class="ml-1 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">ECE</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600">
                                            {{ $secCount }}
                                            @if ($secs->isNotEmpty())
                                                <div class="text-xs text-gray-400">({{ $secs->pluck('name')->join(', ') }})</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-orange-600 font-medium">{{ number_format($ic->existing_enrollment) }}</td>
                                        <td class="px-4 py-3 text-center font-medium text-gray-700">{{ number_format($ic->total_seats) }}</td>
                                        <td class="px-4 py-3 text-center font-bold {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">{{ number_format($available) }}</td>
                                        <td class="px-4 py-3 text-center font-medium text-blue-700">{{ number_format($admitted) }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-blue-900 bg-blue-50">{{ number_format($totalEnrl) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">TOTAL</td>
                                    <td class="px-4 py-3 text-center text-gray-600">
                                        {{ $classes->sum(fn($ic) => ($sectionsMap[$ic->class_id] ?? collect())->count()) }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-orange-600">{{ number_format($totalEnrolled) }}</td>
                                    <td class="px-4 py-3 text-center text-blue-900">{{ number_format($totalSeats) }}</td>
                                    <td class="px-4 py-3 text-center {{ $totalAvailable > 0 ? 'text-green-600' : 'text-red-500' }}">{{ number_format($totalAvailable) }}</td>
                                    <td class="px-4 py-3 text-center text-blue-700">{{ number_format($totalNewAdmit) }}</td>
                                    <td class="px-4 py-3 text-center text-blue-900 bg-blue-100">{{ number_format($totalEnrolled + $totalNewAdmit) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- ── Action Cards ─────────────────────────────────── --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <a href="{{ route('hoi.enrollment.index') }}"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-3">
                            <p class="text-sm text-gray-500">Baseline Enrollment</p>
                            @if ($totalEnrolled > 0)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Entered</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full">Pending</span>
                            @endif
                        </div>
                        <p class="text-3xl font-bold text-blue-900 mb-1">{{ number_format($totalEnrolled) }}</p>
                        <p class="text-xs text-blue-600 font-medium">Enter / Update</p>
                    </a>

                    <a href="{{ route('hoi.admissions.daily') }}"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <p class="text-sm text-gray-500 mb-1">Today's Admissions</p>
                        <p class="text-3xl font-bold text-blue-900 mb-1">{{ number_format($todayTotal) }}</p>
                        <p class="text-xs text-blue-600 font-medium">Enter Admissions</p>
                    </a>

                    <a href="{{ route('hoi.admissions.report') }}"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <p class="text-sm text-gray-500 mb-1">Cumulative Admissions</p>
                        <p class="text-3xl font-bold text-blue-900 mb-1">{{ number_format($totalNewAdmit) }}</p>
                        <p class="text-xs text-blue-600 font-medium">View Report</p>
                    </a>

                </div>
            @else
                {{-- No classes configured yet --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                    <p class="text-yellow-800 font-medium mb-3">Classes not configured yet.</p>
                    <a href="{{ route('hoi.classes.setup') }}"
                        class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Setup Classes Now
                    </a>
                </div>
            @endif
        @else
            {{-- No institution --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                <p class="text-yellow-800 font-medium mb-3">School profile not set up yet.</p>
                <a href="{{ route('hoi.profile.setup') }}"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    Setup Profile Now
                </a>
            </div>

        @endif

    @endrole

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- DIRECTOR DASHBOARD (view-only)                      --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @role('director')
        <h2 class="text-2xl font-bold text-gray-800 mb-1">Director Dashboard</h2>
        <p class="text-gray-500 text-sm mb-8">Academic Year 2026-27</p>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-gray-500 text-sm">Director features coming soon.</p>
        </div>
    @endrole

@endsection
