<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PWA Manifest Configuration
    |--------------------------------------------------------------------------
    |
    | This file configures the Web App Manifest for the Progressive Web App.
    | Customize these values to match your gaming platform branding.
    |
    */

    'manifest' => [
        'name'             => env('APP_NAME', 'Gaming Platform'),
        'short_name'       => 'CasinoApp',
        'description'      => 'Your premier online gaming and casino platform.',
        'start_url'        => '/',
        'display'          => 'standalone',
        'orientation'      => 'portrait',
        'theme_color'      => '#FF5733',
        'background_color' => '#0f172a',
        'lang'             => 'fr',
        'scope'            => '/',
        'icons'            => [
            [
                'src'     => '/minimal/img/icons/icon-72x72.png',
                'sizes'   => '72x72',
                'type'    => 'image/png',
                'purpose' => 'maskable any',
            ],
            [
                'src'     => '/minimal/img/icons/icon-96x96.png',
                'sizes'   => '96x96',
                'type'    => 'image/png',
                'purpose' => 'maskable any',
            ],
            [
                'src'     => '/minimal/img/icons/icon-128x128.png',
                'sizes'   => '128x128',
                'type'    => 'image/png',
                'purpose' => 'maskable any',
            ],
            [
                'src'     => '/minimal/img/icons/icon-144x144.png',
                'sizes'   => '144x144',
                'type'    => 'image/png',
                'purpose' => 'maskable any',
            ],
            [
                'src'     => '/minimal/img/icons/icon-152x152.png',
                'sizes'   => '152x152',
                'type'    => 'image/png',
                'purpose' => 'maskable any',
            ],
            [
                'src'     => '/minimal/img/icons/icon-192x192.png',
                'sizes'   => '192x192',
                'type'    => 'image/png',
                'purpose' => 'maskable any',
            ],
            [
                'src'     => '/minimal/img/icons/icon-384x384.png',
                'sizes'   => '384x384',
                'type'    => 'image/png',
                'purpose' => 'maskable any',
            ],
            [
                'src'     => '/minimal/img/icons/icon-512x512.png',
                'sizes'   => '512x512',
                'type'    => 'image/png',
                'purpose' => 'maskable any',
            ],
        ],
        'shortcuts'        => [
            [
                'name'        => 'Jouer maintenant',
                'short_name'  => 'Jouer',
                'description' => 'Accéder aux jeux',
                'url'         => '/games',
                'icons'       => [
                    ['src' => '/minimal/img/icons/icon-96x96.png', 'sizes' => '96x96'],
                ],
            ],
            [
                'name'        => 'Déposer',
                'short_name'  => 'Dépôt',
                'description' => 'Effectuer un dépôt',
                'url'         => '/deposit',
                'icons'       => [
                    ['src' => '/minimal/img/icons/icon-96x96.png', 'sizes' => '96x96'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Worker Configuration
    |--------------------------------------------------------------------------
    */
    'service_worker' => [
        'enabled'          => true,
        'cache_name'       => 'gaming-platform-v1',
        'offline_page'     => '/offline',
        'cache_strategies' => [
            'network_first'  => ['/api/', '/games/'],
            'cache_first'    => ['/minimal/', '/frontend/'],
            'stale_while_revalidate' => ['/'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Tags
    |--------------------------------------------------------------------------
    */
    'meta' => [
        'apple_mobile_web_app_capable'           => 'yes',
        'apple_mobile_web_app_status_bar_style'  => 'black-translucent',
        'apple_mobile_web_app_title'             => env('APP_NAME', 'Gaming Platform'),
        'mobile_web_app_capable'                 => 'yes',
        'msapplication_tap_highlight'            => 'no',
    ],
];
