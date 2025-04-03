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
     * @param array<string, mixed> $meta key/value for custom metadata. This is not part of the OpenAPI specification and won't appear in the JSON serialized format
     */
    public function __construct(
        public readonly null|array $tags = null,
        public readonly null|string $summary = null,
        public readonly null|string $description = null,
        public readonly null|ExternalDocumentationObject $externalDocs = null,
        public readonly null|string $operationId = null,
        public readonly null|ParameterOrReferenceObjects $parameters = null,
        public readonly RequestBodyObject|ReferenceObject|null $requestBody = null,
        public readonly null|ResponsesObject $responses = null,
        // TODO add callbacks
        public readonly null|bool $deprecated = null,
        public readonly null|SecurityRequirementObject $security = null,
        public readonly null|ServerObjects $servers = null,
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
