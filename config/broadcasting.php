<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | Aquí se establece la conexión por defecto para el broadcasting.
    | Lo cambiamos de 'null' o 'pusher' a 'reverb'.
    |
    */

    'default' => env('BROADCAST_CONNECTION', 'reverb'), // 👈 Cambia aquí

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Aquí se definen todas las conexiones de broadcasting disponibles.
    |
    */

    'connections' => [

        'reverb' => [ // 👈 Este es el nuevo bloque que debes agregar
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST', 'localhost'),
                'port' => env('REVERB_PORT', 9000),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'ping' => env('REVERB_PING_INTERVAL', 10),
                'tls' => [
                    'verify_peer' => env('REVERB_TLS_VERIFY_PEER', true),
                    'verify_peer_name' => env('REVERB_TLS_VERIFY_PEER_NAME', true),
                ],
            ],
        ],
        
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                //
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];