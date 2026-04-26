<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Full paginated notifications list.
     * Marks all as read when the page is opened.
     */
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->orderByDesc('created_at')
            ->paginate(20);

        // Mark all unread as read when the user views the list
        Auth::user()->unreadNotifications->markAsRead();

        return view('hoi.notifications.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read (supports AJAX or form POST).
     */
    public function markRead(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark every unread notification for this user as read.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a single notification permanently.
     */
    public function destroy(string $id)
    {
        Auth::user()->notifications()->findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Notification removed.');
    }
}
