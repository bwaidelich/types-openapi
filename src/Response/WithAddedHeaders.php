<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Response;

interface WithAddedHeaders
{
    /**
     * @return array<string, string|array<string>>
     */
    public function getAddedHeaders(): array;
}
