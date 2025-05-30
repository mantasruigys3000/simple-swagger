<?php

namespace Mantasruigys3000\SimpleSwagger\Data;

class ResponseBody
{
    public SchemaFactory $schemaFactory;
    public string $title;
    public static function make(string $title,callable $function) : static
    {
        $body = new self();
        $body->schemaFactory = new SchemaFactory();
        $body->title = $title;
        $function($body->schemaFactory);

        return $body;
    }
}