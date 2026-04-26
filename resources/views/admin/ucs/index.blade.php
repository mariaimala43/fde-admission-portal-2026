@extends('layouts.app')
@section('title', 'Union Councils')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Union Councils</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $ucs->total() }} UCs total &nbsp;·&nbsp;
                {{ $ucs->where('institutions_count', 0)->count() }} with no schools
                &nbsp;·&nbsp;
                {{ $ucs->whereNull('sector_id')->count() }} with no sector
            </p>
        </div>
        <a href="{{ route('admin.ucs.create') }}"
            class="bg-blue-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            + Add UC
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

    {{-- Integrity alerts --}}
    @php
        $noSectorUCs = $ucs->filter(fn($u) => is_null($u->sector_id));
        $noSchoolUCs = $ucs->filter(fn($u) => $u->institutions_count === 0);
    @endphp

    @if ($noSectorUCs->count())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-4 text-sm">
            ⚠ <strong>{{ $noSectorUCs->count() }} UC(s) have no Sector assigned:</strong>
            {{ $noSectorUCs->pluck('code')->join(', ') }}
        </div>
    @endif

    @if ($noSchoolUCs->count())
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-4 text-sm">
            📋 <strong>{{ $noSchoolUCs->count() }} UC(s) have no institutions linked:</strong>
            {{ $noSchoolUCs->pluck('code')->join(', ') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">UC Name</th>
                        <th class="px-4 py-3 text-left">Sector</th>
                        <th class="px-4 py-3 text-center">Schools</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ucs as $uc)
                        @php
                            $noSector   = is_null($uc->sector_id);
                            $noSchools  = $uc->institutions_count === 0;
                            $rowBg = $noSector ? 'bg-red-50' : ($noSchools ? 'bg-amber-50' : '');
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $rowBg }}">
                            <td class="px-4 py-3 font-mono font-semibold text-gray-700">{{ $uc->code }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $uc->name }}</td>
                            <td class="px-4 py-3">
                                @if ($noSector)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        ⚠ No Sector
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-800">
                                        {{ $uc->sector?->name }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($noSchools)
                                    <span class="text-amber-600 font-semibold">0</span>
                                @else
                                    <span class="font-semibold text-gray-700">{{ $uc->institutions_count }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $uc->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $uc->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.ucs.edit', $uc) }}"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>
                                    <span class="text-gray-200">|</span>
                                    <form method="POST" action="{{ route('admin.ucs.destroy', $uc) }}"
                                        onsubmit="return confirm('Delete UC \'{{ addslashes($uc->name) }}\'? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-700 text-sm font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                No union councils found.
                                <a href="{{ route('admin.ucs.create') }}" class="text-blue-600 hover:underline">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200 text-xs font-semibold text-gray-600">
                    <tr>
                        <td colspan="3" class="px-4 py-3">Total</td>
                        <td class="px-4 py-3 text-center">{{ $ucs->sum('institutions_count') }}</td>
                        <td colspan="2" class="px-4 py-3 text-gray-400">
                            {{ $ucs->where('is_active', true)->count() }} active UCs
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($ucs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $ucs->links() }}
            </div>
        @endif
    </div>

@endsection
