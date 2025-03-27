<?php

namespace Mantasruigys3000\SimpleSwagger\attributes;

use Attribute;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\RouteParameterAttribute;
use Mantasruigys3000\SimpleSwagger\data\RouteParameter;
use Mantasruigys3000\SimpleSwagger\enums\ParameterSchema;
use Mantasruigys3000\SimpleSwagger\enums\RouteParameterType;

#[Attribute]
class PathParameter implements RouteParameterAttribute
{
    public function __construct(public string $name,public ParameterSchema $type,public string $description,public bool $required = true,public ?string $example = null,public ?string $default = null)
    {

    }

    public function getRouteParameter(): RouteParameter
    {
        $type = $this->type->getPrimitiveType();
        $format = $this->type->getFormat();

        // if the example is null, we can check if the type comes with a defained example method
        if (is_null($this->example)){
            $this->example = $this->type->getExample();
        }

        return new RouteParameter(
            RouteParameterType::PATH,
            $this->name,
            $this->description,
            $type,
            $this->required,
            $format,
            $this->default,
            $this->example
        );
    }
}
