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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'alerts365' => [
        'api_key' => env('ALERTS365_KEY'),
        'waba_number' => env('ALERTS365_WABA'),
        'agent_id' => env('ALERTS365_AGENT_ID'),
        'bot_id' => env('ALERTS365_BOT_ID'),
        'base_url' => env('ALERTS365_BASE_URL', 'https://billions.alerts365.in'),
    ],

    'ocr_space' => [
        'api_key' => env('OCR_SPACE_API_KEY'),
        'base_url' => env('OCR_SPACE_BASE_URL', 'https://api.ocr.space/parse/image'),
    ],

];
