<?php

namespace Mantasruigys3000\SimpleSwagger\attributes;

use Attribute;
#[Attribute]
class RouteDescription
{
    public function __construct(public string $summary,public string $description)
    {

    }
}