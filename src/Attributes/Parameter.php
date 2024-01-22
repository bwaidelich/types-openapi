<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Attributes;

use Attribute;
use Wwwision\TypesOpenAPI\Types\ParameterLocation;
use function Wwwision\Types\instantiate;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class Parameter
{
    public readonly ParameterLocation $location;

    public function __construct(
        ParameterLocation|string $location,
        public readonly bool $deprecated = false,
    ) {
        $this->location = is_string($location) ? instantiate(ParameterLocation::class, $location) : $location;
    }
}
