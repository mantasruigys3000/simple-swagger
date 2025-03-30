<?php

namespace Mantasruigys3000\SimpleSwagger\data;

use function PHPUnit\Framework\isInstanceOf;

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
    public $items = [];

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

    public function array()
    {
        $arrayType = $this->type;

        $this->type = 'array';
        $this->items = [
            'type' => $arrayType,
        ];

        if (isset($this->format)){
            $this->items['format'] = $this->format;
        }

        $this->format = null;
        $this->example = [$this->example];

        if (isset($this->schema))
        {
            $this->items['properties'] = $this->schema->getPropertiesArray();
            $this->example = [
                $this->schema->getExampleArray('')
            ];
        }

        unset($this->schema);
        return $this;
    }

}
