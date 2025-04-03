<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

/**
 * @see https://swagger.io/specification/#external-documentation-object
 */
final class ExternalDocumentationObject
{
    public function __construct(
        public readonly string $url,
        public readonly null|string $description = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
