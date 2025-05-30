<?php

namespace Mantasruigys3000\SimpleSwagger\Attributes\interfaces;

use Mantasruigys3000\SimpleSwagger\Data\RouteParameter;

interface RouteParameterAttribute
{
    public function getRouteParameter() : RouteParameter;
}