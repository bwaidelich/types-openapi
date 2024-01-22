<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#openapi-object
 */
final class OpenAPIObject implements JsonSerializable
{
    /**
     * @param array<string>|null $tags
     */
    public function __construct(
        public readonly OpenApiVersion $openapi,
        public readonly InfoObject $info,
        public readonly ?string $jsonSchemaDialect = null,
        public readonly ?ServerObjects $servers = null,
        public readonly ?PathsObject $paths = null,
        // TODO add webhooks
        public readonly ?ComponentsObject $components = null,
        // TODO add security
        public readonly ?array $tags = null,
        public readonly ?ExternalDocumentationObject $externalDocs = null,
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
