<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#openapi-object
 */
final class OpenApiObject implements JsonSerializable
{
    /**
     * @param array<string>|null $tags
     */
    public function __construct(
        public readonly OpenApiVersion $openapi,
        public readonly InfoObject $info,
        public readonly string|null $jsonSchemaDialect = null,
        public readonly ServerObjects|null $servers = null,
        public readonly PathsObject|null $paths = null,
        // TODO add webhooks
        public readonly ComponentsObject|null $components = null,
        public readonly SecurityRequirementObject|null $security = null,
        public readonly array|null $tags = null,
        public readonly ExternalDocumentationObject|null $externalDocs = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
