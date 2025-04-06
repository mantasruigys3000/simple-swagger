<?php

namespace Mantasruigys3000\SimpleSwagger\helpers;

enum EnumHelper
{
    /**
     * Get values from enum cases
     *
     * @param string $enum
     * @return array
     */
    public static function enumValues(string $enum) : array
    {
        $values = [];

        foreach ($enum::cases() as $case){
            $values[] = $case->value;
        }

        return $values;
    }
}
