<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;
use Webmozart\Assert\Assert;

/**
 * @see https://swagger.io/specification/#http-codes
 */
final class HttpStatusCode implements JsonSerializable
{
    private function __construct(
        public readonly int $value,
    ) {
        Assert::greaterThanEq($value, 100);
        Assert::lessThanEq($value, 599);
    }

    public static function fromInteger(int $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
