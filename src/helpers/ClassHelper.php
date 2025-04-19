<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger\helpers;

class ClassHelper
{
    /**
     * Check if this class implements an interface
     */
    public static function implements(string $class, string $implements): bool
    {
        return in_array($implements, class_implements($class));
    }
}
