<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @implements IteratorAggregate<ExampleObject|ReferenceObject>
 */
final class EncodingObjectMap implements IteratorAggregate
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
        yield from $this->items;
    }
}
