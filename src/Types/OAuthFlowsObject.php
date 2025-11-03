<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#oauth-flows-object
 */
final class OAuthFlowsObject implements JsonSerializable
{
    public function __construct(
        public readonly OAuthFlowObject|null $implicit = null,
        public readonly OAuthFlowObject|null $password = null,
        public readonly OAuthFlowObject|null $clientCredentials = null,
        public readonly OAuthFlowObject|null $authorizationCode = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
