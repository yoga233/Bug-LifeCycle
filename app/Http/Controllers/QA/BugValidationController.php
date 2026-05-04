<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Models\Comment;
use App\Models\Notification;
use App\Services\BugStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BugValidationController extends Controller
{
    public function approve(Bug $bug, BugStatusService $statusService, Request $request): RedirectResponse
    {
        try {
            $statusService->transition($bug, 'Resolved', $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Bug berhasil ditandai sebagai "Diselesaikan".');
    }

    public function reject(Bug $bug, BugStatusService $statusService, Request $request): RedirectResponse
    {
        // For now keep schema strict: we do not store reason in DB.
        // (Optional later: store reason as a Comment).
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = trim((string) ($validated['reason'] ?? ''));

        if ($reason !== '') {
            Comment::create([
                'bug_id' => $bug->id,
                'user_id' => $request->user()->id,
                'content' => '[QA Rejected] '.$reason,
            ]);
        }

        try {
            $statusService->transition($bug, 'In Progress', $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Always notify assignee when QA returns a bug, even without reason text.
        if ($bug->assignee_id) {
            $message = 'Bug #'.$bug->id.' dikembalikan oleh QA ke tahap Dalam Pengerjaan.';

            if ($reason !== '') {
                $message .= ' Catatan QA: '.str($reason)->limit(120);
            }

            Notification::create([
                'user_id' => $bug->assignee_id,
                'related_id' => $bug->id,
                'type' => 'BugRejected',
                'message' => (string) str($message)->limit(255),
                'is_read' => false,
                'created_at' => now(),
            ]);
        }

        return back()->with('status', 'Bug dikembalikan ke Programmer. Status menjadi "Dalam Pengerjaan" untuk revisi.');
    }
}
