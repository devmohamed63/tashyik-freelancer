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

    /*
    | Optional: force invoice links (emails / Daftra) to this URL instead of the built-in public page.
    | Use {id} for the local invoice id, e.g. https://example.com/custom/{id}
    |
    | If empty: short link public/i/{view_token} on the dashboard host (HTML in any browser).
    */
    'tashyik' => [
        'invoice_show_url' => env('TASHYIK_INVOICE_SHOW_URL'),
        'public_invoice_link_ttl_days' => env('TASHYIK_PUBLIC_INVOICE_LINK_TTL_DAYS', 1825),
        /**
         * When false, invoice emails do not link to this app’s public invoice HTML (no “hosted” copy in the link sense).
         * Primary CTA uses Daftra public preview URL from the API when available; otherwise only staff / wait messaging.
         */
        'invoice_emails_include_local_public_link' => env('TASHYIK_INVOICE_EMAILS_INCLUDE_LOCAL_PUBLIC_LINK', true),
    ],

    'daftra' => [
        'api_key' => env('DAFTRA_API_KEY'),
        'subdomain' => env('DAFTRA_SUBDOMAIN', 'wadhacompany'),
        'cost_center_id' => env('DAFTRA_COST_CENTER_ID', 1),
        'bank_account_id' => env('DAFTRA_BANK_ACCOUNT_ID'),
        'revenue_account_id' => env('DAFTRA_REVENUE_ACCOUNT_ID'),
        'return_account_id' => env('DAFTRA_RETURN_ACCOUNT_ID'),
        'sp_payout_account_id' => env('DAFTRA_SP_PAYOUT_ACCOUNT_ID', 51),
        'invoice_pdf_enabled' => env('DAFTRA_INVOICE_PDF_EMAIL_ENABLED', true),
        'invoice_pdf_bcc' => env('DAFTRA_INVOICE_PDF_BCC_EMAIL'),
        /** After sync, GET invoice JSON to store invoice_html_url / invoice_pdf_url for emails (no owner login). */
        'fetch_public_invoice_url' => env('DAFTRA_FETCH_PUBLIC_INVOICE_URL', true),
        /** Download invoice_pdf_url from Daftra and attach PDF to the Daftra invoice email when possible. */
        'attach_invoice_pdf_to_email' => env('DAFTRA_ATTACH_INVOICE_PDF_TO_EMAIL', true),
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
                'gemini-2.5-flash,gemini-2.5-flash-lite,gemini-2.0-flash'
            ))
        ))),
        'project_name' => env('GEMINI_PROJECT_NAME'),
    ],

    'pinecone' => [
        'api_key' => env('PINECONE_API_KEY'),
        'index_name' => env('PINECONE_INDEX_NAME'),
        'index_host' => env('PINECONE_INDEX_HOST'),
        'namespace' => env('PINECONE_NAMESPACE', 'default'),
        'api_version' => env('PINECONE_API_VERSION', '2025-10'),
        'record_text_key' => env('PINECONE_RECORD_TEXT_KEY', 'text'),
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
