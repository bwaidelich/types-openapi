<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Attributes;

use Attribute;
use Wwwision\TypesOpenApi\Types\ParameterLocation;

use function Wwwision\Types\instantiate;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class Parameter
{
    public readonly ParameterLocation|null $in;

    public function __construct(
        ParameterLocation|string|null $in = null,
        public readonly string|null $name = null,
    ) {
        $this->in = is_string($in) ? instantiate(ParameterLocation::class, $in) : $in;
    }
}
