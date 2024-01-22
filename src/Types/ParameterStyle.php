<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#style-values
 */
enum ParameterStyle implements JsonSerializable
{
    case matrix;
    case label;
    case form;
    case simple;
    case spaceDelimited;
    case pipeDelimited;
    case deepObject;

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
