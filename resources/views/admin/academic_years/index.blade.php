{{-- SAVE AS: resources/views/admin/academic_years/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Academic Years')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Academic Years</h2>
            <p class="text-sm text-gray-500 mt-1">Manage academic years and admission windows</p>
        </div>
        <a href="{{ route('admin.academic-years.create') }}"
            class="px-5 py-2.5 bg-blue-900 text-white text-sm font-bold rounded-xl hover:bg-blue-800 transition">
            + New Academic Year
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            ❌ {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-5 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-center">Year Period</th>
                    <th class="px-4 py-3 text-center">Admission Window</th>
                    <th class="px-4 py-3 text-center">Daily Cutoff</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($years as $year)
                    <tr
                        class="border-b border-gray-50 hover:bg-gray-50 transition {{ $year->is_active ? 'bg-green-50' : '' }}">
                        <td class="px-5 py-3">
                            <span class="font-bold text-gray-800">{{ $year->name }}</span>
                            @if ($year->is_active)
                                <span
                                    class="ml-2 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">Active</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $year->start_date->format('d M Y') }} — {{ $year->end_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            @if ($year->admission_start && $year->admission_end)
                                {{ $year->admission_start->format('d M') }} — {{ $year->admission_end->format('d M Y') }}
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ \Carbon\Carbon::createFromTimeString($year->daily_cutoff_time)->format('g:i A') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($year->is_active)
                                <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold">🟢
                                    Active</span>
                            @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.academic-years.edit', $year) }}"
                                    class="px-3 py-1.5 text-xs font-semibold bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                                    Edit
                                </a>
                                @if (!$year->is_active)
                                    <form method="POST" action="{{ route('admin.academic-years.set-active', $year) }}">
                                        @csrf
                                        <button type="submit"
                                            onclick="return confirm('Set {{ $year->name }} as the active academic year?')"
                                            class="px-3 py-1.5 text-xs font-semibold bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                            Set Active
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}"
                                        onsubmit="return confirm('Delete academic year \'{{ addslashes($year->name) }}\'? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No academic years yet.
                            <a href="{{ route('admin.academic-years.create') }}"
                                class="text-blue-600 underline ml-1">Create one →</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
