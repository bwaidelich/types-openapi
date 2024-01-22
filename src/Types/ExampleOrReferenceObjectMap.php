<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @implements IteratorAggregate<ExampleObject|ReferenceObject>
 */
final class ExampleOrReferenceObjectMap implements IteratorAggregate
{
    /**
     * @var array<string,ExampleObject|ReferenceObject>
     */
    private array $items;

    public function __construct(ExampleObject|ReferenceObject ...$items)
    {
        Assert::isMap($items);
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
