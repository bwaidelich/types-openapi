<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#response-object
 */
final class ResponseObject implements JsonSerializable
{
    public function __construct(
        public readonly string $description,
        public readonly ?HeaderOrReferenceObjectMap $headers = null,
        public readonly ?MediaTypeObjectMap $content = null,
        public readonly ?LinkOrReferenceObjectMap $links = null,
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
