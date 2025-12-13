<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    //NUEVO
    /*
    |--------------------------------------------------------------------------
    | Power BI Configuration
    |--------------------------------------------------------------------------
    */

    'powerbi' => [
        'embed_url' => env('POWERBI_EMBED_URL', ''),
        'report_id' => env('POWERBI_REPORT_ID', ''),
        'tenant_id' => env('POWERBI_TENANT_ID', ''),
        'gateway_installed' => env('POWERBI_GATEWAY_INSTALLED', false),
        'internal_db_host' => env('POWERBI_INTERNAL_DB_HOST', null),
        'require_institutional_email' => env('POWERBI_REQUIRE_INSTITUTIONAL_EMAIL', false),
        'azure_ad_tenant' => env('POWERBI_AZURE_AD_TENANT', null),
        'service_url' => 'https://app.powerbi.com',
        'api_url' => 'https://api.powerbi.com/v1.0/myorg',
    ],


];
