<?php

/**
 * This file is part of laravel-saml,
 * a SAML IDP integration for laravel. 
 *
 * @license MIT
 * @package kingstarter/laravel-saml
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Base settings
    |--------------------------------------------------------------------------
    |
    | General package settings
    |
    */

    // Include the pre-defined routes from package or not.
    'use_package_routes' => true,
    
    // Forward user roles
    // This option requires entrust to be installed and
    // the user model to support the roles() method. Otherwise an empty
    // array of user roles will be forwarded.
    'forward_roles' => false,
    
    // Allow debugging within SamlAuth trait to get SP data during SAML auth
    // request. The debug output is written to storage/logs/laravel.log.
    'debug_saml_request' => false,

    'email_domain'	=> env('SAML_MAIL', 'ms.tp.edu.tw'),
    /*
    |--------------------------------------------------------------------------
    | IDP (identification provider) settings
    |--------------------------------------------------------------------------
    |
    | Set overall configuration for laravel as idp server.
    |
    | All files are in storage/saml and referenced via Storage::disk('saml') 
    | as root directory. To have a valid storage configuration, add the root  
    | path to the config/filesystem.php file.
    |
    */
    
    'idp' => [
        'metadata'  => 'idp/metadata.xml',
        'cert'      => 'idp/cert.pem',
        'key'       => 'idp/key.pem',
    ],

    /*
    |--------------------------------------------------------------------------
    | SP (service provider) settings
    |--------------------------------------------------------------------------
    |
    | Array of service provider data. Add your list of SPs here.
    |
    | An SP is defined by its consumer service URL which is base64 encoded. 
    | It contains the destination, issuer, cert and cert-key. 
    |
    */

    'sp' => [        
        
        /**
         * SP Entry ID for G suits domain ms.tp.edu.tw
         */
        'aHR0cHM6Ly93d3cuZ29vZ2xlLmNvbS9hL21zLnRwLmVkdS50dy9hY3M=' => [        
            // The destination is the consuming SAML URL. This might be a SamlAuthController receiving the SAML response.  
            'destination' => 'https://accounts.google.com/a/ms.tp.edu.tw/acs',
            // Issuer could be anything, mostly it makes sense to pass the metadata URL
            'issuer' => 'google.com/a/ms.tp.edu.tw',
            //'nameID' => 'idno',
            
            // OPTIONAL: Use a specific audience restriction value when creating the SAMLRequest object.
            //           Default value is the assertion consumer service URL (the base64 encoded SP url). 
            //           This is a bugfix for Nextcloud as SP and can be removed for normal SPs.
            //'audience_restriction' => 'https://www.google.com/a/ms.tp.edu.tw/acs',
        ],
        
    ],
    
];
