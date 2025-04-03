<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#link-object
 */
final class LinkObject implements JsonSerializable
{
    /**
     * @param array<string, mixed>|null $parameters
     */
    public function __construct(
        public readonly null|string $operationRef = null,
        public readonly null|string $operationId = null,
        public readonly null|array $parameters = null,
        // TODO add requestBody
        public readonly null|string $description = null,
        public readonly null|ServerObject $server = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
