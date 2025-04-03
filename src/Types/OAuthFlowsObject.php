<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#oauth-flows-object
 */
final class OAuthFlowsObject implements JsonSerializable
{
    public function __construct(
        public readonly null|OAuthFlowObject $implicit = null,
        public readonly null|OAuthFlowObject $password = null,
        public readonly null|OAuthFlowObject $clientCredentials = null,
        public readonly null|OAuthFlowObject $authorizationCode = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
