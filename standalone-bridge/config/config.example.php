<?php

return [
    // Konfigurasi WebService Dapodik (port 5774).
    'dapodik' => [
        'host'  => getenv('DAPODIK_HOST') ?: '127.0.0.1',
        'port'  => (int)(getenv('DAPODIK_PORT') ?: 5774),
        'npsn'  => getenv('DAPODIK_NPSN') ?: '20300001',
        'token' => getenv('DAPODIK_TOKEN') ?: 'PASTE_DAPODIK_TOKEN_HERE',
    ],

    // Konfigurasi Moodle WebService REST API.
    'moodle' => [
        'url'   => getenv('MOODLE_URL') ?: 'https://lms.sekolah.sch.id',
        'token' => getenv('MOODLE_TOKEN') ?: 'PASTE_MOODLE_WSTOKEN_HERE',
    ],

    // Aturan Sinkronisasi.
    'sync' => [
        'default_password' => 'Dapodik@2026!',
        'email_domain'     => 'sekolah.sch.id',
    ],
];
