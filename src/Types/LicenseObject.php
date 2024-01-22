<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use InvalidArgumentException;
use JsonSerializable;

/**
 * @see https://swagger.io/specification/#license-object
 */
final class LicenseObject implements JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $identifier = null,
        public readonly ?string $url = null,
    ) {
        if ($this->identifier !== null && $this->url !== null) {
            throw new InvalidArgumentException('Fields "identifier" and "url" are mutually exclusive', 1704987583);
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
