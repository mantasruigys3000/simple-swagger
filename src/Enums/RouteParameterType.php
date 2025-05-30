<?php

namespace Mantasruigys3000\SimpleSwagger\Enums;

enum RouteParameterType : string
{
    case PATH = 'path';
    case QUERY = 'query';
    case HEADER = 'header';
}