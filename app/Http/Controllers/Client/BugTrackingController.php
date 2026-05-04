<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Models\GuestBugReport;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class BugTrackingController extends Controller
{
    public function show(Request $request, TicketService $tickets): View
    {
        $ticket = (string) $request->query('ticket', '');
        $ticket = trim($ticket);

        $searched = $request->has('ticket') && trim($ticket) !== '';
        $error = null;
        $bug = null;
        $guestReport = null;

        if ($searched) {
            // Check if it's a guest report ticket (GBR-)
            if (str_starts_with(strtoupper($ticket), 'GBR-')) {
                $guestReport = GuestBugReport::query()
                    ->with(['project', 'severity'])
                    ->where('ticket', strtoupper($ticket))
                    ->first();

                if (!$guestReport) {
                    $error = 'Ticket not found.';
                }
            } else {
                // It's a regular bug ticket
                try {
                    $bugId = $tickets->toBugId($ticket);

                    $bug = Bug::query()
                        ->with(['project', 'priority', 'severity', 'statusHistories' => function ($q) {
                            $q->orderBy('changed_at');
                        }])
                        ->find($bugId);

                    if (!$bug) {
                        $error = 'Ticket not found.';
                    }
                } catch (InvalidArgumentException) {
                    $error = 'Invalid ticket format. Example: BUG-8F3K2L';
                }
            }
        }

        return view('portal.tracking.index', compact('ticket', 'searched', 'error', 'bug', 'guestReport'));
    }
}
