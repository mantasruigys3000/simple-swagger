<?php

namespace Mantasruigys3000\SimpleSwagger\Enums;

use Illuminate\Support\Str;

enum ParameterSchema : string
{
    case INTEGER = 'integer';
    case STRING = 'string';
    case UUID = 'uuid';

    case DATE = 'date';

    public function getPrimitiveType(): string
    {
        return match ($this){
            self::INTEGER => 'integer',
            default => 'string'
        };

    }

    public function getFormat(): ?string
    {
        return match ($this){
            self::UUID => 'uuid',
            default => null,
        };

    }

    /**
     * For reusable example types
     *
     * @return ?string
     */
    public function getExample() : ?string
    {
        return match ($this){
            self::UUID => Str::uuid()->toString(),
            self::DATE => now()->toDateString(),
            default => null,
        };
    }

}
