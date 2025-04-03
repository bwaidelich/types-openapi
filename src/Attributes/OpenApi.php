<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Attributes;

use Attribute;
use Wwwision\TypesOpenAPI\Types\ApiVersion;
use Wwwision\TypesOpenAPI\Types\OpenApiVersion;
use Wwwision\TypesOpenAPI\Types\SecurityRequirementObject;
use Wwwision\TypesOpenAPI\Types\SecuritySchemeObject;
use Wwwision\TypesOpenAPI\Types\SecuritySchemeOrReferenceObjectMap;

#[Attribute(Attribute::TARGET_CLASS)]
final class OpenApi
{
    public readonly ApiVersion|null $apiVersion;
    public readonly OpenApiVersion|null $openApiVersion;
    public readonly SecuritySchemeOrReferenceObjectMap|null $securitySchemes;
    public readonly SecurityRequirementObject|null $security;

    /**
     * @param SecuritySchemeOrReferenceObjectMap|array<string, SecuritySchemeObject|array<mixed>>|null $securitySchemes
     * @param SecurityRequirementObject|array<string>|array<string, array<string>>|string|null $security
     */
    public function __construct(
        public readonly string|null $apiTitle = null,
        ApiVersion|string|null $apiVersion = null,
        OpenApiVersion|string|null $openApiVersion = null,
        SecuritySchemeOrReferenceObjectMap|array|null $securitySchemes = null,
        SecurityRequirementObject|array|string|null $security = null,
    ) {
        if (is_string($apiVersion)) {
            $apiVersion = ApiVersion::fromString($apiVersion);
        }
        $this->apiVersion = $apiVersion;
        if (is_string($openApiVersion)) {
            $openApiVersion = OpenApiVersion::fromString($openApiVersion);
        }
        $this->openApiVersion = $openApiVersion;
        if (is_array($securitySchemes)) {
            $securitySchemes = SecuritySchemeOrReferenceObjectMap::fromArray($securitySchemes);
        }
        $this->securitySchemes = $securitySchemes;
        $this->security = SecurityRequirementObject::parse($security);
    }
}
