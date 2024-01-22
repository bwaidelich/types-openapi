<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @see https://swagger.io/specification/#paths-object
 *
 * @implements IteratorAggregate<PathObject>
 */
final class PathsObject implements IteratorAggregate, JsonSerializable
{
    /**
     * @param array<string, PathObject> $items
     */
    private function __construct(
        private readonly array $items,
    ) {
    }

    public static function create(): self
    {
        return new self([]);
    }

    public function with(RelativePath $path, PathObject $object): self
    {
        $merged = $this->items;
        $merged[$path->value] = $object;
        return new self($merged);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return PathObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
