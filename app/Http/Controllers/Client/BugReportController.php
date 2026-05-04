<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessGuestBugReportJob;
use App\Models\Attachment;
use App\Models\Bug;
use App\Models\BugStatusHistory;
use App\Models\GuestBugReport;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;
use App\Mail\BugReportedTicketMail;
use App\Services\GuestSpamProtectionService;
use App\Services\TicketService;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class BugReportController extends Controller
{
    // Enable queue mode for hybrid architecture (PM validation before bug is created)
    // Set to false to use direct mode (instant bug creation)
    private const USE_QUEUE_MODE = false;

    public function create(): View
    {
        $projects = Project::query()->orderBy('name')->get();
        // Use cached severity for better performance
        $severities = app('cached_severities');

        return view('portal.report.index', compact('projects', 'severities'));
    }

    public function store(Request $request): RedirectResponse
    {
        app()->setLocale($this->resolveClientPortalLang($request));

        // Check rate limit first
        $spamProtection = app(GuestSpamProtectionService::class);

        $email = (string) $request->input('guest_email', '');
        if ($email) {
            $rateCheck = $spamProtection->checkRateLimit($request, $email);
            if (!$rateCheck['allowed']) {
                return back()
                    ->withInput()
                    ->with('error', $rateCheck['reason']);
            }
        }

        $validator = Validator::make(
            $request->all(),
            $this->reportValidationRules(),
            $this->reportValidationMessages(),
            $this->reportValidationAttributes(),
        );

        if ($validator->fails()) {
            return back()
                ->withInput()
                ->withErrors($validator)
                ->with('report_error_meta', $this->buildReportValidationErrorMeta($validator));
        }

        $validated = $validator->validated();

        // Record the report for rate limiting
        if ($email) {
            $spamProtection->recordReport($request, $email);
        }

        // Use queue mode or direct mode based on configuration
        if (self::USE_QUEUE_MODE) {
            return $this->storeViaQueue($validated, $request);
        }

        return $this->storeDirect($validated, $request);
    }

    private function resolveClientPortalLang(Request $request): string
    {
        $lang = (string) $request->session()->get('client_portal_lang', 'en');

        return in_array($lang, ['en', 'id'], true) ? $lang : 'en';
    }

    private function reportValidationRules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:100'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_version' => ['required', 'string', 'max:50'],

            'project_id' => ['required', 'exists:projects,id'],
            'severity_id' => ['required', 'exists:severities,id'],

            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'reproduction_steps' => ['required', 'string'],
            'frequency' => ['required', 'in:once,rare,frequent,random'],

            'attachments' => ['required', 'array', 'max:5'],
            'attachments.*' => ['required', 'file', 'max:5120', 'mimes:png,jpg,jpeg,pdf,txt,log'],
        ];
    }

    private function reportValidationMessages(): array
    {
        return [
            'required' => __('client-report.validation.required'),
            'email' => __('client-report.validation.email'),
            'exists' => __('client-report.validation.exists'),
            'in' => __('client-report.validation.in'),
            'max.string' => __('client-report.validation.max_string'),
            'max.array' => __('client-report.validation.max_array'),
            'file' => __('client-report.validation.file'),
            'mimes' => __('client-report.validation.mimes'),
            'attachments.*.max' => __('client-report.validation.attachment_item_max'),
        ];
    }

    private function reportValidationAttributes(): array
    {
        return [
            'guest_name' => __('client-report.attributes.guest_name'),
            'guest_email' => __('client-report.attributes.guest_email'),
            'guest_version' => __('client-report.attributes.guest_version'),
            'project_id' => __('client-report.attributes.project_id'),
            'severity_id' => __('client-report.attributes.severity_id'),
            'title' => __('client-report.attributes.title'),
            'description' => __('client-report.attributes.description'),
            'reproduction_steps' => __('client-report.attributes.reproduction_steps'),
            'frequency' => __('client-report.attributes.frequency'),
            'attachments' => __('client-report.attributes.attachments'),
            'attachments.*' => __('client-report.attributes.attachments_item'),
        ];
    }

    private function reportErrorFieldLabelKeys(): array
    {
        return [
            'guest_name' => 'report_field_label_guest_name',
            'guest_email' => 'report_field_label_guest_email',
            'guest_version' => 'report_field_label_guest_version',
            'project_id' => 'report_field_label_project',
            'severity_id' => 'report_field_label_severity',
            'title' => 'report_field_label_title',
            'description' => 'report_field_label_description',
            'reproduction_steps' => 'report_field_label_reproduction_steps',
            'frequency' => 'report_field_label_frequency',
            'attachments' => 'report_field_label_attachments',
            'attachments.*' => 'report_field_label_attachments',
        ];
    }

    private function normalizeValidationFieldKey(string $field): string
    {
        if (preg_match('/^attachments\.\d+$/', $field) === 1) {
            return 'attachments.*';
        }

        return $field;
    }

    private function mapValidationRuleToClientMessageKey(string $field, string $ruleName): ?string
    {
        $rule = strtolower($ruleName);

        return match ($rule) {
            'required' => in_array($field, ['attachments', 'attachments.*'], true)
                ? 'report_validation_attachment_required'
                : 'report_validation_required',
            'email' => 'report_validation_invalid_email',
            'exists' => 'report_validation_exists',
            'in' => 'report_validation_invalid_option',
            'max' => match ($field) {
                'attachments' => 'report_validation_max_attachments',
                'attachments.*' => 'report_validation_attachment_file_max',
                default => 'report_validation_max_characters',
            },
            'file' => 'report_validation_file',
            'mimes' => 'report_validation_mimes',
            default => null,
        };
    }

    private function buildReportValidationReplacements(string $field, string $ruleName, array $ruleArguments): array
    {
        $rule = strtolower($ruleName);

        if ($rule === 'mimes') {
            if (empty($ruleArguments)) {
                return [];
            }

            return [
                'values' => implode(', ', array_map(static fn ($value) => strtoupper((string) $value), $ruleArguments)),
            ];
        }

        if ($rule !== 'max') {
            return [];
        }

        $rawMax = $ruleArguments[0] ?? null;
        if ($rawMax === null || $rawMax === '') {
            return [];
        }

        if ($field === 'attachments') {
            return ['count' => (string) $rawMax];
        }

        if ($field === 'attachments.*') {
            $maxKb = (int) $rawMax;
            $sizeMb = $maxKb > 0 ? (string) max(1, (int) round($maxKb / 1024)) : '5';

            return ['size' => $sizeMb];
        }

        return ['max' => (string) $rawMax];
    }

    private function buildReportValidationErrorMeta(ValidatorContract $validator): array
    {
        $meta = [];
        $fieldLabelKeys = $this->reportErrorFieldLabelKeys();

        foreach ($validator->failed() as $field => $failedRules) {
            $normalizedField = $this->normalizeValidationFieldKey((string) $field);

            if (isset($meta[$normalizedField])) {
                continue;
            }

            $ruleName = array_key_first($failedRules);
            if (! is_string($ruleName) || $ruleName === '') {
                continue;
            }

            $messageKey = $this->mapValidationRuleToClientMessageKey($normalizedField, $ruleName);
            if (! is_string($messageKey) || $messageKey === '') {
                continue;
            }

            $ruleArguments = $failedRules[$ruleName] ?? [];
            if (! is_array($ruleArguments)) {
                $ruleArguments = [];
            }

            $meta[$normalizedField] = [
                'message_key' => $messageKey,
                'field_label_key' => $fieldLabelKeys[$normalizedField] ?? null,
                'replacements' => $this->buildReportValidationReplacements($normalizedField, $ruleName, $ruleArguments),
            ];
        }

        return $meta;
    }

    /**
     * Store bug directly to database (no queue)
     */
    private function storeDirect(array $validated, Request $request): RedirectResponse
    {
        $bug = DB::transaction(function () use ($validated, $request) {
            $description = (string) $validated['description'];
            $repro = trim((string) ($validated['reproduction_steps'] ?? ''));
            if ($repro !== '') {
                $description .= "\n\nLangkah Reproduksi:\n".$repro;
            }

            /** @var Bug $bug */
            $bug = Bug::create([
                'project_id' => $validated['project_id'],
                'severity_id' => $validated['severity_id'],
                'priority_id' => null,

                'guest_name' => $validated['guest_name'],
                'guest_email' => $validated['guest_email'],
                'guest_version' => $validated['guest_version'],

                'title' => $validated['title'],
                'description' => $description,
                'frequency' => $validated['frequency'],

                'status' => 'Reported',
            ]);

            BugStatusHistory::create([
                'bug_id' => $bug->id,
                'user_id' => null,
                'old_status' => 'Reported',
                'new_status' => 'Reported',
                'changed_at' => now(),
            ]);

            $files = $request->file('attachments', []);
            foreach ($files as $file) {
                if (! $file) {
                    continue;
                }

                $storedPath = $file->store('bug-attachments', 'public');

                Attachment::create([
                    'bug_id' => $bug->id,
                    'uploaded_by' => null,
                    'file_path' => $storedPath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => (string) $file->getClientMimeType(),
                    'file_size' => (int) ceil($file->getSize() / 1024), // KB
                ]);
            }

            // Notify Project Manager that a new report has arrived
            $pmIds = User::query()
                ->where('is_active', true)
                ->whereHas('roles', fn ($q) => $q->where('name', 'Project Manager'))
                ->pluck('id');

            foreach ($pmIds as $pmId) {
                Notification::create([
                    'user_id' => $pmId,
                    'related_id' => $bug->id,
                    'type' => 'BugReported',
                    'message' => 'Bug dilaporkan: Bug #'.$bug->id.' - '.$bug->title,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }

            return $bug;
        });

        $ticket = app(TicketService::class)->fromBugId($bug->id);

        // Send ticket via email because guest does not login (so ticket isn't lost)
        $trackingUrl = route('client.tracking', ['ticket' => $ticket]);
        Mail::to($bug->guest_email)->send(new BugReportedTicketMail(
            ticket: $ticket,
            guestName: $bug->guest_name,
            title: $bug->title,
            trackingUrl: $trackingUrl,
        ));

        return redirect()
            ->route('client.report.success', ['ticket' => $ticket])
            ->with('report_email', $bug->guest_email);
    }

    /**
     * Store bug via queue (hybrid mode - PM validates first)
     */
    private function storeViaQueue(array $validated, Request $request): RedirectResponse
    {
        $description = (string) $validated['description'];
        $repro = trim((string) ($validated['reproduction_steps'] ?? ''));
        if ($repro !== '') {
            $description .= "\n\nLangkah Reproduksi:\n".$repro;
        }

        // Generate unique ticket for guest tracking
        $ticket = 'GBR-' . strtoupper(substr(md5(uniqid(microtime())), 0, 8));

        // Store in guest_bug_reports queue
        $guestReport = GuestBugReport::create([
            'ticket' => $ticket,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_version' => $validated['guest_version'],
            'project_id' => $validated['project_id'],
            'severity_id' => $validated['severity_id'],
            'title' => $validated['title'],
            'description' => $description,
            'reproduction_steps' => $repro,
            'frequency' => $validated['frequency'],
            'queue_status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reported_at' => now(),
        ]);

        // Handle attachments - store temporarily
        $files = $request->file('attachments', []);
        $attachmentIds = [];
        foreach ($files as $file) {
            if (! $file) {
                continue;
            }

            $storedPath = $file->store('bug-attachments', 'public');

            $attachment = Attachment::create([
                'bug_id' => null, // Will be linked after approval
                'uploaded_by' => null,
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => (string) $file->getClientMimeType(),
                'file_size' => (int) ceil($file->getSize() / 1024),
            ]);
            $attachmentIds[] = $attachment->id;
        }

        // Store attachment IDs in report for later linking
        if (!empty($attachmentIds)) {
            $guestReport->update(['pm_notes' => json_encode(['attachment_ids' => $attachmentIds])]);
        }

        // Send confirmation email to guest
        $trackingUrl = route('client.tracking', ['ticket' => $ticket]);
        
        // Use a simpler email for queue mode
        Mail::send([], [], function ($message) use ($validated, $ticket, $trackingUrl) {
            $message->to($validated['guest_email'])
                ->subject('Laporan Bug Diterima - ' . $ticket)
                ->html("
                    <h2>Laporan Bug Diterima</h2>
                    <p>Halo {$validated['guest_name']},</p>
                    <p>Laporan bug Anda telah diterima dan sedang dalam proses peninjauan.</p>
                    <p><strong>Nomor Tiket:</strong> {$ticket}</p>
                    <p><strong>Judul:</strong> {$validated['title']}</p>
                    <p>Anda dapat melacak status laporan Anda di:</p>
                    <p><a href='{$trackingUrl}'>{$trackingUrl}</a></p>
                    <p>Tim kami akan memproses laporan ini setelah ditinjau oleh Project Manager.</p>
                    <p>Terima kasih atas laporan Anda!</p>
                ");
        });

        return redirect()
            ->route('client.report.success', ['ticket' => $ticket])
            ->with('report_email', $validated['guest_email'])
            ->with('info', 'Laporan Anda sedang dalam proses peninjauan. Anda akan notified setelah disetujui.');
    }
}
