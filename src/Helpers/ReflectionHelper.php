<?php

namespace Mantasruigys3000\SimpleSwagger\Helpers;

use Illuminate\Foundation\Http\FormRequest;
use Mantasruigys3000\SimpleSwagger\Attributes\interfaces\ResponseAttribute;
use Mantasruigys3000\SimpleSwagger\Attributes\ResponseJson;
use Mantasruigys3000\SimpleSwagger\Attributes\ResponseResource;
use Mantasruigys3000\SimpleSwagger\Traits\HasRequestBodies;
use ReflectionAttribute;
use ReflectionMethod;

class ReflectionHelper
{
    /**
     * This returns all used request classes as the array key
     *
     * @param ReflectionMethod $method
     * @return array
     */
    public static function getRequestClasses(ReflectionMethod $method) : array
    {
        $classes = [];

        $methodParams = $method->getParameters();

        if (count($methodParams) > 0) {
            $requestParam = $methodParams[0]->getType()->getName();

            if (is_subclass_of($requestParam, FormRequest::class)) {

                $traits = class_uses($requestParam);
                if (in_array(HasRequestBodies::class, $traits)) {
                    $classes[$requestParam] = 1;
                }
            }
        }

        return $classes;
    }

    /**
     * This returns all resource classes as the array key
     *
     * @param ReflectionMethod $method
     * @return array
     */
    public static function getResourceClasses(ReflectionMethod $method) : array
    {
        $classes = [];

        $responseAttributes = $method->getAttributes(ResponseAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($responseAttributes as $responseAttribute) {
            /**
             * @var ResponseAttribute $responseObject
             */
            $responseObject = $responseAttribute->newInstance();
            if ($responseObject instanceof ResponseResource) {
                $classes[$responseObject->resourceClass] = 1;
            }

            if ($responseObject instanceof ResponseJson) {
                $classes[$responseObject->jsonResponse] = 1;
            }
        }

        return $classes;
    }
}