<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

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
        public readonly array|null $tags = null,
        public readonly string|null $summary = null,
        public readonly string|null $description = null,
        public readonly ExternalDocumentationObject|null $externalDocs = null,
        public readonly string|null $operationId = null,
        public readonly ParameterOrReferenceObjects|null $parameters = null,
        public readonly RequestBodyObject|ReferenceObject|null $requestBody = null,
        public readonly ResponsesObject|null $responses = null,
        // TODO add callbacks
        public readonly bool|null $deprecated = null,
        public readonly SecurityRequirementObject|null $security = null,
        public readonly ServerObjects|null $servers = null,
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
