<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#response-object
 */
final class ResponseObject implements JsonSerializable
{
    public function __construct(
        public readonly string $description,
        public readonly HeaderOrReferenceObjectMap|null $headers = null,
        public readonly MediaTypeObjectMap|null $content = null,
        public readonly LinkOrReferenceObjectMap|null $links = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
