<?php

namespace Mantasruigys3000\SimpleSwagger\attributes\interfaces;

use Mantasruigys3000\SimpleSwagger\data\RouteParameter;

interface RouteParameterAttribute
{
    public function getRouteParameter() : RouteParameter;
}