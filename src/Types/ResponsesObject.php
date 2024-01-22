<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @see https://swagger.io/specification/#responses-object
 *
 * @implements IteratorAggregate<ResponseObject>
 */
final class ResponsesObject implements IteratorAggregate, JsonSerializable
{
    /**
     * @param array<string, ResponseObject> $items
     */
    private function __construct(
        private readonly array $items,
    ) {
    }

    public static function create(): self
    {
        return new self([]);
    }

    public function with(HttpStatusCode $statusCode, ResponseObject $object): self
    {
        $merged = $this->items;
        $merged[(string)$statusCode->value] = $object;
        return new self($merged);
    }

    public function withDefault(ResponseObject $object): self
    {
        $merged = $this->items;
        $merged['default'] = $object;
        return new self($merged);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return ResponseObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
