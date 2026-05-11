<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\GuestBugReport;
use App\Models\GuestRateLimit;
use App\Jobs\ProcessGuestBugReportJob;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GuestReportQueueController extends Controller
{
    /**
     * Display the guest report queue dashboard
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');
        
        $query = GuestBugReport::query()
            ->with(['project', 'severity'])
            ->orderBy('reported_at', 'desc');

        if ($status !== 'all') {
            $query->where('queue_status', $status);
        }

        $reports = $query->paginate(20);
        
        // Statistics
        $stats = [
            'pending' => GuestBugReport::where('queue_status', 'pending')->count(),
            'approved' => GuestBugReport::where('queue_status', 'approved')->count(),
            'rejected' => GuestBugReport::where('queue_status', 'rejected')->count(),
            'today' => GuestBugReport::whereDate('reported_at', today())->count(),
            'blocked' => GuestRateLimit::where('is_blocked', true)->count(),
        ];

        return view('panel.project-manager.guest-reports', compact('reports', 'stats', 'status'));
    }

    /**
     * Approve a guest bug report
     */
    public function approve(Request $request, GuestBugReport $guestReport, TicketService $tickets): RedirectResponse
    {
        if ($guestReport->queue_status !== 'pending') {
            return back()->with('error', 'Laporan sudah diproses sebelumnya.');
        }

        // Validate request
        $request->validate([
            'priority_id' => 'nullable|exists:priorities,id',
        ]);

        // Approve and create bug
        $bug = $guestReport->approve();

        // Update priority if provided
        if ($request->has('priority_id')) {
            $bug->update(['priority_id' => $request->input('priority_id')]);
        }

        // Move attachments if any
        $this->moveAttachments($guestReport, $bug);

        return back()->with('success', 'Laporan bug telah disetujui dan dibuat menjadi bug #' . $tickets->fromBugId($bug->id));
    }

    /**
     * Reject a guest bug report
     */
    public function reject(Request $request, GuestBugReport $guestReport): RedirectResponse
    {
        if ($guestReport->queue_status !== 'pending') {
            return back()->with('error', 'Laporan sudah diproses sebelumnya.');
        }

        $request->validate([
            'reject_reason' => 'nullable|string|max:500',
        ]);

        $reason = $request->input('reject_reason', 'Tidak memenuhi kriteria pelaporan');

        // Optionally block the guest if suspicious
        if ($request->boolean('block_guest')) {
            $rateLimit = GuestRateLimit::firstOrCreate(
                ['ip_address' => $guestReport->ip_address, 'email' => $guestReport->guest_email],
                ['first_report_at' => now(), 'last_report_at' => now()]
            );
            $rateLimit->block($reason, 60 * 24 * 7); // Block for 7 days
        }

        $guestReport->reject($reason);

        return back()->with('success', 'Laporan bug telah ditolak.');
    }

    /**
     * Bulk approve selected reports
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:guest_bug_reports,id',
        ]);

        $count = 0;
        foreach ($request->input('report_ids') as $id) {
            $report = GuestBugReport::find($id);
            if ($report && $report->queue_status === 'pending') {
                $bug = $report->approve();
                $this->moveAttachments($report, $bug);
                $count++;
            }
        }

        return back()->with('success', $count . ' laporan bug telah disetujui.');
    }

    /**
     * Bulk reject selected reports
     */
    public function bulkReject(Request $request): RedirectResponse
    {
        $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:guest_bug_reports,id',
            'reject_reason' => 'nullable|string|max:500',
        ]);

        $count = 0;
        $reason = $request->input('reject_reason', 'Ditolak melalui bulk action');
        
        foreach ($request->input('report_ids') as $id) {
            $report = GuestBugReport::find($id);
            if ($report && $report->queue_status === 'pending') {
                $report->reject($reason);
                $count++;
            }
        }

        return back()->with('success', $count . ' laporan bug telah ditolak.');
    }

    /**
     * View report details
     */
    public function show(GuestBugReport $guestReport): View
    {
        $guestReport->load(['project', 'severity']);

        return view('panel.project-manager.guest-report-detail', compact('guestReport'));
    }

    /**
     * Move attachments from guest report to bug
     */
    private function moveAttachments(GuestBugReport $guestReport, $bug): void
    {
        // Check if there are attachment IDs stored in pm_notes
        if ($guestReport->pm_notes) {
            $data = json_decode($guestReport->pm_notes, true);
            if (isset($data['attachment_ids']) && is_array($data['attachment_ids'])) {
                foreach ($data['attachment_ids'] as $attachmentId) {
                    $attachment = \App\Models\Attachment::find($attachmentId);
                    if ($attachment) {
                        $attachment->update(['bug_id' => $bug->id]);
                    }
                }
            }
        }
    }
}
