<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @implements IteratorAggregate<LinkObject|ReferenceObject>
 */
final class LinkOrReferenceObjectMap implements IteratorAggregate
{
    /**
     * @var array<string, LinkObject|ReferenceObject>
     */
    private array $items;

    public function __construct(LinkObject|ReferenceObject ...$items)
    {
        Assert::isMap($items);
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}
