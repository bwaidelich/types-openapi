<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#header-object
 */
final class HeaderObject implements JsonSerializable
{
    public function __construct(
        public readonly ?string $description = null,
        public readonly ?bool $required = null,
        public readonly ?bool $deprecated = null,
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
