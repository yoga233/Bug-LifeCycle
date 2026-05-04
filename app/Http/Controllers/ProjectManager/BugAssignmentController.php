<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Models\Notification;
use App\Models\User;
use App\Services\BugStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BugAssignmentController extends Controller
{
    public function updatePriority(Bug $bug, Request $request): RedirectResponse|JsonResponse
    {
        // Priority can only be set/changed while bug is still freshly reported.
        if ($bug->status !== 'Reported') {
            $msg = 'Prioritas hanya bisa diubah ketika status bug masih "Dilaporkan".';

            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'priority_id' => ['required', 'integer', 'exists:priorities,id'],
        ]);

        $bug->forceFill([
            'priority_id' => (int) $validated['priority_id'],
        ])->save();

        $bug->load('priority');

        $message = 'Prioritas bug #'.$bug->id.' diset ke '.$bug->priority?->level.'.';

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'bug' => [
                    'id' => $bug->id,
                    'status' => $bug->status,
                    'priority' => [
                        'id' => $bug->priority?->id,
                        'level' => $bug->priority?->level,
                        'sla_hours' => $bug->priority?->sla_hours,
                        'bg_color' => $bug->priority?->bg_color,
                        'text_color' => $bug->priority?->text_color,
                    ],
                ],
            ], 200);
        }

        return back()->with('status', $message);
    }

    public function assign(Bug $bug, Request $request, BugStatusService $statusService): RedirectResponse|JsonResponse
    {
        // Rules:
        // - Assign/Reassign is allowed only when bug is Reported or Assigned (early stage)
        // - Once In Progress or beyond, assignee should not be changed here.
        if (! in_array($bug->status, ['Reported', 'Assigned'], true)) {
            $msg = 'Tidak dapat melakukan penugasan ulang ketika status bug sudah masuk tahap pengerjaan/pengujian/selesai.';

            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        // New workflow: priority is decided by PM first before first assignment.
        if ($bug->status === 'Reported' && ! $bug->priority_id) {
            $msg = 'Tentukan prioritas terlebih dahulu pada detail bug sebelum menugaskan programmer.';

            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'assignee_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        // Only allow assigning to active programmers
        $assignee = User::query()
            ->whereKey($validated['assignee_id'])
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Programmer'))
            ->first();

        if (! $assignee) {
            $msg = __('messages.assignment.assignee_must_be_active_programmer');
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $bug->forceFill(['assignee_id' => $assignee->id]);
        $bug->save();

        // Move Reported -> Assigned and write history
        try {
            if ($bug->status === 'Reported') {
                $statusService->transition($bug, 'Assigned', $request->user());
            }
        } catch (InvalidArgumentException $e) {
            $msg = $e->getMessage();
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        // Notify assignee (DB-backed notifications table)
        Notification::create([
            'user_id' => $assignee->id,
            'related_id' => $bug->id,
            'type' => 'BugAssigned',
            'message' => __('messages.assignment.bug_assigned_notification', ['id' => $bug->id, 'title' => $bug->title]),
            'is_read' => false,
            'created_at' => now(),
        ]);

        $msg = 'Bug #'.$bug->id.' berhasil ditugaskan ke '.$assignee->name.'.';

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => $msg,
                'bug' => [
                    'id' => $bug->id,
                    'status' => $bug->status,
                    'assignee' => [
                        'id' => $assignee->id,
                        'name' => $assignee->name,
                    ],
                ],
            ], 200);
        }

        return back()->with('status', $msg);
    }

    public function unassign(Bug $bug, Request $request, BugStatusService $statusService): RedirectResponse|JsonResponse
    {
        // Rules:
        // - Unassign only allowed when status is Assigned (not started yet)
        if ($bug->status !== 'Assigned') {
            $msg = 'Unassign hanya diperbolehkan ketika status bug masih "Assigned".';
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $bug->forceFill(['assignee_id' => null]);
        $bug->save();

        try {
            $statusService->transition($bug, 'Reported', $request->user());
        } catch (InvalidArgumentException $e) {
            $msg = $e->getMessage();
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $msg = 'Penugasan bug dibatalkan dan status dikembalikan menjadi "Dilaporkan".';

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => $msg,
                'bug' => [
                    'id' => $bug->id,
                    'status' => $bug->status,
                    'assignee' => null,
                ],
            ], 200);
        }

        return back()->with('status', $msg);
    }
}
