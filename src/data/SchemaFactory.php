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
        return $this->addProperty($name,'string',$description,$example)->example($example);
    }

    public function uuid(string $name, string $description) : SchemaProperty
    {
        return $this->addProperty($name,'string',$description)->example(Str::uuid()->toString())->uuid();
    }

    public function datetime(string $name, string $description)
    {
        return $this->addProperty($name,'string',$description)->example(now()->toIso8601String())->format('date-time');
    }


    public function object(string $name, string $description,callable $function)
    {
        $object = new SchemaFactory();
        $function($object);

        $property = $this->addProperty($name,'object',$description,'object example');
        $property->schema = $object;
        return $property;
    }

    public function resource(string $name,string $description,string $class)
    {
        $property = $this->addProperty($name,'object',$description,'example');
        $property->resource = $class;
        return $property;
    }

    private function addProperty(string $name,string $type,string $description) : SchemaProperty
    {
        $property = new SchemaProperty();
        $property->name = $name;
        $property->type = $type;
        $property->description = $description;

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

            if (isset($property->resource) && $property->type === 'object'){

                $properties[$property->name] = [
                    'oneOf' => ReferenceHelper::getResponseSchemaReferences($property->resource),
                ];

                continue;
            }

            if (isset($property->resource) && $property->type === 'array'){

                $properties[$property->name]['items']['oneOf'] = ReferenceHelper::getResponseSchemaReferences($property->resource);
                continue;
            }

            $arr = [
                'type' => $property->type
            ];

            if (isset($property->schema)){
                $arr['properties'] = $property->schema->getPropertiesArray();
            }

            if (isset($property->format)){
                $arr['format'] = $property->format;
            }

            if (filled($property->items)){
                $arr['items'] = $property->items;
            }

            if (isset($property->minLength)){
                $arr['minLength'] = $property->minLength;
            }

            if (isset($property->maxLength)){
                $arr['maxLength'] = $property->maxLength;
            }

            if (isset($property->min)){
                $arr['min'] = $property->min;
            }

            if (isset($property->max)){
                $arr['max'] = $property->max;
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
    public function getExampleArray(string $resourceClass,array $ignoreClasses = []) : array
    {
        $examples = [];
        foreach ($this->properties as $property){

            if(isset($property->schema))
            {
                $examples[$property->name] = $property->schema->getExampleArray($resourceClass);
                continue;
            }

            if (isset($property->resource))
            {
                // Get the first response body
                // TODO: validate it exists
                if (! in_array($property->resource,$ignoreClasses)){
                    $body = $property->resource::responseBodies()[0];
                    $ignoreClasses[] = $resourceClass;
                    if ($property->type === 'array')
                    {
                        $examples[$property->name] = [$body->schemaFactory->getExampleArray($property->resource,$ignoreClasses)];
                    }
                    else{
                        $examples[$property->name] = $body->schemaFactory->getExampleArray($property->resource,$ignoreClasses);
                    }
                    continue;
                }

                // Here if the class is to be ignored
                // TODO need to come up with alternative ways to handle recursive examples, we could give to option to omit them
                $examples[$property->name] = '{recursive}';
                continue;
            }

            $examples[$property->name] = $property->example;
        }

        return $examples;
    }
}
