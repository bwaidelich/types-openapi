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
        public readonly string|null $description = null,
        public readonly bool|null $required = null,
        public readonly bool|null $deprecated = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
