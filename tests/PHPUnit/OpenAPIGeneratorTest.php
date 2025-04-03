<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Traversable;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Attributes\ListBased;
use Wwwision\TypesOpenAPI\Attributes\OpenApi;
use Wwwision\TypesOpenAPI\Attributes\Operation;
use Wwwision\TypesOpenAPI\Exception\AmbiguousPathException;
use Wwwision\TypesOpenAPI\OpenAPIGenerator;
use Wwwision\TypesOpenAPI\Types\HttpMethod;
use Wwwision\TypesOpenAPI\Types\OpenAPIGeneratorOptions;
use Wwwision\TypesOpenAPI\Types\ServerObject;
use Wwwision\TypesOpenAPI\Types\ServerObjects;

use function Wwwision\Types\instantiate;

#[CoversClass(OpenAPIGenerator::class)]
final class OpenAPIGeneratorTest extends TestCase
{
    private OpenAPIGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new OpenAPIGenerator();
    }

    public function test_generate_throws_exception_if_specified_class_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->generator->generate('NonExistingClass', OpenAPIGeneratorOptions::create());
    }

    public function test_generate(): void
    {
        $servers = [];
        foreach (['local' => 'http://localhost:8081', 'prod' => 'https://foo-bar.com/'] as $description => $url) {
            $servers[] = new ServerObject($url, $description);
        }
        $schema = $this->generator->generate(PetStoreApi::class, OpenAPIGeneratorOptions::create(servers: new ServerObjects(...$servers), apiTitle: 'Overridden'));

        $expected = <<<'JSON'
            {
                "info": {
                    "title": "Pet Store API",
                    "version": "1.0.0"
                },
                "openapi": "3.0.3",
                "servers": [
                    {
                        "description": "local",
                        "url": "http://localhost:8081"
                    },
                    {
                        "description": "prod",
                        "url": "https://foo-bar.com/"
                    }
                ],
                "paths": {
                    "/pet/findByStatus": {
                        "get": {
                            "description": "Operation description",
                            "operationId": "pets",
                            "responses": {
                                "200": {
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "$ref": "#/components/schemas/Pets"
                                            }
                                        }
                                    },
                                    "description": "Default"
                                }
                            }
                        }
                    },
                    "/pet/{id}": {
                        "get": {
                            "operationId": "petById",
                            "parameters": [
                                {
                                    "in": "path",
                                    "name": "id",
                                    "required": true,
                                    "schema": {
                                        "type": "integer"
                                    }
                                }
                            ],
                            "responses": {
                                "200": {
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "$ref": "#/components/schemas/Pet"
                                            }
                                        }
                                    },
                                    "description": "Default"
                                },
                                "400": {
                                    "description": "Bad Request"
                                }
                            }
                        }
                    }
                },
                "components": {
                    "schemas": {
                        "Pet": {
                            "additionalProperties": false,
                            "properties": {
                                "id": {
                                    "type": "integer"
                                },
                                "name": {
                                    "type": "string"
                                },
                                "tag": {
                                    "type": "string"
                                }
                            },
                            "required": [
                                "id",
                                "name"
                            ],
                            "type": "object"
                        },
                        "Pets": {
                            "items": {
                                "$ref": "#/components/schemas/Pet"
                            },
                            "maxItems": 10,
                            "minItems": 1,
                            "type": "array"
                        }
                    }
                }
            }
            JSON;

        self::assertJsonStringEqualsJsonString($expected, json_encode($schema, JSON_THROW_ON_ERROR));
    }


    /**
     * @return iterable<mixed>
     */
    public static function valid_paths_provider(): iterable
    {
        yield 'concrete and templated paths overlap' => ['className' => ApiWithConcreteAndTemplatedPathsOverlap::class, 'expectedPaths' => ['/pets/mine' => ['get'], '/pets/{pet}' => ['get']]];
        yield 'same paths but different methods' => ['className' => ApiWithTheSamePathsButDifferentMethods::class, 'expectedPaths' => ['/pets/{pet}' => ['get', 'post']]];
    }

    /**
     * @param array<mixed> $expectedPaths
     */
    #[DataProvider('valid_paths_provider')]
    public function test_valid_paths(string $className, array $expectedPaths): void
    {
        $schema = $this->generator->generate($className, OpenAPIGeneratorOptions::create());
        $actualPaths = [];
        self::assertNotNull($schema->paths);
        foreach ($schema->paths as $path => $pathObject) {
            $actualPaths[$path] = array_keys(iterator_to_array($pathObject->operationsByMethod()));
        }
        self::assertSame($expectedPaths, $actualPaths);
    }

    /**
     * @return iterable<mixed>
     */
    public static function invalid_paths_provider(): iterable
    {
        yield 'same paths and methods' => ['className' => ApiWithTheSamePathsAndMethods::class];
        yield 'same path structure and methods' => ['className' => ApiWithTheSamePathStructureAndMethods::class];
        yield 'same path structure but different methods' => ['className' => ApiWithTheSamePathStructureButDifferentMethods::class];
    }

    #[DataProvider('invalid_paths_provider')]
    public function test_invalid_paths(string $className): void
    {
        $this->expectException(AmbiguousPathException::class);
        $this->generator->generate($className, OpenAPIGeneratorOptions::create());
    }

}

final class Pet
{
    private function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly null|string $tag = null,
    ) {}
}

/**
 * @implements IteratorAggregate<Pet>
 */
#[ListBased(itemClassName: Pet::class, minCount: 1, maxCount: 10)]
final class Pets implements IteratorAggregate
{
    /**
     * @param array<Pet> $pets
     */
    private function __construct(private readonly array $pets) {}

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->pets);
    }
}

enum PetStatus
{
    case available;
    case pending;
    case sold;
}

#[OpenApi(apiTitle: 'Pet Store API', apiVersion: '1.0.0')]

final class PetStoreApi
{
    private Pets $pets;

    public function __construct()
    {
        $this->pets = instantiate(Pets::class, [
            ['id' => 1, 'name' => 'First'],
            ['id' => 2, 'name' => 'Second', 'tags' => ['some-tag']],
            ['id' => 3, 'name' => 'Third', 'tags' => ['some-other-tag']],
        ]);
    }

    #[Operation(path: '/pet/findByStatus', method: HttpMethod::GET)]
    #[Description('Operation description')]
    public function pets(): Pets
    {
        return $this->pets;
    }

    #[Operation(path: '/pet/{id}', method: HttpMethod::GET)]
    public function petById(int $id): Pet
    {
        foreach ($this->pets as $pet) {
            if ($pet->id === $id) {
                return $pet;
            }
        }
        throw new InvalidArgumentException(sprintf('Pet #%d not found', $id));
    }


}

final class ApiWithConcreteAndTemplatedPathsOverlap
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::GET)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/mine', method: HttpMethod::GET)]
    public function mine(): string
    {
        return 'mine';
    }
}

final class ApiWithTheSamePathsAndMethods
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::POST)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/{pet}', method: HttpMethod::POST)]
    public function foo(string $foo): string
    {
        return 'mine';
    }
}

final class ApiWithTheSamePathsButDifferentMethods
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::GET)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/{pet}', method: HttpMethod::POST)]
    public function foo(string $foo): string
    {
        return 'mine';
    }
}


final class ApiWithTheSamePathStructureAndMethods
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::GET)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/{foo}', method: HttpMethod::GET)]
    public function foo(string $foo): string
    {
        return 'mine';
    }
}

final class ApiWithTheSamePathStructureButDifferentMethods
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::GET)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/{foo}', method: HttpMethod::POST)]
    public function foo(string $foo): string
    {
        return 'mine';
    }
}
