<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Types;

use JsonSerializable;
use Webmozart\Assert\Assert;

/**
 * @see https://swagger.io/specification/#security-requirement-object
 */
final class SecurityRequirementObject implements JsonSerializable
{
    public readonly bool $anonymousAccessAllowed;

    /**
     * @param list<array<string, array<string>>> $namesAndScopes each name MUST correspond to a security scheme which is declared in the Security Schemes under the Components Object
     */
    public function __construct(
        public readonly array $namesAndScopes,
    ) {
        $anonymousAccessAllowed = false;
        Assert::notEmpty($this->namesAndScopes);
        foreach ($this->namesAndScopes as $nameAndScope) {
            if ($nameAndScope === []) {
                $anonymousAccessAllowed = true;
                continue;
            }
            Assert::isMap($nameAndScope);
            Assert::allIsArray($nameAndScope); // @phpstan-ignore-line
        }
        $this->anonymousAccessAllowed = $anonymousAccessAllowed;
    }

    /**
     * @param SecurityRequirementObject|array<mixed>|string|null $value
     */
    public static function parse(SecurityRequirementObject|array|string|null $value): self|null
    {
        if ($value instanceof SecurityRequirementObject) {
            return $value;
        }
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            $value = [$value];
        }
        $result = [];
        foreach ($value as $valueKey => $item) {
            if (is_int($valueKey)) {
                if ($item === []) {
                    $result[] = [];
                } else {
                    Assert::string($item);
                    $result[] = [$item => []];
                }
            } else {
                Assert::string($valueKey);
                Assert::isList($item);
                Assert::allString($item);
                $result[] = [$valueKey => $item];
            }
        }
        return new self($result);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function jsonSerialize(): array
    {
        return $this->namesAndScopes;
    }
}
