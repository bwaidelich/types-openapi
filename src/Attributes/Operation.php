<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Attributes;

use Attribute;
use Wwwision\TypesOpenAPI\Types\HttpMethod;
use Wwwision\TypesOpenAPI\Types\RelativePath;
use Wwwision\TypesOpenAPI\Types\SecurityRequirementObject;

use function Wwwision\Types\instantiate;

#[Attribute(Attribute::TARGET_METHOD)]
final class Operation
{
    public readonly RelativePath $path;
    public readonly HttpMethod $method;
    public readonly SecurityRequirementObject|null $security;

    /**
     * @param RelativePath|string $path
     * @param HttpMethod|string $method
     * @param array<string>|array<string, array<string>>|string|null $security
     */
    public function __construct(
        RelativePath|string $path,
        HttpMethod|string $method,
        SecurityRequirementObject|array|string|null $security = null,
    ) {
        $this->path = is_string($path) ? RelativePath::fromString($path) : $path;
        $this->method = is_string($method) ? instantiate(HttpMethod::class, $method) : $method;
        $this->security = SecurityRequirementObject::parse($security);
    }
}
