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
        private readonly SchemaObjectMap|null $schemas = null,
        private readonly ResponseOrReferenceObjectMap|null $responses = null,
        private readonly ParameterOrReferenceObjects|null $parameters = null,
        private readonly ExampleOrReferenceObjectMap|null $examples = null,
        // TODO add requestBodies
        private readonly HeaderOrReferenceObjectMap|null $headers = null,
        private readonly SecuritySchemeOrReferenceObjectMap|null $securitySchemes = null,
        private readonly LinkOrReferenceObjectMap|null $links = null,
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
