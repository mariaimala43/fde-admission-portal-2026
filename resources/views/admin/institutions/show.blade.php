@extends('layouts.app')
@section('title', $institution->name)
@section('content')

    <div class="mb-6 flex justify-between items-start">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $institution->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                <a href="{{ route('admin.institutions.index') }}" class="text-blue-600 hover:underline">Institutions</a>
                / View
            </p>
        </div>
        <a href="{{ route('admin.institutions.edit', $institution) }}"
            class="bg-blue-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            Edit
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Details Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                School Information
            </h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Type</dt>
                    <dd class="font-medium text-gray-800">{{ $institution->type }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Gender</dt>
                    <dd class="font-medium text-gray-800 capitalize">{{ str_replace('_', '-', $institution->gender) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Shift</dt>
                    <dd class="font-medium text-gray-800 capitalize">{{ $institution->shift }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Sector</dt>
                    <dd class="font-medium text-gray-800">{{ $institution->sector?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Union Council</dt>
                    <dd class="font-medium text-gray-800">{{ $institution->unionCouncil?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Address</dt>
                    <dd class="font-medium text-gray-800">{{ $institution->address ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Admission Status</dt>
                    <dd>
                        <span
                            class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $institution->admission_status == 'open'
                            ? 'bg-green-100 text-green-700'
                            : ($institution->admission_status == 'closed'
                                ? 'bg-red-100 text-red-700'
                                : 'bg-gray-100 text-gray-600') }}">
                            {{ ucfirst(str_replace('_', ' ', $institution->admission_status)) }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Facilities Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                Facilities
            </h3>
            <dl class="space-y-3 text-sm">
                @foreach ([
            'has_matric_tech' => 'Matric Tech Classes',
            'has_transport' => 'Transport Facility',
            'has_meal_program' => 'Meal Program',
            'has_evening_classes' => 'Evening Classes',
        ] as $field => $label)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">{{ $label }}</dt>
                        <dd>
                            @if ($institution->$field)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Yes</span>
                            @else
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded-full">No</span>
                            @endif
                        </dd>
                    </div>
                @endforeach

                <div class="flex justify-between">
                    <dt class="text-gray-500">Cambridge Classes</dt>
                    <dd>
                        @if ($institution->is_cambridge)
                            <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">Yes — System
                                Locked</span>
                        @else
                            <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded-full">No</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

    </div>

@endsection
