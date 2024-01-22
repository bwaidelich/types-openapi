<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements IteratorAggregate<ServerObject>
 */
final class ServerObjects implements IteratorAggregate, JsonSerializable
{
    /**
     * @var array<ServerObject>
     */
    private array $items;

    public function __construct(ServerObject ...$items)
    {
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return ServerObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
