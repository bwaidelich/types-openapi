<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;
use Psr\Http\Message\UriInterface;
use Webmozart\Assert\Assert;

/**
 * @see https://swagger.io/specification/#server-object
 */
final class ServerObject implements JsonSerializable
{
    public function __construct(
        public readonly string $url,
        public readonly null|string $description = null,
        public readonly null|ServerVariableObjects $variables = null,
    ) {}

    /**
     * @param array<string, string> $variables
     */
    public function matches(UriInterface $uri, array $variables = []): string|false
    {
        $url = preg_replace_callback('({(\w+)})', fn(array $matches) => $variables[$matches[1]] ?? $this->variables?->getDefaultValue($matches[1]) ?? '', $this->url);
        Assert::string($url);
        $urlComponents = parse_url($url);
        if (isset($urlComponents['scheme']) && $urlComponents['scheme'] !== $uri->getScheme()) {
            return false;
        }
        if (isset($urlComponents['host']) && $urlComponents['host'] !== $uri->getHost()) {
            return false;
        }
        if (isset($urlComponents['port']) && $urlComponents['port'] !== $uri->getPort()) {
            return false;
        }
        $path = $urlComponents['path'] ?? '/';
        if (isset($urlComponents['path'])) {
            if (!str_starts_with($uri->getPath(), $urlComponents['path'])) {
                return false;
            }
            $path = substr($uri->getPath(), strlen($path));
        }
        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
