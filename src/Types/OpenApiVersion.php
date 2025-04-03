<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#openapi-object
 */
final class OpenApiVersion implements JsonSerializable
{
    private function __construct(
        public readonly string $value,
    ) {
        // TODO validate
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function current(): self
    {
        return new self('3.0.3');
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
