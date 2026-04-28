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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'daftra' => [
        'api_key' => env('DAFTRA_API_KEY'),
        'subdomain' => env('DAFTRA_SUBDOMAIN', 'wadhacompany'),
        'cost_center_id' => env('DAFTRA_COST_CENTER_ID', 1),
        'bank_account_id' => env('DAFTRA_BANK_ACCOUNT_ID'),
        'revenue_account_id' => env('DAFTRA_REVENUE_ACCOUNT_ID'),
        'return_account_id' => env('DAFTRA_RETURN_ACCOUNT_ID'),
    ],
        'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'text_model' => env('GEMINI_TEXT_MODEL', 'gemini-2.5-flash'),
      'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 32768),
        'thinking_budget' => env('GEMINI_THINKING_BUDGET'),
        'text_models' => array_values(array_filter(array_map(
            'trim',
            explode(',', env(
                'GEMINI_TEXT_MODELS',
                'gemini-2.5-flash,gemini-2.5-flash-lite,gemini-2.5-flash-preview-09-2025,gemini-2.0-flash,gemini-2.0-flash-001,gemini-1.5-flash-latest,gemini-1.5-pro-latest,gemini-1.5-pro,gemini-1.5-flash-001,gemini-1.5-flash'
            ))
        ))),
        'project_name' => env('GEMINI_PROJECT_NAME'),
    ],
        'image_generation' => [
        'provider' => env('IMAGE_PROVIDER', 'openai'),
        'brand_primary_hex' => env('IMAGE_BRAND_PRIMARY_HEX', '#724193'),
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
             'model' => env('OPENAI_IMAGE_MODEL', 'gpt-image-1.5'),
            'size' => env('OPENAI_IMAGE_SIZE', '1024x1024'),
            'quality' => env('OPENAI_IMAGE_QUALITY', 'standard'),
        ],
    ],

];
