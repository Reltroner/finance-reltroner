<?php
// config/services.php
// Context: finance.reltroner.local / finance.reltroner.com

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services (Unchanged)
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'             => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Reltroner Auth Gateway (SSO) — PHASE 3
    |--------------------------------------------------------------------------
    | Finance TRUSTS ONLY this gateway for authentication.
    | No direct Keycloak interaction here.
    |--------------------------------------------------------------------------
    */

    'gateway' => [

        /*
        | Issuer of RMAT (Reltroner Module Access Token)
        | MUST match token "iss"
        */
        'issuer' => env('RELTRONER_GATEWAY_ISSUER', 'https://app.reltroner.com'),

        /*
        | Audience expected by Finance
        | MUST match token "aud"
        */
        'audience' => env('RELTRONER_GATEWAY_AUDIENCE', 'finance.reltroner.com'),

        /*
        | Shared signing key (HS256)
        | MUST be identical with gateway
        */
        'signing_key' => env('RELTRONER_MODULE_SIGNING_KEY'),

        /*
        | Login URL for redirect on invalid / expired token
        */
        'login_url' => env(
            'RELTRONER_GATEWAY_LOGIN_URL',
            'https://app.reltroner.com/login'
        ),
    ],


    /*
    |--------------------------------------------------------------------------
    | Internal / Peer Modules (Optional, Non-Auth)
    |--------------------------------------------------------------------------
    | NOTE:
    | - NOT used for authentication
    | - For internal service calls only (future)
    |--------------------------------------------------------------------------
    */

    'modules' => [

        // Example: HRM internal API (future use)
        'hrm' => env('HRM_SERVICE', 'http://hrm.reltroner.local'),

        // Add more modules later:
        // 'inventory' => env('INVENTORY_SERVICE'),
        // 'crm'       => env('CRM_SERVICE'),
    ],

];
