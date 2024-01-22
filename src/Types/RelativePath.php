<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#openapi-object
 */
final class RelativePath implements JsonSerializable
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

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
