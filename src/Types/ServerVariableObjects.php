<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use IteratorAggregate;
use JsonSerializable;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @implements IteratorAggregate<ServerVariableObject>
 */
final class ServerVariableObjects implements IteratorAggregate, JsonSerializable
{
    /**
     * @var array<string, ServerVariableObject>
     */
    private array $items;

    public function __construct(ServerVariableObject ...$items)
    {
        Assert::isMap($items);
        $this->items = $items;
    }

    public function getDefaultValue(string $name): string
    {
        return $this->items[$name]->default ?? '';
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    /**
     * @return ServerVariableObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
