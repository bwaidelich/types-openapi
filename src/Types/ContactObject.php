<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#contact-object
 */
final class ContactObject implements JsonSerializable
{
    public function __construct(
        public readonly null|string $name = null,
        public readonly null|string $url = null,
        public readonly null|string $email = null,
    ) {}

    /**
     * @param array{name?: string, url?: string, email?: string} $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            name: $array['name'] ?? null,
            url: $array['url'] ?? null,
            email: $array['email'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
