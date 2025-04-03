<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#example-object
 */
final class ExampleObject implements JsonSerializable
{
    /**
     * @param int|float|string|bool|array<mixed>|null $value
     */
    public function __construct(
        public readonly null|string $summary = null,
        public readonly null|string $description = null,
        public readonly int|float|string|bool|array|null $value = null,
        public readonly null|string $externalValue = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
