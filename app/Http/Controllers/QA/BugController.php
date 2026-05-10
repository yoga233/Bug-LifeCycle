<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BugController extends Controller
{
    public function show(Bug $bug, TicketService $tickets): View
    {
        // Optimized: Select specific columns to reduce data transfer
        $bug->load([
            'project:id,name,description',
            'priority:id,level,sla_hours,bg_color,text_color',
            'severity:id,level,bg_color,text_color',
            'attachments:id,bug_id,file_name,file_path,uploaded_by,created_at',
            'comments.user:id,name,email', // ✅ Hapus closure-nya, pakai string saja
            'statusHistories' => function ($q) {
                $q->select(['id', 'bug_id', 'user_id', 'old_status', 'new_status', 'changed_at'])
                ->orderByDesc('changed_at')
                ->limit(50);
            },
        ]);

        $ticket = $tickets->fromBugId($bug->id);

        return view('panel.qa.bugs.show', compact('bug', 'ticket'));
    }
}
