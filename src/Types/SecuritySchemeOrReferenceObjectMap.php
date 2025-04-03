<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use IteratorAggregate;
use JsonSerializable;
use Traversable;
use Webmozart\Assert\Assert;

use function Wwwision\Types\instantiate;

/**
 * @implements IteratorAggregate<SecuritySchemeObject|ReferenceObject>
 */
final class SecuritySchemeOrReferenceObjectMap implements IteratorAggregate, JsonSerializable
{
    /**
     * @var array<string,SecuritySchemeObject|ReferenceObject>
     */
    private array $items;

    public function __construct(SecuritySchemeObject|ReferenceObject ...$items)
    {
        Assert::isMap($items);
        $this->items = $items;
    }

    /**
     * @param array<string, SecuritySchemeObject|array<mixed>> $items
     */
    public static function fromArray(array $items): self
    {
        $processedItems = [];
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $item = instantiate(SecuritySchemeObject::class, $item);
            }
            $processedItems[$key] = $item;
        }
        return new self(...$processedItems);
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    /**
     * @return array<string,SecuritySchemeObject|ReferenceObject>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
