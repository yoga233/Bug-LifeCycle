<?php

return [
    'validation' => [
        'required' => ':attribute wajib diisi.',
        'email' => 'Format :attribute belum valid.',
        'exists' => ':attribute yang dipilih tidak valid.',
        'in' => 'Nilai :attribute tidak valid.',
        'max_string' => ':attribute maksimal :max karakter.',
        'max_array' => ':attribute maksimal :max file.',
        'file' => ':attribute harus berupa file yang valid.',
        'mimes' => 'Format file :attribute harus berupa: :values.',
        'attachment_item_max' => 'Ukuran setiap lampiran maksimal 5 MB.',
    ],

    'attributes' => [
        'guest_name' => 'Nama lengkap',
        'guest_email' => 'Email aktif',
        'guest_company' => 'Perusahaan / Organisasi',
        'guest_position' => 'Jabatan',
        'guest_version' => 'Versi aplikasi',
        'project_id' => 'Project terdampak',
        'severity_id' => 'Tingkat dampak',
        'title' => 'Judul bug',
        'description' => 'Deskripsi bug',
        'reproduction_steps' => 'Langkah reproduksi',
        'frequency' => 'Frekuensi kejadian',
        'attachments' => 'Lampiran',
        'attachments_item' => 'Lampiran',
    ],

    'spam' => [
        'ip_hard_limit' => 'Terlalu banyak laporan dari IP Anda hari ini. Silakan coba lagi besok.',
        'temporarily_blocked' => 'Anda diblokir sementara. Silakan coba lagi dalam :minutes menit.',
        'rate_limit_exceeded' => 'Anda melebihi batas laporan. Anda diblokir sementara selama :minutes menit.',
    ],
];
