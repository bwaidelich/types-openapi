<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesOpenAPI\Exception\AmbiguousPathException;
use Wwwision\TypesOpenAPI\Types\PathObject;
use Wwwision\TypesOpenAPI\Types\PathsObject;
use Wwwision\TypesOpenAPI\Types\RelativePath;

#[CoversClass(PathsObject::class)]
final class PathsObjectTest extends TestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function order_provider(): iterable
    {
        yield 'keeps order by default' => ['paths' => ['/first', '/second'], 'expectedResult' => ['/first', '/second']];
        // "When matching URLs, concrete (non-templated) paths would be matched before their templated counterparts" (@see https://swagger.io/specification/#paths-object)
        yield 'reorders concrete paths before templated if overlapping' => ['paths' => ['/pets/{pet}', '/pets/mine'], 'expectedResult' => ['/pets/mine', '/pets/{pet}']];
        yield ['paths' => ['/pets/{pet}', '/pets/mine', '/books/fiction', '/movies/{movieId}', '/books/{bookId}', '/movies/horror'], 'expectedResult' => ['/pets/mine', '/pets/{pet}', '/books/fiction', '/movies/horror', '/movies/{movieId}', '/books/{bookId}']];
    }

    /**
     * @param array<string> $paths
     * @param array<mixed> $expectedResult
     */
    #[DataProvider('order_provider')]
    public function test_order(array $paths, array $expectedResult): void
    {
        $pathsObject = PathsObject::create();
        foreach ($paths as $path) {
            $pathsObject = $pathsObject->with(RelativePath::fromString($path), new PathObject());
        }
        self::assertSame($expectedResult, array_keys(iterator_to_array($pathsObject)));
    }

    public function test_disallows_duplicate_concrete_paths(): void
    {
        $paths = PathsObject::create()
            ->with(RelativePath::fromString('/pets/foo'), new PathObject());

        $this->expectException(AmbiguousPathException::class);
        $paths = $paths->with(RelativePath::fromString('/pets/foo'), new PathObject());
    }

    public function test_disallows_duplicate_templated_paths(): void
    {
        $paths = PathsObject::create()
            ->with(RelativePath::fromString('/pets/{foo}'), new PathObject());

        $this->expectException(AmbiguousPathException::class);
        $paths = $paths->with(RelativePath::fromString('/pets/{foo}'), new PathObject());
    }

    /**
     * "Templated paths with the same hierarchy but different templated names MUST NOT exist as they are identical."
     * @see https://swagger.io/specification/#paths-object
     */
    public function test_disallows_paths_with_the_same_hierarchy_but_different_templated_names(): void
    {
        $paths = PathsObject::create()
            ->with(RelativePath::fromString('/pets/{pet}'), new PathObject());

        $this->expectException(AmbiguousPathException::class);
        $paths = $paths->with(RelativePath::fromString('/pets/{petId}'), new PathObject());
    }

    /**
     * "In case of ambiguous matching, it's up to the tooling to decide which one to use."
     * @see https://swagger.io/specification/#paths-object
     */
    public function test_allows_paths_with_the_same_resolution(): void
    {
        $mockPathObject1 = new PathObject();
        $mockPathObject2 = new PathObject();
        $paths = PathsObject::create()
            ->with(RelativePath::fromString('/{entity}/me'), $mockPathObject1)
            ->with(RelativePath::fromString('/books/{id}'), $mockPathObject2);
        self::assertEqualsCanonicalizing(['/{entity}/me' => $mockPathObject1, '/books/{id}' => $mockPathObject2], iterator_to_array($paths));
    }

}
