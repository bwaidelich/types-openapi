<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#encoding-object
 */
final class EncodingObject implements JsonSerializable
{
    public function __construct(
        public readonly string|null $contentType = null,
        public readonly HeaderOrReferenceObjectMap|null $headers = null,
        public readonly string|null $style = null,
        public readonly bool|null $explode = null,
        public readonly bool|null $allowReserverd = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
