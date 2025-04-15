<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Attributes;

use Attribute;
use Wwwision\TypesOpenApi\Types\HttpMethod;
use Wwwision\TypesOpenApi\Types\RelativePath;
use Wwwision\TypesOpenApi\Types\SecurityRequirementObject;

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
        public string|null $summary = null,
        SecurityRequirementObject|array|string|null $security = null,
    ) {
        $this->path = is_string($path) ? RelativePath::fromString($path) : $path;
        $this->method = is_string($method) ? instantiate(HttpMethod::class, $method) : $method;
        $this->security = SecurityRequirementObject::parse($security);
    }
}
