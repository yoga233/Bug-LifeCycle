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
            ->get();

        $twoDaysAgo = now()->subDays(2);

        $summary = Bug::query()
            ->leftJoin('priorities', 'bugs.priority_id', '=', 'priorities.id')
            ->leftJoin('severities', 'bugs.severity_id', '=', 'severities.id')
            ->selectRaw("SUM(CASE WHEN bugs.status = 'Testing' THEN 1 ELSE 0 END) as waiting_count")
            ->selectRaw("SUM(CASE WHEN bugs.status = 'Testing' 
                AND (UPPER(COALESCE(priorities.level, '')) IN ('URGENT','HIGH') 
                     OR UPPER(COALESCE(severities.level, '')) IN ('CRITICAL','MAJOR')) 
                THEN 1 ELSE 0 END) as attention_count")
            ->selectRaw("SUM(CASE WHEN bugs.status = 'Testing' AND bugs.created_at <= ? THEN 1 ELSE 0 END) as stalled_count", [$twoDaysAgo])
            ->selectRaw("SUM(CASE WHEN bugs.status = 'Rejected' THEN 1 ELSE 0 END) as rejected_count")
            ->first();

        $waitingCount   = (int) ($summary->waiting_count ?? 0);
        $attentionCount = (int) ($summary->attention_count ?? 0);
        $stalledCount   = (int) ($summary->stalled_count ?? 0);
        $rejectedCount  = (int) ($summary->rejected_count ?? 0);

        return view('panel.qa.testing-queue', compact(
            'bugs',
            'waitingCount',
            'attentionCount',
            'stalledCount',
            'rejectedCount',
        ));
    }
}