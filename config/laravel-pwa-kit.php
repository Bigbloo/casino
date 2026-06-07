<?php

return [
    'manifest' => [
        'name' => env('PWA_NAME', env('APP_NAME', 'My Casino App')),
        'short_name' => env('PWA_SHORT_NAME', 'CasinoApp'),
        'start_url' => '/',
        'theme_color' => '#FF5733',
        'background_color' => '#ffffff',
        'display' => 'standalone',
        'icons' => [
            [
                'src' => '/icons/icon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ],
            [
                'src' => '/icons/icon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ],
        ],
    ],
];
