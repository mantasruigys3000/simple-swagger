<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger\Helpers;

class ClassHelper
{
    /**
     * Check if this class implements an interface
     */
    public static function implements(string $class, string $implements): bool
    {
        return in_array($implements, class_implements($class));
    }

    /**
     * Check if class uses a trait
     *
     * @param string $class
     * @param string $trait
     * @return bool
     */
    public static function uses(string $class,string $trait)
    {
        return in_array($trait,class_uses($class));
    }
}
