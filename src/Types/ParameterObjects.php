<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements IteratorAggregate<ParameterObject>
 */
final class ParameterObjects implements IteratorAggregate, JsonSerializable
{
    /**
     * @var array<ParameterObject>
     */
    private array $items;

    public function __construct(ParameterObject ...$items)
    {
        // TODO prevent duplicates
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    /**
     * @return ParameterObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
