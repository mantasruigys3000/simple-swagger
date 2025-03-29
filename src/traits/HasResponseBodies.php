<?php

namespace Mantasruigys3000\SimpleSwagger\traits;

trait HasResponseBodies
{
    public abstract static function responseBodies() : array;

    public static function getResponseSchemaID() : string {
        return static::class;
    }
}