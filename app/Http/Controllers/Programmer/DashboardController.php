<?php

namespace App\Http\Controllers\Programmer;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Fokus workspace programmer: hanya antrean kerja aktif.
        $activeStatuses = ['Assigned', 'In Progress', 'Testing', 'Rejected'];
        $sevenDaysAgo = now()->subDays(7);

        // Workspace list: hanya status aktif tanpa fitur filter.
        $tasksQuery = Bug::query()
            ->select([
                'id',
                'project_id',
                'priority_id',
                'assignee_id',
                'guest_name',
                'title',
                'status',
                'created_at',
                'updated_at',
            ])
            ->with([
                'project:id,name',
                'priority:id,level,sla_hours,bg_color,text_color',
            ])
            ->where('assignee_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->withCount(['comments as rejection_comments_count' => function($q) {
                $q->where('type', 'rejection');
            }])
            ->latest(); // <-- Ubah bagian ini

        $tasks = $tasksQuery
            ->get();

        // Add computed SLA due_at for rendering (keep as view-only attribute)
        $tasks->transform(function (Bug $bug) {
            $sla = $bug->priority?->sla_hours;
            $bug->due_at = $sla ? $bug->created_at->copy()->addHours($sla) : null;

            return $bug;
        });

        // Summary cards (counts)
        // Gunakan conditional aggregate agar tidak menembak query count berkali-kali.
        $summary = Bug::query()
            ->where('assignee_id', $user->id)
            ->selectRaw(
                "SUM(CASE WHEN status IN ('Assigned','In Progress','Testing','Rejected') THEN 1 ELSE 0 END) as total_tasks"
            )
            ->selectRaw("SUM(CASE WHEN status = 'Assigned' THEN 1 ELSE 0 END) as assigned_count")
            ->selectRaw("SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_count")
            ->selectRaw("SUM(CASE WHEN status = 'Testing' THEN 1 ELSE 0 END) as testing_count")
            ->selectRaw(
                "SUM(CASE WHEN status IN ('Resolved','Closed') AND updated_at >= ? THEN 1 ELSE 0 END) as resolved_this_week",
                [$sevenDaysAgo]
            )
            ->first();

        $totalTasks = (int) ($summary->total_tasks ?? 0);
        $assignedCount = (int) ($summary->assigned_count ?? 0);
        $inProgressCount = (int) ($summary->in_progress_count ?? 0);
        $testingCount = (int) ($summary->testing_count ?? 0);
        $resolvedThisWeek = (int) ($summary->resolved_this_week ?? 0);

        return view('panel.programmer.dashboard', [
            'userName' => $user->name,
            'tasks' => $tasks,
            'totalTasks' => $totalTasks,
            'assignedCount' => $assignedCount,
            'inProgressCount' => $inProgressCount,
            'testingCount' => $testingCount,
            'resolvedThisWeek' => $resolvedThisWeek,
        ]);
    }

    public function show(Bug $bug, TicketService $tickets, Request $request): View
    {
        // Only assignee can view in programmer panel (simple policy)
        abort_unless($request->user()?->id === $bug->assignee_id, 403);

        // Pre-load all needed users first to prevent duplicate queries
        // Get all unique user IDs: assignee + all comment authors
        $userIds = collect([$bug->assignee_id])->filter();
        
        // Get user IDs from comments without loading them yet
        $commentUserIds = \App\Models\Comment::where('bug_id', $bug->id)->pluck('user_id')->filter()->unique();
        $userIds = $userIds->concat($commentUserIds)->unique()->values();

        // Load all needed users in ONE query
        $users = User::query()->whereIn('id', $userIds)->get()->keyBy('id');

        // Now load bug relations without triggering additional user queries
        $bug->load([
            'project',
            'priority',
            'severity',
            'assignee',
            'attachments',
            'statusHistories' => function ($q) {
                $q->orderByDesc('changed_at');
            },
        ]);

        // Manually load comments with pre-loaded users to avoid duplicate queries
        $comments = \App\Models\Comment::where('bug_id', $bug->id)
            ->get()
            ->map(function ($comment) use ($users) {
                // Attach the pre-loaded user to each comment
                if ($comment->user_id && $users->has($comment->user_id)) {
                    $comment->setRelation('user', $users[$comment->user_id]);
                }
                return $comment;
            });
        
        // Set the comments collection manually
        $bug->setRelation('comments', $comments);

        $ticket = $tickets->fromBugId($bug->id);

        return view('panel.programmer.bugs.show', compact('bug', 'ticket'));
    }
}
