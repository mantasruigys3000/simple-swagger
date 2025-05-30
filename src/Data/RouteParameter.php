<?php

namespace Mantasruigys3000\SimpleSwagger\Data;

use Mantasruigys3000\SimpleSwagger\Enums\RouteParameterType;
use function PHPUnit\Framework\isInstanceOf;

class RouteParameter
{
    public function __construct(
        public RouteParameterType $type,
        public string $name,
        public string $description,
        public string $dataType,
        public bool $required = false,
        public ?string $format = null,
        public ?string $default = null,
        public ?string $example = null
        )
    {

    }

    public function toArray(): array
    {
        $array = [
            'in' => $this->type->value,
            'name' => $this->name,
            'schema' => [
                'type' => $this->dataType
            ],
            'required' => $this->required,
            'description' => $this->description,
        ];

        if ($this->format){
            $array['schema']['format'] = $this->format;
        }

        if ($this->default){
            $array['schema']['default'] = $this->default;
        }

        if ($this->example)
        {
            $array['example'] = $this->example;
        }

        return $array;
    }
}