<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi;

use Wwwision\TypesOpenApi\Types\ApiVersion;
use Wwwision\TypesOpenApi\Types\OpenApiVersion;
use Wwwision\TypesOpenApi\Types\ServerObjects;

final class OpenApiGeneratorOptions
{
    private function __construct(
        public readonly ServerObjects|null $servers,
        public readonly OpenApiVersion|null $openApiVersion,
        public readonly string|null $apiTitle,
        public readonly ApiVersion|null $apiVersion,
    ) {}

    public static function create(
        ServerObjects|null $servers = null,
        OpenApiVersion|string|null $openApiVersion = null,
        string|null $apiTitle = null,
        ApiVersion|string|null $apiVersion = null,
    ): self {
        return new self(
            $servers,
            is_string($openApiVersion) ? OpenApiVersion::fromString($openApiVersion) : $openApiVersion,
            $apiTitle,
            is_string($apiVersion) ? ApiVersion::fromString($apiVersion) : $apiVersion,
        );
    }
}
