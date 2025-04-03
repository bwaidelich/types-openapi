<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#oauth-flow-object
 */
final class OAuthFlowObject implements JsonSerializable
{
    /**
     * @param array<string, string> $scopes The available scopes for the OAuth2 security scheme. A map between the scope name and a short description for it. The map MAY be empty.
     */
    public function __construct(
        public readonly string $authorizationUrl,
        public readonly string $tokenUrl,
        public readonly array $scopes,
        public readonly null|string $refreshUrl = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
