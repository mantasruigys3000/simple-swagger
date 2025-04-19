<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger\interfaces;

use Mantasruigys3000\SimpleSwagger\data\ResponseBody;

interface JsonResponse
{
    /**
     * @return ResponseBody[]
     */
    public function __invoke(): array;
}
