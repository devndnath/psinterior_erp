<?php

use App\Enums\UserType;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | The staff ("web") guard is the default — it is what every admin
    | controller, policy and Gate check assumes unless told otherwise.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Three session guards share the same `users` table (ARCHITECTURE.md §5,
    | §7). Each guard's provider additionally scopes to the matching
    | `user_type`, so a client session can never authenticate as staff even
    | if a client row's credentials were somehow tried against /login, and
    | the reverse. This is enforced in the provider below, not only in UI
    | routing.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'client' => [
            'driver' => 'session',
            'provider' => 'clients',
        ],

        'vendor' => [
            'driver' => 'session',
            'provider' => 'vendors',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'users' => [
            'driver' => 'scoped_eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
            'user_type' => UserType::Staff->value,
        ],

        'clients' => [
            'driver' => 'scoped_eloquent',
            'model' => App\Models\User::class,
            'user_type' => UserType::Client->value,
        ],

        'vendors' => [
            'driver' => 'scoped_eloquent',
            'model' => App\Models\User::class,
            'user_type' => UserType::Vendor->value,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'clients' => [
            'provider' => 'clients',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'vendors' => [
            'provider' => 'vendors',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,

    /*
    |--------------------------------------------------------------------------
    | user_type values each guard is restricted to (read by
    | App\Providers\AuthServiceProvider when it scopes each provider).
    |--------------------------------------------------------------------------
    */

    'guard_user_types' => [
        'web' => UserType::Staff->value,
        'client' => UserType::Client->value,
        'vendor' => UserType::Vendor->value,
    ],

];
