<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        /*
        | admin guard   — used by Admin + Loan Officer portal (/admin/*)
        | officer guard — used by Loan Officer only (/officer/*)
        | borrower guard— used by Borrower portal (/portal/*)
        |
        | All three pull from the same `users` table.
        | The role column ('admin','loan_officer','borrower') controls access.
        */
        'admin' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'officer' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'borrower' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'agent' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
