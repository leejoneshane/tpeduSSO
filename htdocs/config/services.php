<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => 'https://ldap.tp.edu.tw/login/facebook/callback',
       ],
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => 'https://ldap.tp.edu.tw/login/google/callback',
    ],
    'yahoo' => [
        'client_id'     => env('YAHOO_CLIENT_ID'),
        'client_secret' => env('YAHOO_CLIENT_SECRET'),
        'redirect'      => 'https://ldap.tp.edu.tw/login/yahoo/callback',
    ],
    'line' => [
        'client_id'     => env('LINE_KEY'),
        'client_secret' => env('LINE_SECRET'),
        'redirect'      => 'https://ldap.tp.edu.tw/login/line/callback',
    ],

];
