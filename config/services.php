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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),

        /*
         * Identifiants clients acceptés dans le champ `aud` d'un id_token
         * mobile. Séparés par des virgules.
         *
         * La valeur par défaut est l'identifiant web utilisé par l'application
         * Android (`requestIdToken(WEB_CLIENT_ID)` dans LoginScreen.kt) : c'est
         * lui qui figure dans `aud`, pas l'identifiant Android. Un identifiant
         * client OAuth n'est pas un secret — il est distribué dans l'APK — donc
         * le poser ici permet au contrôle de fonctionner sans configuration
         * supplémentaire, plutôt que de laisser le trou ouvert en attendant.
         */
        'allowed_client_ids' => env(
            'GOOGLE_ALLOWED_CLIENT_IDS',
            '364258394169-6osv054m4a6ckphm8l78d7arrah8se2p.apps.googleusercontent.com'
        ),
    ],

    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
