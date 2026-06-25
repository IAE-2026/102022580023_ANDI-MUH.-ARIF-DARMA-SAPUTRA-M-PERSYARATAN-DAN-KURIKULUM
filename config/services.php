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

    'iae' => [
        'url' => env('IAE_SSO_URL', 'https://iae-sso.virtualfri.id'),
        'api_nim' => env('IAE_API_NIM', '102022580023'),
        'service_name' => env('SERVICE_NAME', 'Prasyarat-Kurikulum-Service'),
        'api_key' => env('IAE_API_KEY'),
        'team_id' => env('IAE_TEAM_ID', 'TIM-09'),
        'warga_email' => env('IAE_WARGA_EMAIL'),
        'warga_password' => env('IAE_WARGA_PASSWORD'),
        'token' => env('IAE_SSO_TOKEN'),
        'token_expires_at' => env('IAE_SSO_TOKEN_EXPIRES_AT'),
        'soap_audit_url' => env('IAE_SOAP_AUDIT_URL', 'https://iae-sso.virtualfri.id/soap/v1/audit'),
        'rabbitmq_publish_url' => env('IAE_RABBITMQ_PUBLISH_URL', 'https://iae-sso.virtualfri.id/api/v1/messages/publish'),
        'default_role' => env('IAE_DEFAULT_ROLE', 'mahasiswa'),
        'allowed_nilai_roles' => ['dosen', 'admin'],
        'role_map' => [
            'warga01@ktp.iae.id' => 'dosen',
        ],
    ],

];
