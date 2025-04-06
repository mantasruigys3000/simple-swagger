<?php

namespace Mantasruigys3000\SimpleSwagger\attributes;

use Attribute;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\ResponseAttribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class Response implements ResponseAttribute
{
    public function __construct(public int $status,public string $description = '')
    {

    }

    public function getStatus() : int
    {
        return $this->status;
    }

    public function toArray() : array
    {
        return [
            'description' => $this->description
        ];
    }
}