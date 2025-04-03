<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use InvalidArgumentException;
use JsonSerializable;
use Wwwision\Types\Schema\Schema;
use Wwwision\TypesJSONSchema\Types as JSON;

/**
 * @see https://swagger.io/specification/#parameter-object
 */
final class ParameterObject implements JsonSerializable
{
    /**
     * @param mixed|null $default This parameter is not documented in the specification, but it seems to be supported and can be a good fit when referring to a component Schema with a different default value
     * @param array<string, mixed> $meta key/value for custom metadata. This is not part of the OpenAPI specification and won't appear in the JSON serialized format
     */
    public function __construct(
        public readonly string $name,
        public readonly ParameterLocation $in,
        public readonly null|string $description = null,
        public readonly null|bool $required = null,
        public readonly null|bool $deprecated = null,
        public readonly null|ParameterStyle $style = null,
        public readonly null|bool $explode = null,
        public readonly null|bool $allowReserved = null,
        public readonly null|JSON\Schema $schema = null,
        // TODO add examples
        // TODO add content
        public readonly mixed $default = null,
        public readonly array $meta = [],
    ) {
        if ($this->required === false && $this->in === ParameterLocation::path) {
            throw new InvalidArgumentException('Parameter of location "path" must be required', 1704986928);
        }
    }

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
