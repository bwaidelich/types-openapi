<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#security-scheme-object
 */
enum SecuritySchemeApiKeyLocation implements JsonSerializable
{
    case query;
    case header;
    case cookie;

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
