<?php

namespace Mantasruigys3000\SimpleSwagger\Attributes;

use Attribute;
use Mantasruigys3000\SimpleSwagger\Attributes\interfaces\RouteParameterAttribute;
use Mantasruigys3000\SimpleSwagger\Data\RouteParameter;
use Mantasruigys3000\SimpleSwagger\Enums\ParameterSchema;
use Mantasruigys3000\SimpleSwagger\Enums\RouteParameterType;

#[Attribute]
class QueryParameter implements RouteParameterAttribute
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
            RouteParameterType::QUERY,
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
