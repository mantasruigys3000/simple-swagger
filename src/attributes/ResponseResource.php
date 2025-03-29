<?php

namespace Mantasruigys3000\SimpleSwagger\attributes;

use Attribute;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\ResponseAttribute;

#[Attribute]
class ResponseResource implements ResponseAttribute
{
    public function __construct(public int $status, public string $resourceClass,public bool $collection = false,public string $description = '')
    {

    }

    public function getStatus() : int
    {
        return $this->status;
    }

    public function toArray() : array
    {
        $schemaRefs = [];
        $exampleRefs = [];

        return [
            'description' => $this->description,
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
        ];
    }
}