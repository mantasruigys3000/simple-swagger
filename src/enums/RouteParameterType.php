<?php

namespace Mantasruigys3000\SimpleSwagger\enums;

enum RouteParameterType : string
{
    case PATH = 'path';
    case QUERY = 'query';
    case HEADER = 'header';
}