<?php
'connections' => [
    'default' => [
        'hosts' => [env('LDAP_HOSTS', '172.19.10.7')],
        'port' => env('LDAP_PORT', 389),
        'base_dn' => env('LDAP_BASE_DN', 'ou=Empleados,DC=asamblea,DC=gob,DC=sv'),
        'username' => env('LDAP_USERNAME', 'ASAMBLEA\\apache'),
        'password' => env('LDAP_PASSWORD', 'mapache'),
        'use_ssl' => env('LDAP_USE_SSL', false),
        'use_tls' => env('LDAP_USE_TLS', false),
        'version' => 3,
        'timeout' => 5,
        ],
    ],