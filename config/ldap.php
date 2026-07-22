<?php

return [
    'connections' => [
        'default' => [
            'hosts' => [env('LDAP_HOSTS')],
            'port' => env('LDAP_PORT', 389),
            'base_dn' => env('LDAP_BASE_DN'),
            'username' => env('LDAP_USERNAME'),
            'password' => env('LDAP_PASSWORD'),
            'use_ssl' => env('LDAP_USE_SSL', false),
            'use_tls' => env('LDAP_USE_TLS', false),
            'version' => 3,
            'timeout' => 5,
        ],
    ],
];