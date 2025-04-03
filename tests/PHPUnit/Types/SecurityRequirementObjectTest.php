<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit\Types;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesOpenAPI\Types\SecurityRequirementObject;

#[CoversClass(SecurityRequirementObject::class)]
final class SecurityRequirementObjectTest extends TestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function parse_provider(): iterable
    {
        yield 'single entry with empty scopes' => ['value' => ['foo' => []], 'expectedResult' => [['foo' => []]], 'expectedAnonymousAccessAllowed' => false];
        yield 'multiple entries with scopes' => ['value' => ['foo' => ['bar'], 'bar' => ['baz', 'foos']], 'expectedResult' => [['foo' => ['bar']], ['bar' => ['baz', 'foos']]], 'expectedAnonymousAccessAllowed' => false];
        yield 'only optional' => ['value' => [[]], 'expectedResult' => [[]], 'expectedAnonymousAccessAllowed' => true];
        yield 'optional and named with scopes' => ['value' => [[], 'foo' => ['bar']], 'expectedResult' => [[], ['foo' => ['bar']]], 'expectedAnonymousAccessAllowed' => true];

        yield 'single string' => ['value' => 'foo', 'expectedResult' => [['foo' => []]], 'expectedAnonymousAccessAllowed' => false];
        yield 'single strings' => ['value' => ['foo', 'bar'], 'expectedResult' => [['foo' => []], ['bar' => []]], 'expectedAnonymousAccessAllowed' => false];
        yield 'single strings and arrays mixed' => ['value' => [[], 'foo', 'bar' => ['baz']], 'expectedResult' => [[], ['foo' => []], ['bar' => ['baz']]], 'expectedAnonymousAccessAllowed' => true];
    }

    /**
     * @param SecurityRequirementObject|array<mixed>|string $value
     * @param array<mixed>|null $expectedResult
     */
    #[DataProvider('parse_provider')]
    public function test_parse(SecurityRequirementObject|array|string $value, array|null $expectedResult, bool $expectedAnonymousAccessAllowed): void
    {
        $instance = SecurityRequirementObject::parse($value);
        self::assertNotNull($instance);
        self::assertSame($expectedResult, $instance->namesAndScopes);
        if ($expectedAnonymousAccessAllowed) {
            self::assertTrue($instance->anonymousAccessAllowed);
        } else {
            self::assertFalse($instance->anonymousAccessAllowed);
        }
    }

    /**
     * @param SecurityRequirementObject|array<mixed>|string $value
     * @param array<mixed>|null $expectedResult
     */
    #[DataProvider('parse_provider')]
    public function test_parse_reconstitute(SecurityRequirementObject|array|string $value, array|null $expectedResult, bool $expectedAnonymousAccessAllowed): void
    {
        $instance = SecurityRequirementObject::parse($value);
        self::assertNotNull($instance);
        $reconsistuted = new SecurityRequirementObject($instance->jsonSerialize()); // @phpstan-ignore-line
        self::assertSame($reconsistuted->namesAndScopes, $instance->namesAndScopes);
    }

    public function test_parse_with_null_returns_null(): void
    {
        self::assertNull(SecurityRequirementObject::parse(null));
    }

    /**
     * @return iterable<mixed>
     */
    public static function parse_invalid_provider(): iterable
    {
        yield 'empty array' => ['value' => []];
        yield 'array of string' => ['value' => ['foo' => 'not-an-array']];
        yield 'array of int' => ['value' => ['foo' => 123]];
    }

    /**
     * @param SecurityRequirementObject|array<mixed>|string $value
     */
    #[DataProvider('parse_invalid_provider')]
    public function test_parse_invalid(SecurityRequirementObject|array|string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        SecurityRequirementObject::parse($value);
    }

}
