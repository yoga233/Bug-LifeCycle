<?php

namespace App\Http\Controllers\Programmer;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Services\BugStatusService;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BugWorkflowController extends Controller
{
    public function start(Bug $bug, BugStatusService $statusService, Request $request, TicketService $tickets): RedirectResponse|JsonResponse
    {
        // Only assignee can start work
        abort_unless($request->user()?->id === $bug->assignee_id, 403);

        try {
            $statusService->transition($bug, 'In Progress', $request->user(), $tickets);
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Status bug berhasil diperbarui menjadi "Dalam Pengerjaan".',
                'bug' => [
                    'id' => $bug->id,
                    'ticket' => $tickets->fromBugId($bug->id),
                    'status' => $bug->status,
                ],
                'timeline' => $this->getTimelineData($bug),
            ], 200);
        }

        return back()->with('status', 'Status bug berhasil diperbarui menjadi "Dalam Pengerjaan".');
    }

    public function sendToTesting(Bug $bug, BugStatusService $statusService, Request $request, TicketService $tickets): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()?->id === $bug->assignee_id, 403);

        try {
            $statusService->transition($bug, 'Testing', $request->user(), $tickets);
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Bug berhasil dikirim ke tahap "Pengujian".',
                'bug' => [
                    'id' => $bug->id,
                    'ticket' => $tickets->fromBugId($bug->id),
                    'status' => $bug->status,
                ],
                'timeline' => $this->getTimelineData($bug),
            ], 200);
        }

        return back()->with('status', 'Bug berhasil dikirim ke tahap "Pengujian".');
    }

    private function getTimelineData(Bug $bug): array
    {
        $bug->load('statusHistories');
        $histories = $bug->statusHistories->sortBy('changed_at')->values();
        $events = collect();

        $events->push([
            'status' => $histories->first()?->old_status ?? $bug->status,
            'at' => $bug->created_at?->timezone(config('app.timezone'))?->format('d M Y, H:i'),
        ]);

        foreach ($histories as $h) {
            $events->push([
                'status' => $h->new_status,
                'old_status' => $h->old_status,
                'at' => $h->changed_at?->timezone(config('app.timezone'))?->format('d M Y, H:i'),
                'is_revision' => ($h->old_status === 'Testing' && $h->new_status === 'In Progress'),
            ]);
        }

        return $events->values()->toArray();
    }
}
