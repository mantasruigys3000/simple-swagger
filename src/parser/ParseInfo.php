<?php

namespace Mantasruigys3000\SimpleSwagger\parser;

class ParseInfo
{
    public function __construct(
        public array $missingKeys,
        public array $overDocumentedKeys,
    )
    {

    }
}