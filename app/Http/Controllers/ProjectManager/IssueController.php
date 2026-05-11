<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class IssueController extends Controller
{
    public function index(Request $request, TicketService $tickets): View
    {
        $statuses = [
            'Reported',
            'Assigned',
            'In Progress',
            'Testing',
            'Resolved',
            'Closed',
            'Rejected',
        ];

        $query = Bug::query()
            ->select([
                'id',
                'project_id',
                'severity_id',
                'priority_id',
                'assignee_id',
                'guest_name',
                'title',
                'description',
                'status',
                'created_at',
                'updated_at',
            ])
            ->with([
                'project:id,name',
                'priority:id,level,sla_hours,bg_color,text_color',
                'severity:id,level,bg_color,text_color',
                'assignee:id,name',
            ])
            ->latest();

        // Filters (FR-11)
        $projectId = $request->query('project_id');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $status = $request->query('status');
        if ($status && in_array($status, $statuses, true)) {
            $query->where('status', $status);
        }

        $priorityId = $request->query('priority_id');
        if ($priorityId) {
            $query->where('priority_id', $priorityId);
        }

        $assigneeParam = $request->query('assignee_id');
        if ($assigneeParam === 'unassigned') {
            $query->whereNull('assignee_id');
        } elseif ($assigneeParam) {
            $query->where('assignee_id', $assigneeParam);
        }

        // Search (title or #TicketID)
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $maybe = ltrim($q, '#');

            // Allow searching by raw numeric id: "123" or "#123"
            if (preg_match('/^\d{1,10}$/', $maybe)) {
                $query->whereKey((int) $maybe);
            } else {
                // Ticket format: BUG-XXXXXX / BUG-000123 (legacy)
                try {
                    if (preg_match('/^BUG\-/i', $maybe)) {
                        $bugId = $tickets->toBugId($maybe);
                        $query->whereKey($bugId);
                    } else {
                        $query->where('title', 'like', '%'.$q.'%');
                    }
                } catch (InvalidArgumentException $e) {
                    $query->where('title', 'like', '%'.$q.'%');
                }
            }
        }

        // PM Issues page: keep the list compact (5 items per page).
        $bugs = $query->paginate(8)->withQueryString();

        // Derive ticket display for UI + search affordance
        $bugs->getCollection()->transform(function (Bug $bug) use ($tickets) {
            $bug->setAttribute('ticket', $tickets->fromBugId($bug->id));

            return $bug;
        });

        // Filter options - use cached data for better performance
        $projects = Project::query()->orderBy('name')->get(['id', 'name']);
        $priorities = app('cached_priorities');
        $assignees = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($r) => $r->where('name', 'Programmer'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $filters = [
            'project_id' => $projectId,
            'status' => $status,
            'priority_id' => $priorityId,
            'assignee_id' => $assigneeParam,
            'q' => $q,
        ];

        return view('panel.project-manager.issues.index', compact(
            'bugs',
            'projects',
            'priorities',
            'assignees',
            'statuses',
            'filters',
        ));
    }

    public function show(Bug $bug, TicketService $tickets): View
    {
        $bug->load([
            'project:id,name',
            'priority:id,level,sla_hours,bg_color,text_color',
            'severity:id,level,bg_color,text_color',
            'assignee:id,name',
            'attachments:id,bug_id,uploaded_by,comment_id,file_path,file_name,file_type,file_size,created_at',
            'comments' => fn ($q) => $q
                ->select(['id', 'bug_id', 'user_id', 'content', 'type', 'created_at'])
                ->orderByDesc('created_at'),
            'comments.user:id,name',
            'statusHistories' => fn ($q) => $q
                ->select(['id', 'bug_id', 'user_id', 'old_status', 'new_status', 'changed_at'])
                ->orderByDesc('changed_at'),
        ]);

        $ticket = $tickets->fromBugId($bug->id);

        // Used by assign dropdown in the detail page.
        // Keep query in controller (avoid querying in Blade).
        $programmers = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Programmer'))
            ->orderBy('name')
            ->get(['id', 'name']);

        // Use cached priorities for better performance
        $priorities = app('cached_priorities');

        return view('panel.project-manager.issues.show', compact('bug', 'ticket', 'programmers', 'priorities'));
    }
}
