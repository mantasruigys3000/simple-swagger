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

        /**
         * TODO: interesting problem here
         * Laravel will generate paths like {user} in instances where you would want it to say {user_uuid}.
         * Need to find a way to let users change this name while still being compliant in docs
         */
        '/users/{user}' => [
            'get' => [
                'parameters' => [
                    ['in' => 'path','name' => 'user','schema' => ['type' => 'string','format' => 'uuid'],'required' => true,'description' => 'User UUID','example' => 'uuid example'], // Each param is an object

                    // Auth Account UUID parameter
                    ['in' => 'header','name' => 'X-ACCOUNT-UUID','schema' => ['type' => 'string','format' => 'uuid'],'description' => 'Current account UUID to authenticate as','required' => true,'example' => 'uuid example'],

                    // Query param
                    ['in' => 'query','name' => 'per_page','schema' => ['type' => 'integer','default' => 500],'description' => 'amount of records per page','example' => 25,'required' => false],
                ],
                'operationId' => 'getUser',
                'security' => [
                    ['BearerAuth' => []],
                ],
                'summary' => 'Get a user',
                'description' => 'Get a user from the database',
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
                    ],
                ]
            ]
        ],

        '/users' => [
            // Get all Users
            'get' => [
                'operationId' => 'getUsers',
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
                'operationId' => 'createUser',
                'security' => [
                    ['OAuth2' => ['*']]
                ],
                'summary' => 'Create a new uer',
                'description' => 'Create a new user and store it in the database',
                'tags' => [
                    'users',
                ],
                'requestBody' => [
                    'description' => 'User data',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'oneOf' => [
                                    ['$ref' => '#/components/schemas/create_user_standard'],
                                    ['$ref' => '#/components/schemas/create_user_admin'],
                                ],
                            ]
                        ],
                        'multipart/form-data' => [
                            'schema' => [
                                'oneOf' => [
                                    ['$ref' => '#/components/schemas/create_user_with_file'],
                                    ['$ref' => '#/components/schemas/create_user_with_file'],
                                ],
                            ],
                            'examples' => [
                                'File Example' => ['$ref' => '#/components/examples/file_example']
                            ],
                            // TODO
                            // Supporting encoding field not required right now
                            'encoding' => [
                                'file' => [
                                    'contentType' => 'image/png, image/jpeg',
                                    'headers' => [
                                        'X-File-Header' => [
                                            'description' => 'multipart header',
                                            'schema' => [
                                                'type' => 'string'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
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
                    ],
                    '403' => [
                        '$ref' => '#/components/responses/unauthorized'
                    ],
                    '422' => [
                        '$ref' => '#/components/responses/unprocessable'
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

        'responses' => [
            "unauthorized" => [
                'description' => 'Action is forbidden',
            ],
            'unprocessable' => [
                'description' => 'Invalid data',
                'content' => [
                    'application/json'=> [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'errors' => [
                                    'type' => 'object',
                                    'additionalProperties' => ['type' => 'array','items' => ['type' => 'string']],
                                    'properties' => [
                                        'error_message' => ['type' => 'array','items' => ['type' => 'string']]
                                    ],
                                    'items' => [
                                        'type' => 'string',
                                    ]
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ],

        'schemas' => [

            // Request body Schemas
            'create_user_standard' => [
                'type' => 'object',
                'title' => 'Create Standard User',
                'properties' => [
                    'first_name' => ['type' => 'string']
                ],
                'required' => [
                    'first_name'
                ]
            ],

            'create_user_admin' => [
                'type' => 'object',
                'title' => 'Create Admin User',
                'properties' => [
                    'first_name' => ['type' => 'string'],
                    'email' => ['type' => 'string','format' => 'email']
                ],
                'required' => [
                    'first_name',
                ]
            ],

            'create_user_with_file' => [
                'type' => 'object',
                'title' => 'Create User With File',
                'properties' => [
                    'first_name' => ['type' => 'string'],
                    'email' => ['type' => 'string','format' => 'email'],
                    'file' => ['type' => 'string','format' => 'binary']
                ],
                'required' => [
                    'first_name',
                    'email',
                    'file'
                ],

            ],

            // Response body schemas
            'get_user_response_schema' => [
                'title' => 'User schema title', // Important for rendering while not needing to be unique
                'type' => 'object',
                'properties' => [
                    'uuid' => ['type' => 'string','format' => 'uuid'],
                    'first_name' => ['type' => 'string'],
                    'email' => ['type' => 'string','format' => 'email'],
                    'owners' => [ // Response list of objects
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'owner_name' => ['type' => 'string'],
                            ]
                        ]
                    ],

                    /**
                     * Describe data coming back as
                     * 'key_0' => 'value_0'
                     * 'key_1' => 'value_1'
                     */

                    'map' => [
                        'description' => 'a short_name => long name map',
                        'summary' => 'a short_name => long name map',
                        'type' => 'object',
                        'properties' => [
                            'default' => ['type' => 'string'],
                         ],
                        'additionalProperties' => ['type' => 'string']

                    ]
                ]
            ],
            'get_minimal_user_response_schema' => [
                'title' => 'Minimal user schema title', // Important for rendering while not needing to be unique
                'type' => 'object',
                'properties' => [
                    'uuid' => ['type' => 'string','format' => 'uuid'],
                    'first_name' => ['type' => 'string'],
                ]
            ]
        ],

        'examples' => [

            // Request Examples
            'file_example' => [
                'summary' => 'File Request Body Summary',
                'value' => [
                    'first_name' => 'John',
                    'email' => 'email@example.com',
                    'file' => '1101010101010101010101010101',
                ],
            ],

            // Response Examples
            'user_example' => [
                'summary' => 'User response',
                'value' => [
                    'uuid' => 'ce9280e2-1d3f-466d-9e81-6cd1ec9581d1',
                    'first_name' => 'John',
                    'email' => 'email@example.com',
                    'owners' => [
                        ['owner_name' => 'Jerry','new prop' => 'new prop value'],
                        ['owner_name' => 'Thomas']
                    ],
                    'map' => [
                        'name' => 'long_name'
                    ]
                ],
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
