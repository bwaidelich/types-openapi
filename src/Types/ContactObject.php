<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#contact-object
 */
final class ContactObject implements JsonSerializable
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $url = null,
        public readonly ?string $email = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn ($i) => $i !== null);
    }
}
