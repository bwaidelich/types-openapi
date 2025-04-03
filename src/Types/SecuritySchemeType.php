<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#security-scheme-object
 */
enum SecuritySchemeType implements JsonSerializable
{
    case apiKey;
    case http;
    case mutualTLS;
    case oauth2;
    case openIdConnect;

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
