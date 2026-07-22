<?php

return [
    'connections' => [
        'default' => [
            'hosts' => array_filter(explode(',', env('LDAP_HOSTS', '127.0.0.1'))),
            'port' => (int) env('LDAP_PORT', 389),
            'base_dn' => env('LDAP_BASE_DN', ''),
            'username' => env('LDAP_USERNAME', ''),
            'password' => env('LDAP_PASSWORD', ''),
            'use_ssl' => env('LDAP_USE_SSL', false),
            'use_tls' => env('LDAP_USE_TLS', env('LDAP_TLS', false)),
            'version' => 3,
            'timeout' => 5,
        ],
    ],
    'logging' => [
        'enabled' => env('LDAP_LOGGING', true),
    ],
];
