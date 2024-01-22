<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#info-object
 */
final class InfoObject implements JsonSerializable
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly ?string $termsOfService = null,
        public readonly ?ContactObject $contact = null,
        public readonly ?LicenseObject $license = null,
        public readonly ?string $version = null,
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
