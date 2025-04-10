<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

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
        yield from $this->items;
    }

    /**
     * @return ServerObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
