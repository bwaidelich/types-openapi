<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @see map of {@see MediaTypeObject}
 *
 * @implements IteratorAggregate<MediaTypeObject>
 */
final class MediaTypeObjectMap implements IteratorAggregate, JsonSerializable
{
    /**
     * @param array<string, MediaTypeObject> $items
     */
    private function __construct(
        private readonly array $items,
    ) {
    }

    public static function create(): self
    {
        return new self([]);
    }

    public function with(MediaTypeRange $mediaTypeRange, MediaTypeObject $object): self
    {
        $merged = $this->items;
        $merged[$mediaTypeRange->value] = $object;
        return new self($merged);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return MediaTypeObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
