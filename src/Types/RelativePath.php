<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;

/**
 * @see https://swagger.io/specification/#openapi-object
 */
final class RelativePath implements JsonSerializable
{
    private string $regex;

    private function __construct(
        public readonly string $value,
    ) {
        // TODO validate
        $this->regex = '/^' . preg_replace('/\{([^\/]+)}/', '(?<$1>([^\/]+))', str_replace('/', '\/', $this->value)) . '$/i';
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function containsPlaceholder(string $placeholder): bool
    {
        return str_contains($this->value, '{' . $placeholder . '}');
    }

    public function isTemplated(): bool
    {
        return preg_match('/\{([^\/]+)}/', $this->value) === 1;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function equalsStructurally(self $other): bool
    {
        return preg_replace('/\{([^\/]+)}/', '*', $this->value) === preg_replace('/\{([^\/]+)}/', '*', $other->value);
    }

    /**
     * @param string $value
     * @param array<mixed>|null $matches
     * @return bool
     */
    public function matches(string $value, array|null &$matches = null): bool
    {
        return preg_match($this->regex, $value, $matches) === 1;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
