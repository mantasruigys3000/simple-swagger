<?php

namespace Mantasruigys3000\SimpleSwagger\Attributes;

use Attribute;
#[Attribute]
class RouteDescription
{
    public function __construct(public string $summary,public string $description)
    {

    }
}
