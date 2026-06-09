<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Karyawan Role
    |--------------------------------------------------------------------------
    |
    | Tentukan role default untuk karyawan baru yang melakukan registrasi.
    |
    */
    'default' => env('DEFAULT_ROLE', 'karyawan'),

    /*
    |--------------------------------------------------------------------------
    | Available Roles
    |--------------------------------------------------------------------------
    |
    | Daftar role yang tersedia dalam aplikasi.
    |
    */
    'available' => [
        'admin',
        'karyawan',
    ],
];
