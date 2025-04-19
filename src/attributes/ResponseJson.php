<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger\attributes;

use Attribute;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\ResponseAttribute;
use Mantasruigys3000\SimpleSwagger\helpers\ReferenceHelper;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class ResponseJson implements ResponseAttribute
{
    public function __construct(protected int $status, public string $jsonResponse,public string $description = '') {}

    public function getStatus(): int
    {
        return $this->status;
    }

    public function toArray(): array
    {
        // Get response bodies from the resource class and construct refs
        $bodies = (new $this->jsonResponse)();

        $schemaRefs = [];
        $exampleRefs = [];

        foreach ($bodies as $body) {
            $schemaRefs[] = ['$ref' => '#/components/schemas/'.ReferenceHelper::getResponseID($body, $this->jsonResponse)];
            $exampleRefs[$body->title] = ['$ref' => '#/components/examples/'.ReferenceHelper::getResponseExampleID($body, $this->jsonResponse)];
        }

        $content = [
            'application/json' => [
                'schema' => [
                    'oneOf' => $schemaRefs,
                ],
                'examples' => $exampleRefs,
            ],
        ];

        return [
            'description' => $this->description,
            'content' => $content,
        ];
    }
}
