<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IssueCommentController extends Controller
{
    public function store(Bug $bug, Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $comment = Comment::create([
            'bug_id' => $bug->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
        ]);

        // For AJAX comment submission we return JSON (no page reload).
        // Use broader detection than expectsJson() to avoid accidental redirect responses.
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Komentar berhasil ditambahkan.',
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user_name' => $user->name,
                    'user_initial' => strtoupper(substr($user->name ?? 'U', 0, 1)),
                    'created_at' => $comment->created_at
                        ?->timezone(config('app.timezone'))
                        ?->format('d M Y, H:i'),
                ],
            ], 201);
        }

        // UX: redirect with anchor so the browser returns to the comment area
        // instead of landing at the top of the page after a POST-redirect.
        return redirect()
            ->route('pm.issues.show', $bug)
            ->withFragment('comment-form')
            ->with('status', 'Komentar berhasil ditambahkan.');
    }
}
