<?php

use App\Http\Middleware\AuthenticateFirm;
use Mantasruigys3000\SimpleSwagger\Data\SecurityScheme;

return [

    /**
     * Documentation Info
     */

    'openapi' => '3.1.0',

    'info' => [
        'title' => 'swagger title',
        'summary' => 'swagger summary',
        'description' => 'swagger description',
        'termsOfService' => 'https://tos.com',
        //                'contact' => [
        //                    // Contact Object
        //                    ''
        //                ],
        //                'license' => [
        //                    ''
        //                ],
        'version' => '0.0.1',
    ],

    'servers' => [
        ['url' => 'https://website.com'],
    ],

    'security_schemes' => [
        SecurityScheme::BearerAuth('bearer')
    ],

    /**
     * Allowed route URI's
     */
    'allowed_routes' => [
        'api/*'
    ],

    'output_path' => public_path('docs/openapi.yaml'),

];
