<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use InvalidArgumentException;
use JsonSerializable;
use Wwwision\TypesJSONSchema\Types as JSON;

/**
 * @see https://swagger.io/specification/#parameter-object
 */
final class ParameterObject implements JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly ParameterLocation $in,
        public readonly ?string $description = null,
        public readonly ?bool $required = null,
        public readonly ?bool $deprecated = null,
        public readonly ?ParameterStyle $style = null,
        public readonly ?bool $explode = null,
        public readonly ?bool $allowReserved = null,
        public readonly ?JSON\Schema $schema = null,
        // TODO add examples
        // TODO add content
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
        return array_filter(get_object_vars($this), static fn ($i) => $i !== null);
    }
}
