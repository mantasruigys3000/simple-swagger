<?php

namespace Mantasruigys3000\SimpleSwagger\data;

class SchemaProperty
{
    public string $name;
    public string $type;
    public ?string $format;
    public bool $required = false;
    public mixed $example = '';
    public string $description;
    public array $refs = [];
    public string $resource;
    public SchemaFactory $schema;

    public function required() : self
    {
        $this->required = true;
        return $this;
    }

    public function format(string $format)
    {
        $this->format = $format;
        return $this;
    }

    public function uuid() : self
    {
        return $this->format('uuid');
    }

    public function example(mixed $example) : self
    {
        $this->example = $example;
        return $this;
    }

}