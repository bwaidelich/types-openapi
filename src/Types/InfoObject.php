<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#info-object
 */
final class InfoObject implements JsonSerializable
{
    public function __construct(
        public readonly string $title,
        public readonly ApiVersion $version,
        public readonly null|string $summary = null,
        public readonly null|string $description = null,
        public readonly null|string $termsOfService = null,
        public readonly null|ContactObject $contact = null,
        public readonly null|LicenseObject $license = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
