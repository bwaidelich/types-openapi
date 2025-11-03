<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#server-variable-object
 */
final class ServerVariableObject implements JsonSerializable
{
    /**
     * @param array<string>|null $enum
     */
    public function __construct(
        public readonly string $default,
        public readonly array|null $enum = null,
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
