<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use InvalidArgumentException;
use JsonSerializable;

/**
 * A Media Type or Media Type Range (@see https://datatracker.ietf.org/doc/html/rfc7231#appendix--d)
 */
final class MediaTypeRange implements JsonSerializable
{
    private const PATTERN = '/^(?P<type>(?:[\.!#%&\'\`\^~\$\*\+\-\|\w]+))\/(?P<subtype>(?:[\.!#%&\'\`\^~\$\*\+\-\|\w]+))(?P<parameters>.*)$/i';
    public readonly string $type;
    public readonly string $subtype;

    private function __construct(
        public readonly string $value,
    ) {
        if (preg_match(self::PATTERN, $this->value, $matches) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid Media Type Range "%s"', $this->value), 1742410742);
        }
        $this->type = $matches['type'];
        $this->subtype = $matches['subtype'];
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function matches(MediaTypeRange $other): bool
    {
        $typeMatches = ($this->type === '*' || $this->type === $other->type);
        $subtypeMatches = ($this->subtype === '*' || $this->subtype === $other->subtype);

        return ($typeMatches && $subtypeMatches);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
