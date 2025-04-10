<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#components-object
 */
final class ComponentsObject implements JsonSerializable
{
    public function __construct(
        private readonly null|SchemaObjectMap $schemas = null,
        private readonly null|ResponseOrReferenceObjectMap $responses = null,
        private readonly null|ParameterOrReferenceObjects $parameters = null,
        private readonly null|ExampleOrReferenceObjectMap $examples = null,
        // TODO add requestBodies
        private readonly null|HeaderOrReferenceObjectMap $headers = null,
        private readonly null|SecuritySchemeOrReferenceObjectMap $securitySchemes = null,
        private readonly null|LinkOrReferenceObjectMap $links = null,
        // TODO add callbacks
        // TODO add pathItems
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
