<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Response;

interface WithAddedHeaders
{
    /**
     * @return array<string, string|array<string>>
     */
    public function getAddedHeaders(): array;
}
