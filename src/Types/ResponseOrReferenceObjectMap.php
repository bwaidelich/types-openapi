<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @implements IteratorAggregate<ResponseObject|ReferenceObject>
 */
final class ResponseOrReferenceObjectMap implements IteratorAggregate
{
    /**
     * @var array<string,ResponseObject|ReferenceObject>
     */
    private array $items;

    public function __construct(ResponseObject|ReferenceObject ...$items)
    {
        Assert::isMap($items);
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}
