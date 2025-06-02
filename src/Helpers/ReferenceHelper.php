<?php

namespace Mantasruigys3000\SimpleSwagger\Helpers;

use Illuminate\Support\Str;
use Mantasruigys3000\SimpleSwagger\Data\RequestBody;
use Mantasruigys3000\SimpleSwagger\Data\ResponseBody;

class ReferenceHelper
{
    /**
     * @param ResponseBody $body
     * @param string $class
     * @return string
     */
    public static function getResponseID(ResponseBody $body, string $class) : string {
        $id = $body->title. $class;
        $id = Str::of($id)->snake()->replace('\\','')->replace('/','')->lower()->toString();

        return $id;
    }

    public static function getResponseExampleID(ResponseBody $body, string $class,bool $collection = false)
    {
        $id = self::getResponseID($body,$class) . '_example';
        if ($collection){
            $id = 'collection_'.$id;
        }
        return $id;
    }

    public static function getResponseSchemaReferences(string $resourceClass) : array
    {
        $bodies = $resourceClass::responseBodies();

        $schemaRefs = [];

        foreach ($bodies as $body){
            $schemaRefs[] = ['$ref' => '#/components/schemas/' . ReferenceHelper::getResponseID($body,$resourceClass)];
        }

        return $schemaRefs;
    }

    public static function getResponseExampleReferences(string $resourceClass) : array
    {
        $bodies = $resourceClass::responseBodies();

        $exampleRefs = [];

        foreach ($bodies as $body){
            $exampleRefs[$body->title] = ['$ref' => '#/components/examples/' . ReferenceHelper::getResponseExampleID($body,$resourceClass)];
        }

        return $exampleRefs;
    }

    public static function getResponseCollectionExampleReferences(string $resourceClass) : array
    {
        $bodies = $resourceClass::responseBodies();

        $exampleRefs = [];

        foreach ($bodies as $body){
            $exampleRefs[$body->title] = ['$ref' => '#/components/examples/' . ReferenceHelper::getResponseExampleID($body,$resourceClass,true)];
        }

        return $exampleRefs;
    }

    public static function getRequestBodyReference(RequestBody $body,string $resourceClass): string
    {
        return '#/components/schemas/' . ReferenceHelper::getRequestID($body,$resourceClass);
    }

    /**
     * Get request body example reference
     *
     * @param RequestBody $body
     * @param string $resourceClass
     * @return string
     */
    public static function getRequestBodyExampleReference(RequestBody $body, string $resourceClass) : string
    {
        return '#/components/examples/' . ReferenceHelper::getRequestExampleID($body,$resourceClass);
    }

    public static function getRequestSchemaReferences(string $resourceClass) : array
    {
        $bodies = $resourceClass::requestBodies();

        $schemaRefs = [];

        foreach ($bodies as $body){
            $schemaRefs[] = ['$ref' => self::getRequestBodyReference($body,$resourceClass)];
        }

        return $schemaRefs;
    }

    public static function getRequestSchemaContentType(string $resourceClass) : array
    {
        $types = [];

        $bodies = $resourceClass::requestBodies();

        foreach ($bodies as $body){
            $id = ReferenceHelper::getRequestID($body,$resourceClass);
            $types[$id] = 'application/json';
        }

        return $types;
    }

    public static function getRequestExampleReferences(string $resourceClass) : array
    {
        $bodies = $resourceClass::requestBodies();

        $exampleRefs = [];

        foreach ($bodies as $body){
            $exampleRefs[$body->title] = ['$ref' => self::getRequestBodyExampleReference($body,$resourceClass)];
        }

        return $exampleRefs;
    }

    public static function getRequestID(RequestBody $body, string $class) : string {
        $id = $body->title. $class;
        $id = Str::of($id)->snake()->replace('\\','')->replace('/','')->lower()->toString();

        return $id;
    }

    public static function getRequestExampleID(RequestBody $body, string $class)
    {
        return self::getRequestID($body,$class) . '_example';
    }

}