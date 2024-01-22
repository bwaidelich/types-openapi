<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#components-object
 */
final class ComponentsObject implements JsonSerializable
{
    public function __construct(
        private readonly ?SchemaObjectMap $schemas = null,
        private readonly ?ResponseOrReferenceObjectMap $responses = null,
        private readonly ?ParameterOrReferenceObjects $parameters = null,
        private readonly ?ExampleOrReferenceObjectMap $examples = null,
        // TODO add requestBodies
        private readonly ?HeaderOrReferenceObjectMap $headers = null,
        // TODO add securitySchemes
        private readonly ?LinkOrReferenceObjectMap $links = null,
        // TODO add callbacks
        // TODO add pathItems
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
