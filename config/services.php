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

    'mspbs' => [
        'fhir_url' => env('MSPBS_FHIR_URL', 'https://fhir.mspbs.gov.py/fhir'),
        'vshc_issuance_url' => rtrim(env('MSPBS_VHL_URL', 'https://gdncn.mspbs.gov.py'), '/').'/v2/vshcIssuance',
        'vshc_validation_url' => rtrim(env('MSPBS_VHL_URL', 'https://gdncn.mspbs.gov.py'), '/').'/v2/vshcValidation',

        // Servidores FHIR permitidos para la consulta de VHL (mitiga SSRF vía
        // el campo "Servidor FHIR"): solo se puede elegir uno de estos.
        'fhir_servers' => [
            'https://fhir.mspbs.gov.py/fhir' => 'MSPBS Paraguay',
        ],
    ],

];
