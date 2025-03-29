<?php

namespace Mantasruigys3000\SimpleSwagger\data;

class SchemaProperty
{
    public string $name;
    public string $type;
    public ?string $format;
    public bool $required = false;
    public string $example;
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

    public function uuid()
    {
        return $this->format('uuid');
    }

}