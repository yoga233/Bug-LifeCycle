<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use Illuminate\View\View;

class TestingQueueController extends Controller
{
    public function index(): View
    {
        $bugs = Bug::query()
            ->select([
                'id',
                'project_id',
                'severity_id',
                'priority_id',
                'guest_name',
                'title',
                'description',
                'status',
                'created_at',
            ])
            ->with([
                'project:id,name',
                'priority:id,level,sla_hours,bg_color,text_color',
                'severity:id,level,bg_color,text_color',
            ])
            ->where('status', 'Testing')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Direct query without caching - let database indexes handle performance
        $summary = Bug::query()
            ->selectRaw('COUNT(*) as total_bugs')
            ->selectRaw("SUM(CASE WHEN status IN ('Reported','Assigned','In Progress','Testing') THEN 1 ELSE 0 END) as active_count")
            ->selectRaw("SUM(CASE WHEN status = 'Testing' THEN 1 ELSE 0 END) as testing_count")
            ->selectRaw("SUM(CASE WHEN status IN ('Resolved','Closed') THEN 1 ELSE 0 END) as resolved_count")
            ->first();

        $totalBugs = (int) ($summary->total_bugs ?? 0);
        $activeCount = (int) ($summary->active_count ?? 0);
        $testingCount = (int) ($summary->testing_count ?? 0);
        $resolvedCount = (int) ($summary->resolved_count ?? 0);

        return view('panel.qa.testing-queue', compact(
            'bugs',
            'totalBugs',
            'activeCount',
            'testingCount',
            'resolvedCount',
        ));
    }
}
