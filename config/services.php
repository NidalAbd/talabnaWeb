<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party خدمات such
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        // Other OAuth clients whose ID tokens are accepted (Android; iOS com.talabna.talabna).
        'android_client_id' => env('GOOGLE_ANDROID_CLIENT_ID'),
        'ios_client_id' => env('GOOGLE_IOS_CLIENT_ID', '808302489355-ap7tq4tme5du6vqkskp7hu9ovcpaqjog.apps.googleusercontent.com'),
    ],
    'apple' => [
        // Sign in with Apple: the iOS bundle id is the identity-token audience.
        'client_id' => env('APPLE_CLIENT_ID', 'com.talabna.talabna'),
        'services_id' => env('APPLE_SERVICES_ID'),
        // Token revocation on account deletion (guideline 5.1.1(v)): Apple developer key (.p8).
        'team_id' => env('APPLE_TEAM_ID'),
        'key_id' => env('APPLE_KEY_ID'),
        'private_key' => env('APPLE_PRIVATE_KEY'),
        'private_key_path' => env('APPLE_PRIVATE_KEY_PATH'),
    ],
    'apple_iap' => [
        // App Store Connect > app > App Information > App-Specific Shared Secret.
        'shared_secret' => env('APPLE_IAP_SHARED_SECRET'),
        'bundle_id' => env('APPLE_CLIENT_ID', 'com.talabna.talabna'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'google_play' => [
        'package_name' => env('GOOGLE_PLAY_PACKAGE_NAME'),
        'credentials_path' => env('GOOGLE_PLAY_CREDENTIALS_PATH'),
    ],

];
