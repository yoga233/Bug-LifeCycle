@php
    $fieldBase = 'report-field-input block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm transition focus:outline-none';
    $fieldError = 'report-field-input-error';
    $fieldNormal = 'report-field-input-normal';
    $reportErrorMeta = session('report_error_meta', []);
    $reportLang = $clientPortalLang ?? 'en';
    $isId = $reportLang === 'id';
@endphp

<section class="report-shell">
    <div class="wrap">
        @if (session('error'))
            <div class="mb-6 report-alert report-alert-danger" role="alert" aria-live="polite">
                <span class="report-alert-icon" aria-hidden="true">
                    <x-lucide name="alert-circle" class="h-4 w-4" />
                </span>
                <div class="min-w-0">
                    <p class="report-alert-subtitle">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="report-flow">
            <form
                id="clientReportForm"
                method="POST"
                action="{{ route('client.report.store') }}"
                enctype="multipart/form-data"
                novalidate
                data-has-server-errors="{{ $errors->any() ? 'true' : 'false' }}"
                data-report-error-meta='@json($reportErrorMeta)'
            >
                @csrf

                <div class="report-form-stack">

                    {{-- ── Header ── --}}
                    <header class="report-card report-card-brand">
                        <div class="report-card-brand-inner">
                            <span class="report-card-kicker" data-report-i18n="report_form_kicker">Public Bug Report</span>

                            <h1 class="report-card-title" data-report-i18n="report_form_title">
                                Tell us what happened
                            </h1>

                            <p class="report-card-subtitle" data-report-i18n="report_form_subtitle">
                                Fill in each section clearly. The more detail you provide, the faster we can fix it.
                            </p>

                            <div class="report-card-meta" role="list" aria-label="Form guidance">
                                <div class="report-card-meta-item" role="listitem">
                                    <span class="report-card-meta-dot" aria-hidden="true"></span>
                                    <span data-report-i18n="report_meta_all_required">All fields are required</span>
                                </div>
                                <div class="report-card-meta-item" role="listitem">
                                    <span class="report-card-meta-dot" aria-hidden="true"></span>
                                    <span data-report-i18n="report_hero_tag_2">Screenshot and annotation support</span>
                                </div>
                                <div class="report-card-meta-item" role="listitem">
                                    <span class="report-card-meta-dot" aria-hidden="true"></span>
                                    <span data-report-i18n="report_hero_tag_3">Ticket ID sent by email</span>
                                </div>
                            </div>
                        </div>
                    </header>

                    {{-- ── Reporter Information ── --}}
                    <section class="report-card report-card-section">
                        <div class="report-card-body">
                            <h2 class="report-section-title" data-report-i18n="report_section_reporter">Your Information</h2>

                            <div class="report-field-grid">
                                <div class="report-field">
                                    <label for="guest_name" class="report-field-label">
                                        <span data-report-i18n="report_label_guest_name">Full Name</span>
                                    </label>
                                    <input
                                        id="guest_name"
                                        name="guest_name"
                                        value="{{ old('guest_name') }}"
                                        data-required="true"
                                        data-field-label="Full name"
                                        data-report-i18n-field-label="report_field_label_guest_name"
                                        data-error-id="error-guest_name"
                                        class="{{ $fieldBase }} {{ $errors->has('guest_name') ? $fieldError : $fieldNormal }}"
                                        aria-invalid="{{ $errors->has('guest_name') ? 'true' : 'false' }}"
                                    />
                                    @error('guest_name')
                                        <p id="error-guest_name" class="report-field-error report-error-text" data-report-error-field="guest_name">{{ $message }}</p>
                                    @else
                                        <p id="error-guest_name" class="report-field-error report-error-text hidden"></p>
                                    @enderror
                                </div>

                                <div class="report-field">
                                    <label for="guest_email" class="report-field-label">
                                        <span data-report-i18n="report_label_guest_email">Email Address</span>
                                    </label>
                                    <input
                                        id="guest_email"
                                        name="guest_email"
                                        type="email"
                                        value="{{ old('guest_email') }}"
                                        data-required="true"
                                        data-field-label="Email address"
                                        data-report-i18n-field-label="report_field_label_guest_email"
                                        data-error-id="error-guest_email"
                                        class="{{ $fieldBase }} {{ $errors->has('guest_email') ? $fieldError : $fieldNormal }}"
                                        aria-invalid="{{ $errors->has('guest_email') ? 'true' : 'false' }}"
                                    />
                                    <p class="report-field-hint" data-report-i18n="report_guest_email_hint">
                                        Your ticket ID and status updates will be sent here.
                                    </p>
                                    @error('guest_email')
                                        <p id="error-guest_email" class="report-field-error report-error-text" data-report-error-field="guest_email">{{ $message }}</p>
                                    @else
                                        <p id="error-guest_email" class="report-field-error report-error-text hidden"></p>
                                    @enderror
                                </div>

                                <div class="report-field">
                                    <label for="guest_company" class="report-field-label">
                                        <span data-report-i18n="report_label_guest_company">Company / Organization</span>
                                    </label>
                                    <input
                                        id="guest_company"
                                        name="guest_company"
                                        value="{{ old('guest_company') }}"
                                        data-required="true"
                                        data-field-label="Company / Organization"
                                        data-report-i18n-field-label="report_field_label_guest_company"
                                        data-error-id="error-guest_company"
                                        class="{{ $fieldBase }} {{ $errors->has('guest_company') ? $fieldError : $fieldNormal }}"
                                        placeholder="PT Example Indonesia"
                                        data-report-i18n-placeholder="report_placeholder_guest_company"
                                        aria-invalid="{{ $errors->has('guest_company') ? 'true' : 'false' }}"
                                    />
                                    @error('guest_company')
                                        <p id="error-guest_company" class="report-field-error report-error-text" data-report-error-field="guest_company">{{ $message }}</p>
                                    @else
                                        <p id="error-guest_company" class="report-field-error report-error-text hidden"></p>
                                    @enderror
                                </div>

                                <div class="report-field">
                                    <label for="guest_position" class="report-field-label">
                                        <span data-report-i18n="report_label_guest_position">Job Title</span>
                                    </label>
                                    <input
                                        id="guest_position"
                                        name="guest_position"
                                        value="{{ old('guest_position') }}"
                                        data-required="true"
                                        data-field-label="Job title"
                                        data-report-i18n-field-label="report_field_label_guest_position"
                                        data-error-id="error-guest_position"
                                        class="{{ $fieldBase }} {{ $errors->has('guest_position') ? $fieldError : $fieldNormal }}"
                                        placeholder="QA Engineer"
                                        data-report-i18n-placeholder="report_placeholder_guest_position"
                                        aria-invalid="{{ $errors->has('guest_position') ? 'true' : 'false' }}"
                                    />
                                    @error('guest_position')
                                        <p id="error-guest_position" class="report-field-error report-error-text" data-report-error-field="guest_position">{{ $message }}</p>
                                    @else
                                        <p id="error-guest_position" class="report-field-error report-error-text hidden"></p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ── Issue Context ── --}}
                    <section class="report-card report-card-section">
                        <div class="report-card-body">
                            <h2 class="report-section-title" data-report-i18n="report_section_bug_details">Issue Context</h2>

                            <div class="report-field-grid">
                                <div class="report-field">
                                    <label for="guest_version" class="report-field-label">
                                        <span data-report-i18n="report_label_guest_version">App Version</span>
                                    </label>
                                    <input
                                        id="guest_version"
                                        name="guest_version"
                                        value="{{ old('guest_version') }}"
                                        data-required="true"
                                        data-field-label="App version"
                                        data-report-i18n-field-label="report_field_label_guest_version"
                                        data-error-id="error-guest_version"
                                        class="{{ $fieldBase }} {{ $errors->has('guest_version') ? $fieldError : $fieldNormal }}"
                                        placeholder="v2.14.3 or Chrome Browser"
                                        data-report-i18n-placeholder="report_placeholder_guest_version"
                                        aria-invalid="{{ $errors->has('guest_version') ? 'true' : 'false' }}"
                                    />
                                    @error('guest_version')
                                        <p id="error-guest_version" class="report-field-error report-error-text" data-report-error-field="guest_version">{{ $message }}</p>
                                    @else
                                        <p id="error-guest_version" class="report-field-error report-error-text hidden"></p>
                                    @enderror
                                </div>

                                <div class="report-field">
                                    <label for="project_id" class="report-field-label">
                                        <span data-report-i18n="report_label_project">Project</span>
                                    </label>
                                    <div
                                        class="report-select-wrapper {{ $errors->has('project_id') ? 'report-select-wrapper-error' : '' }}"
                                        data-required-select="true"
                                        data-field-name="project_id"
                                        data-field-label="Project"
                                        data-report-i18n-field-label="report_field_label_project"
                                        data-report-select-placeholder-key="report_select_placeholder_project"
                                        data-error-id="error-project_id"
                                    >
                                        <x-pm.filter-dropdown
                                            name="project_id"
                                            :items="collect($projects)->map(fn($p) => ['value' => (string) $p->id, 'label' => $p->name])->values()->all()"
                                            :selected="old('project_id', '')"
                                            placeholder="{{ $isId ? 'Pilih proyek' : 'Select project' }}"
                                            :searchable="true"
                                        />
                                    </div>
                                    @error('project_id')
                                        <p id="error-project_id" class="report-field-error report-error-text" data-report-error-field="project_id">{{ $message }}</p>
                                    @else
                                        <p id="error-project_id" class="report-field-error report-error-text hidden"></p>
                                    @enderror
                                </div>

                                <div class="report-field">
                                    <label for="severity_id" class="report-field-label">
                                        <span data-report-i18n="report_label_severity">Severity</span>
                                    </label>
                                    <div
                                        class="report-select-wrapper {{ $errors->has('severity_id') ? 'report-select-wrapper-error' : '' }}"
                                        data-required-select="true"
                                        data-field-name="severity_id"
                                        data-field-label="Severity"
                                        data-report-i18n-field-label="report_field_label_severity"
                                        data-report-select-placeholder-key="report_select_placeholder_severity"
                                        data-error-id="error-severity_id"
                                    >
                                        <x-pm.filter-dropdown
                                            name="severity_id"
                                            :items="collect($severities)->map(fn($s) => ['value' => (string) $s->id, 'label' => $s->level])->values()->all()"
                                            :selected="old('severity_id', '')"
                                            placeholder="{{ $isId ? 'Pilih keparahan' : 'Select severity' }}"
                                            :searchable="false"
                                        />
                                    </div>
                                    @error('severity_id')
                                        <p id="error-severity_id" class="report-field-error report-error-text" data-report-error-field="severity_id">{{ $message }}</p>
                                    @else
                                        <p id="error-severity_id" class="report-field-error report-error-text hidden"></p>
                                    @enderror
                                </div>

                                <div class="report-field">
                                    <label for="frequency" class="report-field-label">
                                        <span data-report-i18n="report_label_frequency">Frequency</span>
                                    </label>
                                    <div
                                        class="report-select-wrapper {{ $errors->has('frequency') ? 'report-select-wrapper-error' : '' }}"
                                        data-required-select="true"
                                        data-field-name="frequency"
                                        data-field-label="Frequency"
                                        data-report-i18n-field-label="report_field_label_frequency"
                                        data-report-select-placeholder-key="report_select_placeholder_frequency"
                                        data-error-id="error-frequency"
                                    >
                                        <x-pm.filter-dropdown
                                            name="frequency"
                                            :items="[
                                                ['value' => 'once', 'label' => 'Once'],
                                                ['value' => 'rare', 'label' => 'Rarely'],
                                                ['value' => 'frequent', 'label' => 'Frequently'],
                                                ['value' => 'random', 'label' => 'Random']
                                            ]"
                                            :selected="old('frequency', '')"
                                            placeholder="{{ $isId ? 'Pilih frekuensi' : 'Select frequency' }}"
                                            :searchable="false"
                                        />
                                    </div>
                                    @error('frequency')
                                        <p id="error-frequency" class="report-field-error report-error-text" data-report-error-field="frequency">{{ $message }}</p>
                                    @else
                                        <p id="error-frequency" class="report-field-error report-error-text hidden"></p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ── Issue Title ── --}}
                    <section class="report-card report-card-section">
                        <div class="report-card-body">
                            <h2 class="report-section-title" data-report-i18n="report_section_issue_title">Issue Title</h2>

                            <div class="report-field">
                                <label for="title" class="report-field-label">
                                    <span data-report-i18n="report_label_title">Title</span>
                                </label>
                                <input
                                    id="title"
                                    name="title"
                                    value="{{ old('title') }}"
                                    data-required="true"
                                    data-field-label="Issue title"
                                    data-report-i18n-field-label="report_field_label_title"
                                    data-error-id="error-title"
                                    class="{{ $fieldBase }} {{ $errors->has('title') ? $fieldError : $fieldNormal }}"
                                    placeholder="Save button not working on Profile page"
                                    data-report-i18n-placeholder="report_placeholder_title"
                                    aria-invalid="{{ $errors->has('title') ? 'true' : 'false' }}"
                                />
                                @error('title')
                                    <p id="error-title" class="report-field-error report-error-text" data-report-error-field="title">{{ $message }}</p>
                                @else
                                    <p id="error-title" class="report-field-error report-error-text hidden"></p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- ── Description ── --}}
                    <section class="report-card report-card-section">
                        <div class="report-card-body">
                            <h2 class="report-section-title" data-report-i18n="report_section_description">Description</h2>

                            <div class="report-field">
                                <label for="description" class="report-field-label">
                                    <span data-report-i18n="report_label_description">What happened?</span>
                                </label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows="4"
                                    data-required="true"
                                    data-field-label="Description"
                                    data-report-i18n-field-label="report_field_label_description"
                                    data-error-id="error-description"
                                    class="{{ $fieldBase }} {{ $errors->has('description') ? $fieldError : $fieldNormal }} resize-y"
                                    placeholder="Describe what happened and what you expected instead."
                                    data-report-i18n-placeholder="report_placeholder_description"
                                    aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                                >{{ old('description') }}</textarea>
                                @error('description')
                                    <p id="error-description" class="report-field-error report-error-text" data-report-error-field="description">{{ $message }}</p>
                                @else
                                    <p id="error-description" class="report-field-error report-error-text hidden"></p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- ── Steps to Recreate ── --}}
                    <section class="report-card report-card-section">
                        <div class="report-card-body">
                            <h2 class="report-section-title" data-report-i18n="report_section_steps">Steps to Recreate</h2>

                            <div class="report-field">
                                <label for="reproduction_steps" class="report-field-label">
                                    <span data-report-i18n="report_label_reproduction_steps">What did you do?</span>
                                </label>
                                <textarea
                                    id="reproduction_steps"
                                    name="reproduction_steps"
                                    rows="4"
                                    data-required="true"
                                    data-field-label="Steps to recreate"
                                    data-report-i18n-field-label="report_field_label_reproduction_steps"
                                    data-error-id="error-reproduction_steps"
                                    class="{{ $fieldBase }} {{ $errors->has('reproduction_steps') ? $fieldError : $fieldNormal }} resize-y"
                                    placeholder="1. Opened the app and logged in&#10;2. Went to [page]&#10;3. Clicked [button]&#10;4. Issue appeared"
                                    data-report-i18n-placeholder="report_placeholder_reproduction_steps"
                                    aria-invalid="{{ $errors->has('reproduction_steps') ? 'true' : 'false' }}"
                                >{{ old('reproduction_steps') }}</textarea>
                                @error('reproduction_steps')
                                    <p id="error-reproduction_steps" class="report-field-error report-error-text" data-report-error-field="reproduction_steps">{{ $message }}</p>
                                @else
                                    <p id="error-reproduction_steps" class="report-field-error report-error-text hidden"></p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- ── Screenshots ── --}}
                    <section class="report-card report-card-section">
                        <div class="report-card-body">
                            <h2 class="report-section-title" data-report-i18n="report_section_attachments">Screenshots</h2>

                            <div class="report-field">
                                <label for="attachments" class="report-field-label">
                                    <span data-report-i18n="report_label_attachments">Attach images</span>
                                </label>

                                <input
                                    id="attachments"
                                    name="attachments[]"
                                    type="file"
                                    multiple
                                    accept="image/*"
                                    data-required="true"
                                    data-field-label="Screenshots"
                                    data-report-i18n-field-label="report_field_label_attachments"
                                    data-error-id="error-attachments"
                                    data-dropzone-id="attachmentDropzone"
                                    aria-invalid="{{ $errors->has('attachments') || $errors->has('attachments.*') ? 'true' : 'false' }}"
                                    class="sr-only report-file-input {{ ($errors->has('attachments') || $errors->has('attachments.*')) ? 'report-field-input-error' : 'report-field-input-normal' }}"
                                />

                                <div
                                    id="attachmentDropzone"
                                    class="report-upload-dropzone {{ ($errors->has('attachments') || $errors->has('attachments.*')) ? 'report-upload-dropzone-error' : '' }}"
                                    role="button"
                                    tabindex="0"
                                    aria-controls="attachments"
                                    aria-describedby="reportAttachmentHint"
                                >
                                    <div id="attachmentDropzoneEmptyState" class="report-upload-empty-state">
                                        <span class="report-upload-empty-icon" aria-hidden="true">
                                            <x-icon name="upload" class="h-5 w-5" />
                                        </span>
                                        <p class="report-upload-empty-title" data-report-i18n="report_upload_empty_text">
                                            Drop images here or click to browse
                                        </p>
                                        <p class="report-upload-empty-subtitle" data-report-i18n="report_upload_empty_subtext">
                                            JPG, PNG, WEBP, GIF — max 5 files
                                        </p>
                                    </div>
                                </div>

                                <div id="attachmentPreviewWrapper" class="report-attachment-preview-shell hidden">
                                    <div class="report-attachment-preview-head">
                                        <div class="report-attachment-preview-head-top">
                                            <p class="report-attachment-preview-title" data-report-i18n="report_preview_title">Attached</p>
                                            <button
                                                type="button"
                                                id="attachmentAddBtn"
                                                class="report-preview-btn report-preview-btn-ghost report-attachment-add-btn hidden"
                                            >
                                                <x-icon name="plus" class="h-3.5 w-3.5" />
                                                <span data-report-i18n="report_preview_add_image">Add</span>
                                            </button>
                                        </div>
                                        <p class="report-field-hint" data-report-i18n="report_preview_hint_text">
                                            Click Annotate to mark problem areas.
                                        </p>
                                    </div>

                                    <div id="attachmentPreviewList" class="report-attachment-preview-list"></div>

                                    <div id="annotationWorkspace" class="hidden">
                                        <div class="report-annotation-shell">
                                            <div class="report-annotation-head">
                                                <div class="report-annotation-file">
                                                    <span class="report-annotation-file-icon" aria-hidden="true">
                                                        <x-icon name="file-text" class="h-4 w-4" />
                                                    </span>
                                                    <span id="annotationFileLabel" class="report-annotation-file-label"></span>
                                                </div>
                                                <button
                                                    id="annotationClose"
                                                    type="button"
                                                    class="report-annotation-close-btn"
                                                >
                                                    <x-icon name="x" class="h-3.5 w-3.5" />
                                                    <span data-report-i18n="report_annotation_close">Close</span>
                                                </button>
                                            </div>

                                            <div
                                                id="annotationToolbar"
                                                class="report-annotation-toolbar"
                                                role="toolbar"
                                                data-report-i18n-aria-label="report_annotation_toolbar_aria_label"
                                                aria-label="Annotation toolbar"
                                            >
                                                <div class="report-annotation-toolbar-group">
                                                    <button type="button" data-tool="select" class="report-annotation-tool-btn is-active" title="Select" data-report-i18n-title="report_annotation_tool_select" disabled aria-disabled="true">
                                                        <x-icon name="mouse-pointer-2" class="h-4 w-4" />
                                                    </button>
                                                    <button type="button" data-tool="rectangle" class="report-annotation-tool-btn" title="Rectangle" data-report-i18n-title="report_annotation_tool_rectangle" disabled aria-disabled="true">
                                                        <x-icon name="square" class="h-4 w-4" />
                                                    </button>
                                                    <button type="button" data-tool="arrow" class="report-annotation-tool-btn" title="Arrow" data-report-i18n-title="report_annotation_tool_arrow" disabled aria-disabled="true">
                                                        <x-icon name="move-up-right" class="h-4 w-4" />
                                                    </button>
                                                    <button type="button" data-tool="freehand" class="report-annotation-tool-btn" title="Freehand" data-report-i18n-title="report_annotation_tool_freehand" disabled aria-disabled="true">
                                                        <x-icon name="pencil" class="h-4 w-4" />
                                                    </button>
                                                    <button type="button" data-tool="text" class="report-annotation-tool-btn" title="Text" data-report-i18n-title="report_annotation_tool_text" disabled aria-disabled="true">
                                                        <x-icon name="type" class="h-4 w-4" />
                                                    </button>
                                                </div>

                                                <div class="report-annotation-toolbar-divider"></div>

                                                <div class="report-annotation-toolbar-group">
                                                    <button type="button" data-color="#EF4444" class="report-annotation-color-btn is-active" style="background:#EF4444" title="Red" data-report-i18n-title="report_annotation_color_red" disabled aria-disabled="true"></button>
                                                    <button type="button" data-color="#F59E0B" class="report-annotation-color-btn" style="background:#F59E0B" title="Yellow" data-report-i18n-title="report_annotation_color_yellow" disabled aria-disabled="true"></button>
                                                    <button type="button" data-color="#10B981" class="report-annotation-color-btn" style="background:#10B981" title="Green" data-report-i18n-title="report_annotation_color_green" disabled aria-disabled="true"></button>
                                                    <button type="button" data-color="#3B82F6" class="report-annotation-color-btn" style="background:#3B82F6" title="Blue" data-report-i18n-title="report_annotation_color_blue" disabled aria-disabled="true"></button>
                                                    <button type="button" data-color="#111827" class="report-annotation-color-btn" style="background:#111827" title="Black" data-report-i18n-title="report_annotation_color_black" disabled aria-disabled="true"></button>
                                                </div>

                                                <div class="report-annotation-toolbar-divider"></div>

                                                <div class="report-annotation-toolbar-group ml-auto">
                                                    <button type="button" id="annotationUndo" class="report-annotation-tool-btn" title="Undo" data-report-i18n-title="report_annotation_undo" disabled aria-disabled="true">
                                                        <x-icon name="undo-2" class="h-4 w-4" />
                                                    </button>
                                                    <button type="button" id="annotationRedo" class="report-annotation-tool-btn" title="Redo" data-report-i18n-title="report_annotation_redo" disabled aria-disabled="true">
                                                        <x-icon name="redo-2" class="h-4 w-4" />
                                                    </button>
                                                    <button type="button" id="annotationDelete" class="report-annotation-tool-btn report-annotation-tool-btn-danger" title="Delete" data-report-i18n-title="report_annotation_delete_selected" disabled aria-disabled="true">
                                                        <x-icon name="trash-2" class="h-4 w-4" />
                                                    </button>
                                                    <button type="button" id="annotationClearAll" class="report-annotation-tool-btn report-annotation-tool-btn-danger" title="Clear All" data-report-i18n-title="report_annotation_clear_all" disabled aria-disabled="true">
                                                        <x-icon name="layers" class="h-4 w-4" />
                                                    </button>
                                                    <button type="button" id="annotationSave" class="report-preview-btn report-preview-btn-solid report-annotation-save-btn" title="Save" data-report-i18n-title="report_annotation_save" disabled aria-disabled="true">
                                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                                        <span data-report-i18n="report_annotation_save">Save</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <div id="annotationStatus" class="report-annotation-status"></div>
                                            <div id="annotationCanvasContainer" class="report-annotation-canvas"></div>
                                        </div>
                                    </div>
                                </div>

                                @error('attachments')
                                    <p id="error-attachments" class="report-field-error report-error-text" data-report-error-field="attachments">{{ $message }}</p>
                                @else
                                    <p id="error-attachments" class="report-field-error report-error-text hidden"></p>
                                @enderror

                                @error('attachments.*')
                                    <p class="report-field-error report-error-text" data-report-error-field="attachments.*">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- ── Submit ── --}}
                    <section class="report-card report-card-section">
                        <div class="report-card-body report-card-body-submit">
                            <p class="report-submit-note" data-report-i18n="report_meta_note">
                                After submitting, your ticket ID will be sent to your email.
                            </p>

                            <div class="report-submit-actions">
                                <button type="submit" class="btn btn-solid report-submit-btn">
                                    <x-lucide name="send" class="h-4 w-4" />
                                    <span data-report-i18n="report_submit_button">Send Report</span>
                                </button>
                            </div>
                        </div>
                    </section>

                </div>
            </form>
        </div>
    </div>
</section>