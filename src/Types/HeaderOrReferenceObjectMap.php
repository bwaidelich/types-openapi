<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @implements IteratorAggregate<HeaderObject|ReferenceObject>
 */
final class HeaderOrReferenceObjectMap implements IteratorAggregate
{
    /**
     * @var array<string, HeaderObject|ReferenceObject>
     */
    private array $items;

    public function __construct(HeaderObject|ReferenceObject ...$items)
    {
        Assert::isMap($items);
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}
