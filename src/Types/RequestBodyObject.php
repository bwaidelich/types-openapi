<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#request-body-object
 */
final class RequestBodyObject implements JsonSerializable
{
    public function __construct(
        public readonly MediaTypeObjectMap $content,
        public readonly ?string $description = null,
        public readonly ?bool $required = null,
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
