<?php

namespace Mantasruigys3000\SimpleSwagger\attributes\interfaces;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
interface ResponseAttribute
{
    public function getStatus() : int;

    public function toArray() : array;
}