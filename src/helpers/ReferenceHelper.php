<?php

namespace Mantasruigys3000\SimpleSwagger\helpers;

use Illuminate\Support\Str;
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

    public static function getResponseExampleID(ResponseBody $body, string $class)
    {
        return self::getResponseID($body,$class) . '_example';
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
            $exampleRefs[] = ['$ref' => '#/components/examples/' . ReferenceHelper::getResponseExampleID($body,$resourceClass)];
        }

        return $exampleRefs;
    }
}