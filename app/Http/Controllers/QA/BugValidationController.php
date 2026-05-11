<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Models\Comment;
use App\Models\Notification;
use App\Services\BugStatusService;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BugValidationController extends Controller
{
    public function approve(Bug $bug, BugStatusService $statusService, Request $request, TicketService $tickets): RedirectResponse|JsonResponse
    {
        try {
            $statusService->transition($bug, 'Resolved', $request->user(), $tickets);
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Bug berhasil ditandai sebagai "Diselesaikan".',
                'bug' => [
                    'id' => $bug->id,
                    'ticket' => $tickets->fromBugId($bug->id),
                    'status' => $bug->status,
                ],
                'timeline' => $this->getTimelineData($bug),
            ], 200);
        }

        return back()->with('status', 'Bug berhasil ditandai sebagai "Diselesaikan".');
    }

    public function reject(Bug $bug, BugStatusService $statusService, Request $request, TicketService $tickets): RedirectResponse|JsonResponse
    {
        $request->validate([
            'reason'        => ['nullable', 'string', 'max:1000'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:5120', // 5 MB per file
            ],
        ]);

        $reason = trim((string) ($request->input('reason', '')));

        // ── Simpan komentar alasan penolakan ──────────────────────────────
        if ($reason !== '') {
            Comment::create([
                'bug_id'  => $bug->id,
                'user_id' => $request->user()->id,
                'content' => '[QA Dikembalikan] '.$reason,
            ]);
        }

        // ── Simpan lampiran gambar QA ─────────────────────────────────────
        $uploadedCount = 0;
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Validasi MIME ketat di sisi server (double-check)
                if (! in_array($file->getMimeType(), [
                    'image/jpeg', 'image/png', 'image/webp', 'image/gif',
                ], true)) {
                    continue;
                }

                $path = $file->store('bug-attachments', 'public');

                if ($path) {
                    $bug->attachments()->create([
                        'uploaded_by' => $request->user()->id,
                        'file_path'   => $path,
                        'file_name'   => $file->getClientOriginalName(),
                        'file_type'   => $file->getMimeType(),
                        'file_size'   => (int) round($file->getSize() / 1024),
                    ]);
                    $uploadedCount++;
                }
            }
        }

        // ── Transisi status ───────────────────────────────────────────────
        try {
            $statusService->transition($bug, 'In Progress', $request->user(), $tickets);
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        // ── Notifikasi ke assignee ────────────────────────────────────────
        if ($bug->assignee_id) {
            $ticketId = $tickets->fromBugId($bug->id);
            $message = 'Bug #'.$ticketId.' dikembalikan oleh QA ke tahap Dalam Pengerjaan.';

            if ($reason !== '') {
                $message .= ' Catatan QA: '.str($reason)->limit(120);
            }

            if ($uploadedCount > 0) {
                $message .= ' ('.$uploadedCount.' gambar dilampirkan)';
            }

            Notification::create([
                'user_id'    => $bug->assignee_id,
                'related_id' => $bug->id,
                'type'       => 'BugRejected',
                'message'    => (string) str($message)->limit(255),
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        $statusMsg = 'Bug dikembalikan ke Programmer. Status menjadi "Dalam Pengerjaan" untuk revisi.';
        if ($uploadedCount > 0) {
            $statusMsg .= " {$uploadedCount} gambar berhasil dilampirkan.";
        }

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => $statusMsg,
                'bug' => [
                    'id' => $bug->id,
                    'ticket' => $tickets->fromBugId($bug->id),
                    'status' => $bug->status,
                ],
                'timeline' => $this->getTimelineData($bug),
            ], 200);
        }

        return back()->with('status', $statusMsg);
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
