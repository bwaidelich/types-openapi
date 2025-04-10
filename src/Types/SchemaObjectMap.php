<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use IteratorAggregate;
use JsonSerializable;
use Traversable;
use Webmozart\Assert\Assert;
use Wwwision\JsonSchema as Json;

/**
 * @implements IteratorAggregate<Json\Schema>
 */
final class SchemaObjectMap implements JsonSerializable, IteratorAggregate
{
    /**
     * @var array<string,Json\Schema>
     */
    private array $items;

    public function __construct(Json\Schema ...$items)
    {
        Assert::isMap($items);
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    /**
     * @return array<string, Json\Schema>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
