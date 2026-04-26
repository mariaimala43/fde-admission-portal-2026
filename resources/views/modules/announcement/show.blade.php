@extends('layouts.app')

@section('title', 'Announcement')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('announcements.index') }}" class="hover:text-blue-600 transition-colors">Announcements</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-700 font-medium">View</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $announcement->title }}</h1>
        </div>
        <a href="{{ route('announcements.edit', $announcement) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition-colors">
            ✏️ Edit
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">

        {{-- Status badges --}}
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold {{ $announcement->getTypeBadgeClass() }} capitalize">
                {{ $announcement->getIcon() }} {{ $announcement->type }}
            </span>
            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold {{ $announcement->getPriorityBadgeClass() }} capitalize">
                {{ $announcement->priority }} priority
            </span>
            @if ($announcement->isExpired())
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Expired</span>
            @elseif ($announcement->isPublished())
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Published</span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Draft</span>
            @endif
            @if ($announcement->is_pinned)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">📌 Pinned</span>
            @endif
        </div>

        {{-- Message --}}
        <div class="prose prose-sm max-w-none text-gray-700">
            {!! nl2br(e($announcement->body)) !!}
        </div>

        {{-- Meta --}}
        <div class="pt-4 border-t border-gray-100 grid grid-cols-2 gap-3 text-sm text-gray-500">
            <div>
                <span class="font-medium text-gray-700">Target roles:</span>
                {{ empty($announcement->target_roles) ? 'All roles' : implode(', ', $announcement->target_roles) }}
            </div>
            <div>
                <span class="font-medium text-gray-700">Reads:</span>
                {{ number_format($announcement->reads_count) }}
            </div>
            <div>
                <span class="font-medium text-gray-700">Published:</span>
                {{ $announcement->published_at ? $announcement->published_at->format('d M Y, h:i A') : '—' }}
            </div>
            <div>
                <span class="font-medium text-gray-700">Expires:</span>
                {{ $announcement->expires_at ? $announcement->expires_at->format('d M Y, h:i A') : 'Never' }}
            </div>
            <div>
                <span class="font-medium text-gray-700">Created by:</span>
                {{ $announcement->creator?->name ?? 'System' }}
            </div>
            <div>
                <span class="font-medium text-gray-700">Created at:</span>
                {{ $announcement->created_at->format('d M Y, h:i A') }}
            </div>
        </div>

    </div>

</div>
@endsection
