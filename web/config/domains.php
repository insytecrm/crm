<?php

$appHost = parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';

return [
    /*
    |--------------------------------------------------------------------------
    | CNAME target for custom domains
    |--------------------------------------------------------------------------
    |
    | Clients should point their CRM/website hostnames at this target (CNAME)
    | or an A record that resolves to the same place as this host.
    |
    */
    'cname_target' => env('DOMAINS_CNAME_TARGET', $appHost),

    /*
    |--------------------------------------------------------------------------
    | DNS TXT verification prefix
    |--------------------------------------------------------------------------
    */
    'verification_prefix' => 'insyte-site-verification=',
];
