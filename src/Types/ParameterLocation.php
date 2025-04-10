<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#parameter-object
 */
enum ParameterLocation implements JsonSerializable
{
    case query;
    case header;
    case path;
    case cookie;

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
