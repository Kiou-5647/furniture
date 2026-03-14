<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',

    'passwords' => 'users',

    'username' => 'email',

    'email' => 'email',

    'lowercase_usernames' => true,

    'home' => '/dashboard',

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
        'login' => 'dang-nhap',
        'logout' => 'dang-xuat',
        'register' => 'dang-ky',
        'reset-password' => 'dat-lai-mat-khau',
        'forgot-password' => 'quen-mat-khau',
        'user/confirm-password' => 'nguoi-dung/xac-nhan-mat-khau',
    ],

];
