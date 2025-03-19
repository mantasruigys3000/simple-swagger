<?php

declare(strict_types=1);

/**
 * A Full OpenAPI schema in a php array for reference.
 */
return [
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
    'paths' => [
        '/users' => [
            // Get single User
            'get' => [
                'security' => [
                    ['BearerAuth' => []],
                ],
                'summary' => 'Get all users',
                'description' => 'Get all users from the database',
                'tags' => [
                    'users',
                ],
            ],

            // Post to make a new user
            'post' => [
                'security' => [
                    ['OAuth2' => ['*']]
                ],
                'summary' => 'Create a new user',
                'description' => 'Create a new user and store it in the database',
                'tags' => [
                    'users',
                ],
                'responses' => [
                    '200' => [
                        'description' => 'User successfully created',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'oneOf' => [
                                        ['$ref' => '#/components/schemas/get_user_response_schema'],
                                        ['$ref' => '#/components/schemas/get_minimal_user_response_schema'],
                                    ]
                                ],
                                'examples' => [
                                    'User' => ['$ref' => '#/components/examples/user_example'],
                                    'Minimal User' => ['$ref' => '#/components/examples/minimal_user_example'],
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
    ],
    'tags' => [
        ['name' => 'users', 'description' => 'User APIS'],
    ],

    /*
     * Components
     */

    'components' => [

        /**
         * Responses
         */

        /*'responses' => [
            "user" => [
                'description' => 'user data',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/get_user_response_schema'
                        ]
                    ]
                ]
            ],
            'minimal_user' => [
                'description' => 'minimal user data',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/get_user_response_schema'
                        ]
                    ]
                ]
            ]
        ],*/

        'schemas' => [
            'get_user_response_schema' => [
                'type' => 'object',
                'properties' => [
                    'uuid' => ['type' => 'string','format' => 'uuid'],
                    'first_name' => ['type' => 'string'],
                ]
            ],
            'get_minimal_user_response_schema' => [
                'type' => 'object',
                'properties' => [
                    'uuid' => ['type' => 'string','format' => 'uuid'],
                    'first_name' => ['type' => 'string'],
                ]
            ]
        ],

        'examples' => [
            'user_example' => [
                'summary' => 'User response',
                'value' => [
                    'uuid' => 'ce9280e2-1d3f-466d-9e81-6cd1ec9581d1',
                    'first_name' => 'John',
                ]
            ],
            'minimal_user_example' => [
                'summary' => 'Minimal user response',
                'value' => [
                    'uuid' => 'ce9280e2-1d3f-466d-9e81-6cd1ec9581d1',
                    'first_name' => 'John',
                ]
            ]
        ],

//    'schemas' => [
//    ],

        /**
         * Security Schemes
         */

        'securitySchemes' => [

            'BearerAuth' => [
                'scheme' => 'bearer',
                'type' => 'http',
            ],

            'OAuth2' => [
                'type' => 'oauth2',
                'flows' => [
                    'authorizationCode' => [
                        'scopes' => [
                            '*' => 'all'
                        ],
                        'tokenUrl' => 'https://example.com/oauth/token',
                        'authorizationUrl' => 'https://example.com/oauth/authorize',
                    ]
                ]
            ]
        ],
    ],
];
