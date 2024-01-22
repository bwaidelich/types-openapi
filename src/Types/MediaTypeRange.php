<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * A Media Type or Media Type Range (@see https://datatracker.ietf.org/doc/html/rfc7231#appendix--d)
 */
final class MediaTypeRange implements JsonSerializable
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
