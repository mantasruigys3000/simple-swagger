<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger\Interfaces;

use Mantasruigys3000\SimpleSwagger\Data\ResponseBody;

interface JsonResponse
{
    /**
     * @return ResponseBody[]
     */
    public function __invoke(): array;
}
