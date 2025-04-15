<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Attributes;

use Attribute;
use Wwwision\TypesOpenApi\Types\ApiVersion;
use Wwwision\TypesOpenApi\Types\ContactObject;
use Wwwision\TypesOpenApi\Types\LicenseObject;
use Wwwision\TypesOpenApi\Types\OpenApiVersion;
use Wwwision\TypesOpenApi\Types\SecurityRequirementObject;
use Wwwision\TypesOpenApi\Types\SecuritySchemeObject;
use Wwwision\TypesOpenApi\Types\SecuritySchemeOrReferenceObjectMap;

#[Attribute(Attribute::TARGET_CLASS)]
final class OpenApi
{
    public readonly ApiVersion|null $apiVersion;
    public readonly OpenApiVersion|null $openApiVersion;
    public readonly ContactObject|null $contact;
    public readonly LicenseObject|null $license;
    public readonly SecuritySchemeOrReferenceObjectMap|null $securitySchemes;
    public readonly SecurityRequirementObject|null $security;

    /**
     * @param ContactObject|array{name?: string, url?: string, email?: string}|null $contact
     * @param LicenseObject|array{name: string, identifier?: string, url?: string}|null $license
     * @param SecuritySchemeOrReferenceObjectMap|array<string, SecuritySchemeObject|array<mixed>>|null $securitySchemes
     * @param SecurityRequirementObject|array<string>|array<string, array<string>>|string|null $security
     */
    public function __construct(
        public readonly string|null $apiTitle = null,
        ApiVersion|string|null $apiVersion = null,
        public string|null $summary = null,
        public string|null $termsOfService = null,
        ContactObject|array|null $contact = null,
        LicenseObject|array|null $license = null,
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
        if (is_array($contact)) {
            $contact = ContactObject::fromArray($contact);
        }
        $this->contact = $contact;
        if (is_array($license)) {
            $license = LicenseObject::fromArray($license);
        }
        $this->license = $license;
        $this->openApiVersion = $openApiVersion;
        if (is_array($securitySchemes)) {
            $securitySchemes = SecuritySchemeOrReferenceObjectMap::fromArray($securitySchemes);
        }
        $this->securitySchemes = $securitySchemes;
        $this->security = SecurityRequirementObject::parse($security);
    }
}
