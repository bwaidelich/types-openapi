<?php
declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit\Types;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesOpenAPI\Types\CustomResolver;
use Wwwision\TypesOpenAPI\Types\CustomResolvers;

#[CoversClass(CustomResolvers::class)]
#[CoversClass(CustomResolver::class)]
final class CustomResolversTest extends TestCase
{

    public function test_with_throws_exception_if_resolver_is_registered_twice(): void
    {
        $customResolvers = CustomResolvers::create()
            ->with(new CustomResolver('Foo', 'bar', fn () => true))
            ->with(new CustomResolver('Bar', 'bar', fn () => true));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A resolver for field "bar" on type "Foo" is already registered');
        $customResolvers->with(new CustomResolver('Foo', 'bar', fn () => true));
    }

    public function test_resolvers_can_be_iterated(): void
    {
        $r1 = new CustomResolver('Foo', 'bar', fn () => true);
        $r2 = new CustomResolver('Bar', 'baz', fn () => true);
        $r3 = new CustomResolver('Foo', 'baz', fn () => true);
        $customResolvers = CustomResolvers::create($r1, $r2, $r3);

        self::assertSame([$r1, $r3, $r2], iterator_to_array($customResolvers));
    }

    public function test_getAllForType_returns_empty_array_if_no_corresponding_resolver_is_registered(): void
    {
        $r1 = new CustomResolver('Foo', 'bar', fn () => true);
        $r2 = new CustomResolver('Bar', 'baz', fn () => true);
        $r3 = new CustomResolver('Foo', 'baz', fn () => true);
        $customResolvers = CustomResolvers::create($r1, $r2, $r3);
        self::assertSame([], iterator_to_array($customResolvers->getAllForType('bar')));
    }

    public function test_getAllForType_returns_resolvers_for_type(): void
    {
        $r1 = new CustomResolver('Foo', 'bar', fn () => true);
        $r2 = new CustomResolver('Bar', 'baz', fn () => true);
        $r3 = new CustomResolver('Foo', 'baz', fn () => true);
        $customResolvers = CustomResolvers::create($r1, $r2, $r3);
        self::assertSame([$r1, $r3], iterator_to_array($customResolvers->getAllForType('Foo')));
    }

    public function test_get_returns_null_if_no_corresponding_resolver_is_registered(): void
    {
        $r1 = new CustomResolver('Foo', 'bar', fn () => true);
        $r2 = new CustomResolver('Bar', 'baz', fn () => true);
        $r3 = new CustomResolver('Foo', 'baz', fn () => true);
        $customResolvers = CustomResolvers::create($r1, $r2, $r3);
        self::assertNull($customResolvers->get('Bar', 'bar'));
    }

    public function test_get_returns_corresponding_resolver(): void
    {
        $r1 = new CustomResolver('Foo', 'bar', fn () => true);
        $r2 = new CustomResolver('Bar', 'baz', fn () => true);
        $r3 = new CustomResolver('Foo', 'baz', fn () => true);
        $customResolvers = CustomResolvers::create($r1, $r2, $r3);
        self::assertSame($r2, $customResolvers->get('Bar', 'baz'));
    }

}
