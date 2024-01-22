<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Attributes;

use Attribute;
use Wwwision\TypesOpenAPI\Types\HttpMethod;
use Wwwision\TypesOpenAPI\Types\RelativePath;
use function Wwwision\Types\instantiate;

#[Attribute(Attribute::TARGET_METHOD)]
final class Operation
{
    public readonly RelativePath $path;
    public readonly HttpMethod $method;

    public function __construct(
        RelativePath|string $path,
        HttpMethod|string $method,
        public readonly string|null $description = null,
    ) {
        $this->path = is_string($path) ? RelativePath::fromString($path) : $path;
        $this->method = is_string($method) ? instantiate(HttpMethod::class, $method) : $method;
    }
}
