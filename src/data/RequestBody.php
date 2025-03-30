<?php

namespace Mantasruigys3000\SimpleSwagger\data;

class RequestBody
{
    public SchemaFactory $schemaFactory;
    public static function make(string $title,callable $function)
    {
        $body = new self();
        $body->schemaFactory = new SchemaFactory();
        $body->title = $title;
        $function($body->schemaFactory);

        return $body;
    }
}