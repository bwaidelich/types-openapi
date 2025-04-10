<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;
use Wwwision\JsonSchema as Json;

/**
 * @see https://swagger.io/specification/#media-type-object
 */
final class MediaTypeObject implements JsonSerializable
{
    /**
     * @param array<string, mixed> $meta key/value for custom metadata. This is not part of the OpenAPI specification and won't appear in the JSON serialized format
     */
    public function __construct(
        public readonly null|Json\Schema $schema = null,
        // TODO add example
        public readonly null|ExampleOrReferenceObjectMap $examples = null,
        public readonly null|EncodingObjectMap $encoding = null,
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
