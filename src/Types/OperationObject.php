<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#operation-object
 */
final class OperationObject implements JsonSerializable
{
    /**
     * @param array<string>|null $tags
     */
    public function __construct(
        public readonly ?array $tags = null,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly ?ExternalDocumentationObject $externalDocs = null,
        public readonly ?string $operationId = null,
        public readonly ?ParameterOrReferenceObjects $parameters = null,
        public readonly RequestBodyObject|ReferenceObject|null $requestBody = null,
        public readonly ?ResponsesObject $responses = null,
        // TODO add callbacks
        public readonly ?bool $deprecated = null,
        // TODO add security
        public readonly ?ServerObjects $servers = null,
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
