<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#encoding-object
 */
final class EncodingObject implements JsonSerializable
{
    public function __construct(
        public readonly null|string $contentType = null,
        public readonly null|HeaderOrReferenceObjectMap $headers = null,
        public readonly null|string $style = null,
        public readonly null|bool $explode = null,
        public readonly null|bool $allowReserverd = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
