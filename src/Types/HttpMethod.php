<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

enum HttpMethod implements JsonSerializable
{
    case GET;
    case PUT;
    case POST;
    case DELETE;
    case OPTIONS;
    case HEAD;
    case PATCH;
    case TRACE;

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
