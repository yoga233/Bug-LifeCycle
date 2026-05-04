<?php

namespace App\Http\Controllers\Programmer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = Notification::query()
            ->select(['id', 'user_id', 'related_id', 'type', 'message', 'is_read', 'created_at'])
            ->with([
                'bug:id,project_id,priority_id',
                'bug.project:id,name',
                'bug.priority:id,level',
            ])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Optimized: Use single query to get unread count - rely on existing index
        // The index notifications_user_is_read_idx should handle this efficiently
        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('panel.programmer.notifications', compact('notifications', 'unreadCount'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('status', 'Semua notifikasi berhasil ditandai sebagai dibaca.');
    }

    public function markRead(Notification $notification, Request $request): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        if (! $notification->is_read) {
            $notification->forceFill(['is_read' => true])->save();
        }

        // If notification is tied to a bug, go there. Otherwise go back to notifications.
        if ($notification->related_id) {
            return redirect()->route('programmer.bugs.show', [
                'bug' => $notification->related_id,
                'return' => url()->previous(),
            ]);
        }

        return redirect()->route('programmer.notifications');
    }

    public function destroy(Notification $notification, Request $request): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->delete();

        return back()->with('status', 'Notifikasi berhasil dihapus.');
    }
}
