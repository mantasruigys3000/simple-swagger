<?php

namespace Mantasruigys3000\SimpleSwagger\attributes;

use Attribute;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\ResponseAttribute;
use Mantasruigys3000\SimpleSwagger\helpers\ReferenceHelper;

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
        // Get response bodies from the resource class and construct refs
        $bodies = $this->resourceClass::responseBodies();

        $schemaRefs = ReferenceHelper::getResponseSchemaReferences($this->resourceClass);
        $exampleRefs = ReferenceHelper::getResponseExampleReferences($this->resourceClass);

        return [
            'description' => $this->description,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'oneOf' => $schemaRefs
                    ],
                    'examples' => $exampleRefs
                ]
            ]
        ];
    }
}