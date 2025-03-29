<?php

namespace Mantasruigys3000\SimpleSwagger\data;

use phpDocumentor\Reflection\Types\CallableParameter;

class SchemaFactory
{
    /**
     * @var SchemaProperty[]
     */
    public array $properties;

    public function string(string $name, string $description, string $example)
    {
        return $this->addProperty($name,'string');
    }

    public function uuid(string $name, string $description) : SchemaProperty{
        return $this->addProperty($name,'string')->uuid();
    }

    private function addProperty(string $name,string $type) : SchemaProperty
    {
        $property = new SchemaProperty();
        $property->name = $name;
        $property->type = $type;

        $this->properties[] = $property;
        return $property;
    }

    public function getPropertiesArray() : array
    {
        $properties = [];

        foreach ($this->properties as $property)
        {
            $arr = [
                'type' => $property->type
            ];

            if (isset($property->format)){
                $arr['format'] = $property->format;
            }

            $properties[$property->name] = $arr;
        }

        return $properties;
    }

    public function getRequired() : array
    {
        $required = [];
        foreach ($this->properties as $property){
            if ($property->required){
                $required[] = $property->name;
            }
        }

        return $required;
    }
}