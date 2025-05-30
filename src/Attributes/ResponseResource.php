<?php

namespace Mantasruigys3000\SimpleSwagger\Attributes;

use Attribute;
use Mantasruigys3000\SimpleSwagger\Attributes\interfaces\ResponseAttribute;
use Mantasruigys3000\SimpleSwagger\Helpers\ReferenceHelper;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
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
        $exampleRefs = $this->collection ?
            ReferenceHelper::getResponseCollectionExampleReferences($this->resourceClass) :
            ReferenceHelper::getResponseExampleReferences($this->resourceClass);

        $nonCollectionContent = [
            'application/json' => [
                'schema' => [
                    'oneOf' => $schemaRefs
                ],
                'examples' => $exampleRefs
            ]
        ];

        $collectionContent = [
            'application/json' => [
                'schema' => [
                    'type' => 'array',
                    'items' => [
                        'oneOf' => $schemaRefs
                    ]
                ],
                'examples' => $exampleRefs
            ]
        ];

        $content = $this->collection ? $collectionContent : $nonCollectionContent;

        return [
            'description' => $this->description,
            'content' => $content,
        ];
    }
}