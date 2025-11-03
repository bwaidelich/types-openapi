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
        public readonly string|null $ref = null,
        public readonly string|null $summary = null,
        public readonly string|null $description = null,
        public readonly OperationObject|null $get = null,
        public readonly OperationObject|null $put = null,
        public readonly OperationObject|null $post = null,
        public readonly OperationObject|null $delete = null,
        public readonly OperationObject|null $options = null,
        public readonly OperationObject|null $head = null,
        public readonly OperationObject|null $patch = null,
        public readonly OperationObject|null $trace = null,
        public readonly ServerObjects|null $servers = null,
        public readonly ParameterObjects|null $parameters = null,
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
