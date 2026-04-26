@extends('layouts.app')
@section('title', 'Admission Quota')

@section('content')

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Admission Quota</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->name }}
                @if($academicYear)
                    &middot; <span class="text-blue-700 font-medium">{{ $academicYear->name }}</span>
                @endif
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Set how many new students you plan to admit this year per class.
                Leave blank for no limit.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:underline">← Dashboard</a>
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

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-blue-900">{{ $totalQuota > 0 ? number_format($totalQuota) : '—' }}</p>
            <p class="text-xs text-gray-400 uppercase mt-1">Total Quota Set</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ number_format($totalAdmitted) }}</p>
            <p class="text-xs text-gray-400 uppercase mt-1">Admitted So Far</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $totalQuota > 0 ? number_format($totalAvailable) : '—' }}</p>
            <p class="text-xs text-gray-400 uppercase mt-1">Remaining</p>
        </div>
    </div>

    {{-- Quota Form --}}
    <form method="POST" action="{{ route('hoi.quota.save') }}">
        @csrf
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-900 text-white text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Class</th>
                            <th class="px-4 py-3 text-center font-semibold">Admitted So Far</th>
                            <th class="px-4 py-3 text-center font-semibold">
                                Admission Quota
                                <div class="text-blue-200 font-normal normal-case text-xs mt-0.5">
                                    Max new students for this year
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center font-semibold hidden sm:table-cell">Remaining</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($classes as $ic)
                            @php
                                $admittedCount = (int) ($admitted[$ic->class_id] ?? 0);
                                $quota         = $ic->admission_quota;
                                $remaining     = $quota !== null ? max(0, $quota - $admittedCount) : null;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $ic->classModel?->name ?? "Class {$ic->class_id}" }}
                                    @if($ic->classModel?->is_ece)
                                        <span class="ml-1 text-xs bg-pink-100 text-pink-700 px-1.5 py-0.5 rounded-full">ECE</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="{{ $admittedCount > 0 ? 'text-orange-600 font-semibold' : 'text-gray-400' }}">
                                        {{ $admittedCount > 0 ? number_format($admittedCount) : '—' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <input type="hidden" name="quota[{{ $loop->index }}][class_id]" value="{{ $ic->class_id }}">
                                    <input
                                        type="number"
                                        name="quota[{{ $loop->index }}][quota]"
                                        value="{{ $quota }}"
                                        min="0"
                                        max="99999"
                                        placeholder="No limit"
                                        class="w-28 text-center border border-gray-300 rounded-lg px-2 py-1.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                                    >
                                </td>

                                <td class="px-4 py-3 text-center hidden sm:table-cell">
                                    @if ($quota === null)
                                        <span class="text-xs text-gray-400 italic">No limit</span>
                                    @elseif ($remaining === 0)
                                        <span class="text-xs font-semibold text-red-500">Full</span>
                                    @else
                                        <span class="font-semibold text-green-600">{{ number_format($remaining) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    No classes configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
                <button type="submit"
                    class="bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                    💾 Save Quotas
                </button>
                <a href="{{ route('dashboard') }}"
                    class="px-5 py-2.5 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </div>
    </form>

@endsection
