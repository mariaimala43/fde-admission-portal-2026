{{-- resources/views/fde/merit_lists/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Merit Lists — FDE Cell')

@section('content')

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Merit Lists</h2>
            <p class="text-sm text-gray-500 mt-1">Upload and manage merit list files for all institutions</p>
        </div>
        <div class="text-sm text-gray-500 bg-white border border-gray-200 rounded-lg px-4 py-2">
            📁 {{ $totalFiles }} total file(s) uploaded
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Upload Form --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-4">
                <div class="px-5 py-4 border-b border-gray-100 bg-blue-900">
                    <h3 class="text-sm font-bold text-white">📤 Upload Merit List</h3>
                    <p class="text-xs text-blue-200 mt-0.5">Upload for any institution</p>
                </div>
                <form method="POST" action="{{ route('fde.merit-lists.store') }}"
                      enctype="multipart/form-data" class="p-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                            Institution <span class="text-red-500">*</span>
                        </label>
                        <select name="institution_id" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Select Institution —</option>
                            @foreach ($allInstitutions as $inst)
                                <option value="{{ $inst->id }}" {{ old('institution_id') == $inst->id ? 'selected' : '' }}>
                                    [EMIS: {{ $inst->code ?? 'N/A' }}] {{ $inst->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Type school name or EMIS in your browser's select search</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                            Title <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               placeholder="e.g. Class 9 Merit List 2026"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                            Files <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="files[]" accept=".pdf,.xlsx,.xls,.csv" multiple required
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                      file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0
                                      file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">PDF, Excel, or CSV · Max 10 MB each · Multiple files allowed</p>
                    </div>
                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                        Upload File(s)
                    </button>
                </form>
            </div>
        </div>

        {{-- Institution Files --}}
        <div class="lg:col-span-2 space-y-4" x-data="{ search: '' }">

            {{-- Search Bar --}}
            @if ($institutions->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3 flex items-center gap-3">
                <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input
                    x-model="search"
                    type="text"
                    placeholder="Search by school name or EMIS number…"
                    class="w-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none bg-transparent">
                <button x-show="search" @click="search=''"
                        class="text-gray-400 hover:text-gray-600 shrink-0 text-lg leading-none">×</button>
            </div>

            {{-- No results message --}}
            <p x-show="search && document.querySelectorAll('[data-merit-inst]:not([style*=\'display: none\'])').length === 0"
               class="text-center text-sm text-gray-400 py-6">
                No schools match "<span x-text="search" class="font-semibold"></span>"
            </p>
            @endif

            @if ($institutions->isEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-12 text-center">
                    <p class="text-gray-400 text-sm">No merit lists uploaded yet.</p>
                    <p class="text-gray-400 text-xs mt-1">Use the form to upload the first file.</p>
                </div>
            @else
                @foreach ($institutions as $inst)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                         data-merit-inst
                         x-show="!search ||
                                  '{{ strtolower(addslashes($inst->name)) }}'.includes(search.toLowerCase()) ||
                                  '{{ strtolower($inst->code ?? '') }}'.includes(search.toLowerCase())"
                         x-transition>
                        <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                            <div>
                                <span class="text-sm font-bold text-gray-800">{{ $inst->name }}</span>
                                <span class="ml-2 text-xs text-gray-400">EMIS: {{ $inst->code }}</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $inst->meritLists->count() }} file(s)</span>
                        </div>
                        <div class="divide-y divide-gray-50">
                            @foreach ($inst->meritLists as $ml)
                                <div class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded shrink-0 {{ $ml->badgeClass() }}">
                                            {{ $ml->extension() }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-800 truncate">
                                                {{ $ml->title ?: $ml->original_name }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                {{ $ml->original_name }}
                                                <span class="mx-1">·</span>
                                                {{ $ml->formattedSize() }}
                                                <span class="mx-1">·</span>
                                                {{ $ml->created_at->format('d M Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <a href="{{ $ml->downloadUrl() }}" target="_blank"
                                           class="px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                            ↓ Download
                                        </a>
                                        <form method="POST"
                                              action="{{ route('fde.merit-lists.destroy', $ml) }}"
                                              onsubmit="return confirm('Delete this file?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>{{-- end x-data --}}

    </div>

@endsection
