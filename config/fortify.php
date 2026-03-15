<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',

    'passwords' => 'users',

    'username' => 'email',

    'email' => 'email',

    'lowercase_usernames' => true,

    'home' => '/',

    'prefix' => '',

    'domain' => null,

    'middleware' => ['web'],

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
        'verification' => '6,1',
    ],

    'views' => true,

    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
            // 'window' => 0
        ]),
    ],

    'paths' => [
        'login' => '/dang-nhap',
        'logout' => '/dang-xuat',
        'register' => '/dang-ky',

        // Password Reset
        'password' => [
            'request' => '/quen-mat-khau',                  // /forgot-password
            'reset' => '/dat-lai-mat-khau/{token}',         // /reset-password
            'confirm' => '/xac-nhan-mat-khau',              // /user/confirm-password
            'confirmation' => '/nguoi-dung/trang-thai-xac-nhan-mat-khau',   // /user/confirmed-password-status
        ],

        'verification' => [
            'notice' => '/email/xac-thuc',                  // /email/verify
            'verify' => '/email/xac-thuc/{id}/{hash}',      // /email/verify/{id}/{hash}
            'send' => '/gui-xac-thuc-email',                // /email/verification-notification
        ],

        'user-profile-information' => [
            'update' => '/nguoi-dung/cap-nhat-thong-tin',   // /user/profile-information
        ],

        'user-password' => [
            'update' => '/nguoi-dung/cap-nhat-mat-khau',    // /user/password
        ],

        'two-factor' => [
            'login' => '/xac-thuc-hai-yeu-to',              // /two-factor-challenge
            'qr-code' => '/ma-qr-hai-yeu-to',               // /user/two-factor-qr-code
            'secret-key' => '/khoa-bi-mat-hai-yeu-to',      // /user/two-factor-secret-key
            'recovery-codes' => '/ma-khoi-phuc-hai-yeu-to', // /user/two-factor-recovery-codes
        ],

    ],
];
