<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

final class OpenAPIGeneratorOptions
{
    private function __construct(
        public readonly null|ServerObjects $servers,
        public readonly null|OpenApiVersion $openApiVersion,
        public readonly null|string $apiTitle,
        public readonly null|ApiVersion $apiVersion,
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
