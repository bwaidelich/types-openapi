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
        public readonly string|null $summary = null,
        public readonly string|null $description = null,
        public readonly string|null $termsOfService = null,
        public readonly ContactObject|null $contact = null,
        public readonly LicenseObject|null $license = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
