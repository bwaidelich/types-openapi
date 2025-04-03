<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#request-body-object
 */
final class RequestBodyObject implements JsonSerializable
{
    /**
     * @param array<string, mixed> $meta key/value for custom metadata. This is not part of the OpenAPI specification and won't appear in the JSON serialized format
     */
    public function __construct(
        public readonly MediaTypeObjectMap $content,
        public readonly null|string $description = null,
        public readonly null|bool $required = null,
        public readonly array $meta = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        unset($vars['meta']);
        return array_filter($vars, static fn($i) => $i !== null);
    }
}
