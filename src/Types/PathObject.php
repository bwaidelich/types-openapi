<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#path-object
 */
final class PathObject implements JsonSerializable
{
    public function __construct(
        public readonly ?string $ref = null,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly ?OperationObject $get = null,
        public readonly ?OperationObject $put = null,
        public readonly ?OperationObject $post = null,
        public readonly ?OperationObject $delete = null,
        public readonly ?OperationObject $options = null,
        public readonly ?OperationObject $head = null,
        public readonly ?OperationObject $patch = null,
        public readonly ?OperationObject $trace = null,
        public readonly ?ServerObjects $servers = null,
        public readonly ?ParameterObjects $parameters = null,
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
