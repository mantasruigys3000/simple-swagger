<?php

namespace Mantasruigys3000\SimpleSwagger\helpers;

use Illuminate\Support\Str;
use Mantasruigys3000\SimpleSwagger\data\RequestBody;
use Mantasruigys3000\SimpleSwagger\data\ResponseBody;

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

    public static function getRequestSchemaReferences(string $resourceClass) : array
    {
        $bodies = $resourceClass::requestBodies();

        $schemaRefs = [];

        foreach ($bodies as $body){
            $schemaRefs[] = ['$ref' => '#/components/schemas/' . ReferenceHelper::getRequestID($body,$resourceClass)];
        }

        return $schemaRefs;
    }

    public static function getRequestExampleReferences(string $resourceClass) : array
    {
        $bodies = $resourceClass::requestBodies();

        $exampleRefs = [];

        foreach ($bodies as $body){
            $exampleRefs[$body->title] = ['$ref' => '#/components/examples/' . ReferenceHelper::getRequestExampleID($body,$resourceClass)];
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