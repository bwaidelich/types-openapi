<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;
use Wwwision\TypesJSONSchema\Types as JSON;

/**
 * @see https://swagger.io/specification/#media-type-object
 */
final class MediaTypeObject implements JsonSerializable
{
    public function __construct(
        public readonly ?JSON\Schema $schema = null,
        // TODO add example
        public readonly ?ExampleOrReferenceObjectMap $examples = null,
        public readonly ?EncodingObjectMap $encoding = null,
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
