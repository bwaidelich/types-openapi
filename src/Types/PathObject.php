<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#path-object
 */
final class PathObject implements JsonSerializable
{
    public function __construct(
        public readonly null|string $ref = null,
        public readonly null|string $summary = null,
        public readonly null|string $description = null,
        public readonly null|OperationObject $get = null,
        public readonly null|OperationObject $put = null,
        public readonly null|OperationObject $post = null,
        public readonly null|OperationObject $delete = null,
        public readonly null|OperationObject $options = null,
        public readonly null|OperationObject $head = null,
        public readonly null|OperationObject $patch = null,
        public readonly null|OperationObject $trace = null,
        public readonly null|ServerObjects $servers = null,
        public readonly null|ParameterObjects $parameters = null,
    ) {}

    /**
     * @return iterable<string, OperationObject>
     */
    public function operationsByMethod(): iterable
    {
        foreach (['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'] as $httpMethod) {
            $this->{$httpMethod} !== null && yield $httpMethod => $this->{$httpMethod};
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
