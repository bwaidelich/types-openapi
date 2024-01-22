<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use ArrayIterator;
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
        return new ArrayIterator($this->items);
    }

    /**
     * @return ParameterObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
