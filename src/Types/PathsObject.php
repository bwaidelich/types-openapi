<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use IteratorAggregate;
use JsonSerializable;
use Traversable;
use Wwwision\TypesOpenApi\Exception\AmbiguousPathException;

/**
 * @see https://swagger.io/specification/#paths-object
 *
 * @implements IteratorAggregate<PathObject>
 */
final class PathsObject implements IteratorAggregate, JsonSerializable
{
    /**
     * @var array<int, array{path: RelativePath, object: PathObject}>
     */
    private readonly array $items;

    /**
     * @param array<int, array{path: RelativePath, object: PathObject}> $items
     */
    private function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function create(): self
    {
        return new self([]);
    }

    public function with(RelativePath $path, PathObject $object): self
    {
        $items = $this->items;
        $newItem = ['path' => $path, 'object' => $object];
        foreach ($this->items as $index => $item) {
            // "Templated paths with the same hierarchy but different templated names MUST NOT exist as they are identical." (@see https://swagger.io/specification/#paths-object)
            if ($path->equalsStructurally($item['path'])) {
                throw new AmbiguousPathException(sprintf('Path "%s" is ambiguous', $path->value), 1711991566);
            }
            // "When matching URLs, concrete (non-templated) paths would be matched before their templated counterparts" (@see https://swagger.io/specification/#paths-object)
            if (!$path->isTemplated() && $item['path']->isTemplated() && $item['path']->matches($path->value)) {
                array_splice($items, $index, 0, [$newItem]);
                return new self($items);
            }
        }
        $items[] = $newItem;
        return new self($items);
    }

    /**
     * @param array<mixed>|null $matches
     */
    public function match(string $path, array|null &$matches = null): null|PathObject
    {
        foreach ($this->items as $item) {
            if ($item['path']->matches($path, $matches)) {
                return $item['object'];
            }
        }
        return null;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function getIterator(): Traversable
    {
        foreach ($this->items as $item) {
            yield $item['path']->value => $item['object'];
        }
    }

    /**
     * @return PathObject[]
     */
    public function jsonSerialize(): array
    {
        return iterator_to_array($this);
    }
}
