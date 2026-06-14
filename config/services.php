<?php

use App\Models\Empresa;
use Illuminate\Support\Facades\Storage;

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

    'greenter' => [
        'mode'      => env('GREENTER_MODE', 'beta'),
        'beta_url'  => env('GREENTER_BETA_URL', 'https://ose.nubefact.com/ol-ti-itcpe/billService?wsdl'),
        'demo_url'  => env('GREENTER_DEMO_URL', 'https://demo-ose.nubefact.com/ol-ti-itcpe/billService?wsdl'),
        'cert_disk' => env('GREENTER_CERT_DISK', 'public'),
    ],

];
