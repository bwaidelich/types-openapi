<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use Webmozart\Assert\Assert;
use Wwwision\TypesJSONSchema\Types as JSON;

/**
 * @implements IteratorAggregate<JSON\Schema>
 */
final class SchemaObjectMap implements JsonSerializable, IteratorAggregate
{
    /**
     * @var array<string,JSON\Schema>
     */
    private array $items;

    public function __construct(JSON\Schema ...$items)
    {
        Assert::isMap($items);
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return array<string, JSON\Schema>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
