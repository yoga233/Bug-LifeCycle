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
            $statusService->transition($bug, 'In Progress', $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        // ── Notifikasi ke assignee ────────────────────────────────────────
        if ($bug->assignee_id) {
            $message = 'Bug #'.$bug->id.' dikembalikan oleh QA ke tahap Dalam Pengerjaan.';

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

        return back()->with('status', $statusMsg);
    }
}
