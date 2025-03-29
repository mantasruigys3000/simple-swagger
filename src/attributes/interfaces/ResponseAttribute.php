<?php

namespace Mantasruigys3000\SimpleSwagger\attributes\interfaces;

use Attribute;

#[Attribute]
interface ResponseAttribute
{
    public function getStatus() : int;

    public function toArray() : array;
}