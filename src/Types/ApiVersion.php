<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * User defined version of the API
 * @see https://swagger.io/specification/#info-object
 */
final class ApiVersion implements JsonSerializable
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

    public static function default(): self
    {
        return new self('0.0.0');
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
