<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#header-object
 */
final class HeaderObject implements JsonSerializable
{
    public function __construct(
        public readonly null|string $description = null,
        public readonly null|bool $required = null,
        public readonly null|bool $deprecated = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
