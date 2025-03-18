<?php

namespace Mantasruigys3000\SimpleSwagger;

use Symfony\Component\Yaml\Yaml;

class Writer
{
    public function __construct()
    {

    }

    public function write()
    {
        // Gather openapi php array

        $data = [
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
                ['url' => 'https://website.com']
            ],
            'paths' => [
                '/users' => [
                    'get' => [
                        'summary' => 'Get all users',
                        'description' => 'Get all users from the database',
                        'tags' => [
                            'users'
                        ]
                    ],
                    'post' => [
                        'summary' => 'Create a new user',
                        'description' => 'Create a new user and store it in the database',
                        'tags' => [
                            'users',
                        ]
                    ]
                ]
            ],
            'tags' =>[
                ['name' => 'users','description' => 'User APIS'],
            ],
            'components' => [
                ''
            ]
        ];

        // Turn to yaml

        $yaml = Yaml::dump($data);

        // Put yaml to file

        $dir = explode(DIRECTORY_SEPARATOR,config('docs.output_path'));
        array_pop($dir);
        $dir = implode(DIRECTORY_SEPARATOR,$dir);
        if (! is_dir($dir))
        {
            mkdir($dir,recursive:true);
        }

        file_put_contents(config('docs.output_path'),$yaml);

    }
}