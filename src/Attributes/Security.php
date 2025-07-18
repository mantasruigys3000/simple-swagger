<?php

namespace Mantasruigys3000\SimpleSwagger\Attributes;

use Attribute;

#[Attribute]
class Security
{
    public function __construct(public string $name, public array $scopes = [])
    {

    }

    public function toArray()
    {
        return [$this->name => $this->scopes];
    }
}