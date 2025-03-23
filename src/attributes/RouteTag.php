<?php

namespace Mantasruigys3000\SimpleSwagger\attributes;

use Attribute;
use Composer\Pcre\PHPStan\PregMatchParameterOutTypeExtension;

#[Attribute]
class RouteTag
{
    public function __construct(public string $name, public string $description) {}

    public function toArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}