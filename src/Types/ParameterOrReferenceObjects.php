<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements IteratorAggregate<ParameterObject|ReferenceObject>
 */
final class ParameterOrReferenceObjects implements IteratorAggregate, JsonSerializable
{
    /**
     * @var array<ParameterObject|ReferenceObject>
     */
    private array $items;

    public function __construct(ParameterObject|ReferenceObject ...$items)
    {
        // TODO prevent duplicates
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return ParameterObject[]|ReferenceObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
