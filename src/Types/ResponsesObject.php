<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

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
     * @param array<string|int, ResponseObject> $items
     */
    private function __construct(
        private readonly array $items,
    ) {}

    public static function create(): self
    {
        return new self([]);
    }

    public function with(HttpStatusCode $statusCode, ResponseObject $object): self
    {
        $merged = $this->items;
        $merged[(string) $statusCode->value] = $object;
        ksort($merged);
        return new self($merged);
    }

    public function withDefault(ResponseObject $object): self
    {
        $merged = $this->items;
        $merged['default'] = $object;
        return new self($merged);
    }

    public function hasResponseForStatusCode(int $statusCode): bool
    {
        return array_key_exists((string) $statusCode, $this->items);
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    /**
     * @return ResponseObject[]
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
