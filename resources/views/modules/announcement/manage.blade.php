@extends('layouts.app')

@section('title', 'Manage Announcements')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('announcements.index') }}"
                        class="hover:text-blue-600 transition-colors">Announcements</a>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-700 font-medium">Manage</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Manage Announcements</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $announcements->total() }} announcement{{ $announcements->total() !== 1 ? 's' : '' }} total
                </p>
            </div>
            <a href="{{ route('announcements.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Announcement
            </a>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div
                class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                <svg class="h-5 w-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @if ($announcements->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50">
                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Announcement</th>
                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Type / Priority</th>
                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Reads</th>
                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Created</th>
                                <th
                                    class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($announcements as $announcement)
                                <tr class="hover:bg-gray-50 transition-colors">

                                    {{-- Title & Meta --}}
                                    <td class="px-5 py-4 max-w-sm">
                                        <div class="flex items-start gap-2">
                                            @if ($announcement->is_pinned)
                                                <svg class="h-4 w-4 text-yellow-500 mt-0.5 shrink-0" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                                                </svg>
                                            @endif
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 leading-snug">
                                                    {{ Str::limit($announcement->title, 70) }}
                                                </p>
                                                <p class="text-xs text-gray-400 mt-0.5">
                                                    By {{ $announcement->creator?->name ?? 'System' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Type / Priority --}}
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $announcement->getTypeBadgeClass() }} w-fit capitalize">
                                                {{ $announcement->type }}
                                            </span>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $announcement->getPriorityBadgeClass() }} w-fit capitalize">
                                                {{ $announcement->priority }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-5 py-4">
                                        @if ($announcement->isExpired())
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                                Expired
                                            </span>
                                        @elseif($announcement->isPublished())
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                                Published
                                            </span>
                                        @elseif($announcement->published_at && $announcement->published_at->isFuture())
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
                                                Scheduled
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                                Draft
                                            </span>
                                        @endif

                                        @if ($announcement->published_at)
                                            <p class="text-xs text-gray-400 mt-1">
                                                {{ $announcement->published_at->format('d M Y') }}
                                            </p>
                                        @endif
                                    </td>

                                    {{-- Reads --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-1.5 text-sm text-gray-700">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            {{ number_format($announcement->reads_count) }}
                                        </div>
                                    </td>

                                    {{-- Created --}}
                                    <td class="px-5 py-4">
                                        <p class="text-sm text-gray-600">{{ $announcement->created_at->format('d M Y') }}
                                        </p>
                                        <p class="text-xs text-gray-400">{{ $announcement->created_at->diffForHumans() }}
                                        </p>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <a href="{{ route('announcements.show', $announcement) }}"
                                                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                                                title="View">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            {{-- Edit --}}
                                            <a href="{{ route('announcements.edit', $announcement) }}"
                                                class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-md transition-colors"
                                                title="Edit">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            {{-- Delete --}}
                                            <form action="{{ route('announcements.destroy', $announcement) }}"
                                                method="POST"
                                                onsubmit="return confirm('Delete this announcement? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                                                    title="Delete">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($announcements->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                        {{ $announcements->links() }}
                    </div>
                @endif
            @else
                {{-- Empty State --}}
                <div class="text-center py-16 px-4">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-1">No announcements yet</h3>
                    <p class="text-sm text-gray-500 mb-5">Get started by creating your first announcement.</p>
                    <a href="{{ route('announcements.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Announcement
                    </a>
                </div>
            @endif
        </div>

        {{-- Stats row --}}
        @if ($announcements->count() > 0)
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @php
                    $allItems = $announcements->getCollection();
                    $published = $allItems->filter(fn($a) => $a->isPublished() && !$a->isExpired())->count();
                    $drafts = $allItems->filter(fn($a) => !$a->published_at)->count();
                    $pinned = $allItems->filter(fn($a) => $a->is_pinned)->count();
                    $expired = $allItems->filter(fn($a) => $a->isExpired())->count();
                @endphp
                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $published }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Published</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 text-center">
                    <p class="text-2xl font-bold text-gray-600">{{ $drafts }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Drafts</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 text-center">
                    <p class="text-2xl font-bold text-yellow-500">{{ $pinned }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Pinned</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 text-center">
                    <p class="text-2xl font-bold text-gray-400">{{ $expired }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Expired</p>
                </div>
            </div>
        @endif

    </div>
@endsection
