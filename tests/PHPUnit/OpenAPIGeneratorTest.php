<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit;

require_once __DIR__ . '/Fixture/Fixture.php';

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesOpenAPI\Exception\AmbiguousPathException;
use Wwwision\TypesOpenAPI\OpenAPIGenerator;
use Wwwision\TypesOpenAPI\Types\OpenAPIGeneratorOptions;
use Wwwision\TypesOpenAPI\Types\ServerObject;
use Wwwision\TypesOpenAPI\Types\ServerObjects;

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

    public function test_generate_petStore(): void
    {
        $servers = [];
        foreach (['local' => 'http://localhost:8081', 'prod' => 'https://foo-bar.com/'] as $description => $url) {
            $servers[] = new ServerObject($url, $description);
        }
        $schema = $this->generator->generate(Fixture\PetStoreApi::class, OpenAPIGeneratorOptions::create(servers: new ServerObjects(...$servers), apiTitle: 'Overridden'));

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
                                },
                                "404": {
                                    "description": "Not Found"
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

    public function test_generate_anotherApi(): void
    {
        $schema = $this->generator->generate(Fixture\AnotherApi::class, OpenAPIGeneratorOptions::create());
        $expected = <<<'JSON'
        {
            "openapi": "3.0.3",
            "info": {
                "title": "",
                "version": "0.0.0"
            },
            "paths": {
                "\/some-interface": {
                    "patch": {
                        "operationId": "someInterface",
                        "responses": {
                            "200": {
                                "description": "Default",
                                "content": {
                                    "application\/json": {
                                        "schema": {
                                            "$ref": "#\/components\/schemas\/SomeInterface"
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            },
            "components": {
                "schemas": {
                    "EmailAddress": {
                        "type": "string",
                        "format": "email"
                    },
                    "ImplementationA": {
                        "type": "object",
                        "properties": {
                            "someString": {
                                "type": "string"
                            },
                            "emailAddress": {
                                "$ref": "#\/components\/schemas\/EmailAddress"
                            }
                        },
                        "additionalProperties": false,
                        "required": [
                            "someString",
                            "emailAddress"
                        ]
                    },
                    "ImplementationB": {
                        "type": "object",
                        "properties": {
                            "someBoolean": {
                                "type": "boolean"
                            },
                            "emailAddress": {
                                "$ref": "#\/components\/schemas\/EmailAddress"
                            }
                        },
                        "additionalProperties": false,
                        "required": [
                            "someBoolean",
                            "emailAddress"
                        ]
                    },
                    "SomeInterface": {
                        "oneOf": [
                            {
                                "$ref": "#\/components\/schemas\/ImplementationA"
                            },
                            {
                                "$ref": "#\/components\/schemas\/ImplementationB"
                            }
                        ]
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
        yield 'concrete and templated paths overlap' => ['className' => Fixture\ApiWithConcreteAndTemplatedPathsOverlap::class, 'expectedPaths' => ['/pets/mine' => ['get'], '/pets/{pet}' => ['get']]];
        yield 'same paths but different methods' => ['className' => Fixture\ApiWithTheSamePathsButDifferentMethods::class, 'expectedPaths' => ['/pets/{pet}' => ['get', 'post']]];
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
        yield 'same paths and methods' => ['className' => Fixture\ApiWithTheSamePathsAndMethods::class];
        yield 'same path structure and methods' => ['className' => Fixture\ApiWithTheSamePathStructureAndMethods::class];
        yield 'same path structure but different methods' => ['className' => Fixture\ApiWithTheSamePathStructureButDifferentMethods::class];
    }

    #[DataProvider('invalid_paths_provider')]
    public function test_invalid_paths(string $className): void
    {
        $this->expectException(AmbiguousPathException::class);
        $this->generator->generate($className, OpenAPIGeneratorOptions::create());
    }

}
