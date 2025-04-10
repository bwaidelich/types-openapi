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
        public readonly null|string $jsonSchemaDialect = null,
        public readonly null|ServerObjects $servers = null,
        public readonly null|PathsObject $paths = null,
        // TODO add webhooks
        public readonly null|ComponentsObject $components = null,
        public readonly null|SecurityRequirementObject $security = null,
        public readonly null|array $tags = null,
        public readonly null|ExternalDocumentationObject $externalDocs = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
