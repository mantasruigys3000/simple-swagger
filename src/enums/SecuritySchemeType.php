<?php

namespace Mantasruigys3000\SimpleSwagger\enums;

enum SecuritySchemeType : string
{
    case HTTP = 'http';
    case API_KEY = 'apiKey';

    case OAUTH2 = 'oauth2';
}