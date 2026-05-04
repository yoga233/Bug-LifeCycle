<?php

namespace App\Http\Controllers\QA;

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

        // Optimized: Get unread count from the paginated results or a single aggregate query
        // Using the cached value from topbar if available, or do a single efficient count
        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('panel.qa.notifications', compact('notifications', 'unreadCount'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        // Optimized: Direct UPDATE with WHERE condition - single query
        // MySQL will only update rows that match the condition
        // No need for exists() check first - let the database handle it
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('status', 'Semua notifikasi berhasil ditandai sebagai dibaca.');
    }

    public function markRead(Notification $notification, Request $request): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        // Optimized: Only update if actually unread
        if (! $notification->is_read) {
            $notification->forceFill(['is_read' => true])->save();
        }

        if ($notification->related_id) {
            return redirect()->route('qa.bugs.show', [
                'bug' => $notification->related_id,
                'return' => url()->previous(),
            ]);
        }

        return redirect()->route('qa.notifications');
    }

    public function destroy(Notification $notification, Request $request): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->delete();

        return back()->with('status', 'Notifikasi berhasil dihapus.');
    }
}
