@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="fde-card mb-6" style="padding:20px 24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 style="font-size:18px;font-weight:700;color:var(--text);margin-bottom:4px;">
                    🔔 Notifications
                </h1>
                <p style="font-size:13px;color:var(--text-muted);">
                    Admission reminders and system alerts
                </p>
            </div>
            @if($notifications->where('read_at', null)->count() > 0)
            <form method="POST" action="{{ route('hoi.notifications.mark-all-read') }}">
                @csrf
                <button type="submit"
                        style="padding:8px 16px;border-radius:8px;
                               background:var(--active-bg);border:1px solid var(--border-g);
                               color:var(--active-text);font-size:12px;font-weight:600;
                               cursor:pointer;transition:all var(--t);">
                    ✓ Mark all as read
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- ── Notification list ───────────────────────────────────────────── --}}
    @if($notifications->isEmpty())

        <div class="fde-card" style="padding:48px 24px;text-align:center;">
            <div style="font-size:40px;margin-bottom:12px;">🔕</div>
            <p style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px;">
                No notifications yet
            </p>
            <p style="font-size:13px;color:var(--text-muted);">
                Daily admission reminders will appear here at 3:00 PM PKT if you haven't submitted.
            </p>
        </div>

    @else

        <div style="display:flex;flex-direction:column;gap:8px;">
            @foreach($notifications as $notification)
            @php
                $data    = $notification->data;
                $isUnread = is_null($notification->read_at);
            @endphp

            <div class="fde-card"
                 style="padding:0;overflow:hidden;
                        {{ $isUnread ? 'border-left:3px solid #f59e0b;' : '' }}">
                <div style="display:flex;align-items:flex-start;gap:14px;padding:16px 20px;">

                    {{-- Bell icon --}}
                    <div style="flex-shrink:0;width:38px;height:38px;border-radius:50%;
                                background:{{ $isUnread ? 'rgba(245,158,11,0.18)' : 'var(--card2)' }};
                                display:flex;align-items:center;justify-content:center;">
                        <svg style="width:18px;height:18px;color:{{ $isUnread ? '#f59e0b' : 'var(--text-faint)' }};"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002
                                     6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388
                                     6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3
                                     0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>

                    {{-- Content --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:flex-start;
                                    justify-content:space-between;gap:8px;margin-bottom:4px;">
                            <p style="font-size:13px;font-weight:{{ $isUnread ? '700' : '600' }};
                                      color:var(--text);line-height:1.3;">
                                {{ $data['title'] ?? 'Notification' }}
                                @if($isUnread)
                                    <span style="display:inline-block;width:7px;height:7px;
                                                 border-radius:50%;background:#3b82f6;
                                                 margin-left:5px;vertical-align:middle;"></span>
                                @endif
                            </p>
                            <span style="font-size:11px;color:var(--text-faint);flex-shrink:0;
                                         white-space:nowrap;">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p style="font-size:12px;color:var(--text-muted);line-height:1.5;margin-bottom:10px;">
                            {{ $data['message'] ?? '' }}
                        </p>

                        {{-- Action buttons --}}
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            @if(!empty($data['action_url']))
                            <a href="{{ $data['action_url'] }}"
                               style="display:inline-flex;align-items:center;gap:4px;
                                      padding:6px 14px;border-radius:8px;
                                      background:#f59e0b;color:#fff;
                                      font-size:12px;font-weight:600;
                                      text-decoration:none;transition:all var(--t);">
                                {{ $data['action_text'] ?? 'View' }} →
                            </a>
                            @endif

                            <form method="POST"
                                  action="{{ route('hoi.notifications.destroy', $notification->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="padding:5px 12px;border-radius:8px;
                                               background:none;border:1px solid var(--border);
                                               font-size:12px;color:var(--text-muted);
                                               cursor:pointer;transition:all var(--t);">
                                    Dismiss
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            @endforeach

            {{-- Pagination --}}
            @if($notifications->hasPages())
            <div style="margin-top:8px;">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>

    @endif

</div>
@endsection
