<?php

namespace Mantasruigys3000\SimpleSwagger\data;

use Illuminate\Support\Str;
use Mantasruigys3000\SimpleSwagger\helpers\ReferenceHelper;
use phpDocumentor\Reflection\Types\CallableParameter;
use function Orchestra\Testbench\container;

class SchemaFactory
{
    /**
     * @var SchemaProperty[]
     */
    public array $properties;

    public function string(string $name, string $description, string $example)
    {
        return $this->addProperty($name,'string',$description,$example);
    }

    public function uuid(string $name, string $description) : SchemaProperty{
        return $this->addProperty($name,'string',$description,Str::uuid()->toString())->uuid();
    }

    public function datetime(string $name, string $description)
    {
        return $this->addProperty($name,'string',$description,now()->toIso8601String())->format('date-time');
    }

    public function resource(string $name,string $description,string $class)
    {
        $property = $this->addProperty($name,'object',$description,'example');
        //$property->refs[] = ReferenceHelper::getResponseSchemaReferences($class);
        $property->resource = $class;
        return $property;
    }

    private function addProperty(string $name,string $type,string $description,string $example) : SchemaProperty
    {
        $property = new SchemaProperty();
        $property->name = $name;
        $property->type = $type;
        $property->description = $description;
        $property->example = $example;

        $this->properties[] = $property;
        return $property;
    }

    public function getPropertiesArray() : array
    {
        $properties = [];

        foreach ($this->properties as $property)
        {
            if (count($property->refs) > 0){
                $properties[$property->name] = [
                    'oneOf' => $property->refs[0],
                ];

                continue;
            }

            if (isset($property->resource)){
                $properties[$property->name] = [
                    'oneOf' => ReferenceHelper::getResponseSchemaReferences($property->resource),
                ];

                continue;
            }

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

    /**
     * @return array
     */
    public function getExampleArray() : array
    {
        $examples = [];
        foreach ($this->properties as $property){
            $examples[$property->name] = $property->example;
        }

        return $examples;
    }
}