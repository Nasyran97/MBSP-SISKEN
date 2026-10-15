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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mbsp' => [
        'username'       => env('MBSP_DEPT_API_USERNAME', 'enforce'),
        'password'       => env('MBSP_DEPT_API_PASSWORD', 'enforce@mbsp123'),
        'dept_url'       => env('MBSP_DEPT_API_URL', 'https://apimbsp.mbsp.gov.my/public/APIS_6874be6b13258/data/department'),
        'dept_key'       => env('MBSP_DEPT_API_KEY', '6874be6b13269'),
        'dept_value'     => env('MBSP_DEPT_API_VALUE', 'XHWBIEWPB0UKNLO505Q2B171'),
        'staff_url'      => env('MBSP_STAFF_API_URL', 'https://apimbsp.mbsp.gov.my/public/APIS_633bb3e5a094b/staff/bydepartment'),
        'staff_key'      => env('MBSP_STAFF_API_KEY', '633bb3e5a0993'),
        'staff_value'    => env('MBSP_STAFF_API_VALUE', 'LBTXN72WW3JCS13W1WIUE485'),
    ],

];
