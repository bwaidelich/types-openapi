<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#http-codes
 */
final class HttpStatusCode implements JsonSerializable
{
    private function __construct(
        public readonly int $value,
    ) {
        // TODO validate
    }

    public static function fromInteger(int $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
