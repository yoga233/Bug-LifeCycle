<?php

return [
    'validation' => [
        'required' => ':attribute is required.',
        'email' => 'Invalid :attribute format.',
        'exists' => 'The selected :attribute is invalid.',
        'in' => 'The selected :attribute is invalid.',
        'max_string' => ':attribute may not be greater than :max characters.',
        'max_array' => ':attribute may not have more than :max files.',
        'file' => ':attribute must be a valid file.',
        'mimes' => ':attribute must be a file of type: :values.',
        'attachment_item_max' => 'Each attachment may not be greater than 5 MB.',
    ],

    'attributes' => [
        'guest_name' => 'Full name',
        'guest_email' => 'Active email',
        'guest_company' => 'Company / Organization',
        'guest_position' => 'Job title',
        'guest_version' => 'App version',
        'project_id' => 'Affected project',
        'severity_id' => 'Impact level',
        'title' => 'Bug title',
        'description' => 'Bug description',
        'reproduction_steps' => 'Reproduction steps',
        'frequency' => 'Occurrence frequency',
        'attachments' => 'Attachments',
        'attachments_item' => 'Attachment',
    ],

    'spam' => [
        'ip_hard_limit' => 'Too many reports from your IP today. Please try again tomorrow.',
        'temporarily_blocked' => 'You are temporarily blocked. Please try again in :minutes minutes.',
        'rate_limit_exceeded' => 'You exceeded the report limit. You are temporarily blocked for :minutes minutes.',
    ],
];
