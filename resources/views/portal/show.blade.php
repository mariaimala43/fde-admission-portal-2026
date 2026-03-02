<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $institution->name }} - FDE Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen">

    {{-- Header --}}
    <div class="bg-blue-900 text-white py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <a href="{{ route('portal.index') }}" class="text-blue-300 text-sm hover:text-white mb-4 inline-block">
                Back to Schools
            </a>
            <h1 class="text-2xl md:text-3xl font-bold">{{ $institution->name }}</h1>
            <p class="text-blue-200 text-sm mt-1">
                {{ $institution->sector?->name }} Sector
                &nbsp;&middot;&nbsp; {{ $institution->type }}
                &nbsp;&middot;&nbsp; {{ ucfirst(str_replace('_', ' ', $institution->gender)) }}
                &nbsp;&middot;&nbsp; {{ ucfirst($institution->shift) }} Shift
            </p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            {{-- School Info --}}
            <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">
                    School Information
                </h2>
                <div class="space-y-3 text-sm">
                    @if ($institution->address)
                        <div class="flex gap-3">
                            <span class="text-gray-400">Address:</span>
                            <span class="text-gray-700">{{ $institution->address }}</span>
                        </div>
                    @endif
                    @if ($institution->contact_number)
                        <div class="flex gap-3">
                            <span class="text-gray-400">Phone:</span>
                            <span class="text-gray-700">{{ $institution->contact_number }}</span>
                        </div>
                    @endif
                    <div class="flex gap-3">
                        <span class="text-gray-400">Level:</span>
                        <span class="text-gray-700">{{ $institution->type }} School</span>
                    </div>
                    <div class="flex gap-3">
                        <span class="text-gray-400">Shift:</span>
                        <span class="text-gray-700">{{ ucfirst($institution->shift) }} Shift</span>
                    </div>
                </div>

                {{-- Facilities --}}
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mt-6 mb-3">
                    Facilities
                </h2>
                <div class="flex flex-wrap gap-2">
                    @if ($institution->has_transport)
                        <span class="bg-yellow-100 text-yellow-700 text-xs px-3 py-1.5 rounded-full font-medium">Transport</span>
                    @endif
                    @if ($institution->has_meal_program)
                        <span class="bg-green-100 text-green-700 text-xs px-3 py-1.5 rounded-full font-medium">Meal Program</span>
                    @endif
                    @if ($institution->has_matric_tech)
                        <span class="bg-orange-100 text-orange-700 text-xs px-3 py-1.5 rounded-full font-medium">Matric Tech</span>
                    @endif
                    @if ($institution->has_evening_classes)
                        <span class="bg-purple-100 text-purple-700 text-xs px-3 py-1.5 rounded-full font-medium">Evening Classes</span>
                    @endif
                    @if ($institution->is_cambridge)
                        <span class="bg-indigo-100 text-indigo-700 text-xs px-3 py-1.5 rounded-full font-medium">Cambridge</span>
                    @endif
                    @if ($institution->has_ece)
                        <span class="bg-pink-100 text-pink-700 text-xs px-3 py-1.5 rounded-full font-medium">ECE Center</span>
                    @endif
                    @if (!$institution->has_transport && !$institution->has_meal_program && !$institution->has_matric_tech && !$institution->has_evening_classes && !$institution->is_cambridge && !$institution->has_ece)
                        <span class="text-gray-400 text-sm">No special facilities listed</span>
                    @endif
                </div>
            </div>

            {{-- Admission Status --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">
                    Admission Status
                </h2>
                @php
                    $totalSeats = $seatData->sum('total_seats');
                    $totalExist = $seatData->sum('existing_enrollment');
                    $totalAdmit = $admissionTotal->sum('total_admitted');
                    $totalAvail = max(0, $totalSeats - $totalExist - $totalAdmit);
                @endphp
                <div class="text-4xl font-bold {{ $totalAvail > 0 ? 'text-green-600' : 'text-red-500' }} mb-1">
                    {{ number_format($totalAvail) }}
                </div>
                <p class="text-sm text-gray-500 mb-4">total seats available</p>
                <span
                    class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                    {{ $institution->admission_status === 'open' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ ucfirst($institution->admission_status) }}
                </span>
                @if ($academicYear)
                    <p class="text-xs text-gray-400 mt-4">
                        {{ $academicYear->name }}<br>
                        Admissions open until<br>
                        {{ \Carbon\Carbon::parse($academicYear->admission_end)->format('d M Y') }}
                    </p>
                @endif
            </div>

        </div>

        {{-- Class-wise Enrollment Table (Document Format) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-800">Class-wise Enrollment & Availability</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Class</th>
                            <th class="px-4 py-3 text-center">Existing Enrollment</th>
                            <th class="px-4 py-3 text-center">Intake Capacity</th>
                            <th class="px-4 py-3 text-center text-green-600">Seats Available<br><span class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                            <th class="px-4 py-3 text-center text-blue-600">Newly Admitted</th>
                            <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Total Enrollment<br><span class="normal-case font-normal text-gray-400">(Auto-Calculated)</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($seatData as $ic)
                            @php
                                $admitted  = $admissionTotal[$ic->class_id]?->total_admitted ?? 0;
                                $available = max(0, $ic->total_seats - $ic->existing_enrollment - $admitted);
                                $totalEnrl = $ic->existing_enrollment + $admitted;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold text-gray-800">
                                    {{ $ic->classModel?->name }}
                                    @if ($ic->classModel?->is_ece)
                                        <span class="ml-1 text-xs bg-pink-100 text-pink-700 px-2 py-0.5 rounded-full">ECE</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-orange-600 font-medium">
                                    {{ number_format($ic->existing_enrollment) }}
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-gray-700">
                                    {{ number_format($ic->total_seats) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-lg font-bold {{ $available > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $available > 0 ? number_format($available) : 'Full' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-blue-700 font-medium">
                                    {{ number_format($admitted) }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-blue-900 bg-blue-50">
                                    {{ number_format($totalEnrl) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-blue-50 border-t-2 border-blue-100 font-bold text-sm">
                        <tr>
                            <td class="px-4 py-3 text-gray-700">TOTAL</td>
                            <td class="px-4 py-3 text-center text-orange-600">{{ number_format($totalExist) }}</td>
                            <td class="px-4 py-3 text-center text-blue-900">{{ number_format($totalSeats) }}</td>
                            <td class="px-4 py-3 text-center {{ $totalAvail > 0 ? 'text-green-600' : 'text-red-500' }}">{{ number_format($totalAvail) }}</td>
                            <td class="px-4 py-3 text-center text-blue-700">{{ number_format($totalAdmit) }}</td>
                            <td class="px-4 py-3 text-center text-blue-900 bg-blue-100">{{ number_format($totalExist + $totalAdmit) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    {{-- Footer --}}
    <div class="bg-blue-900 text-blue-200 text-center py-6 mt-16 text-xs">
        Federal Directorate of Education - Islamabad &nbsp;&middot;&nbsp; Admissions Portal {{ now()->year }}
    </div>

</body>

</html>
