<?php

namespace App\Http\Controllers\Programmer;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Services\BugStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BugWorkflowController extends Controller
{
    public function start(Bug $bug, BugStatusService $statusService, Request $request): RedirectResponse
    {
        // Only assignee can start work
        abort_unless($request->user()?->id === $bug->assignee_id, 403);

        try {
            $statusService->transition($bug, 'In Progress', $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Status bug berhasil diperbarui menjadi "Dalam Pengerjaan".');
    }

    public function sendToTesting(Bug $bug, BugStatusService $statusService, Request $request): RedirectResponse
    {
        abort_unless($request->user()?->id === $bug->assignee_id, 403);

        try {
            $statusService->transition($bug, 'Testing', $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Bug berhasil dikirim ke tahap "Pengujian".');
    }
}
