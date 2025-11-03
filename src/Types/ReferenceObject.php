<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#reference-object
 */
final class ReferenceObject implements JsonSerializable
{
    public function __construct(
        public readonly string $ref,
        public readonly string|null $summary = null,
        public readonly string|null $description = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
