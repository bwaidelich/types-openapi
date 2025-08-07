<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Tests\PHPUnit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesOpenApi\Exception\AmbiguousPathException;
use Wwwision\TypesOpenApi\OpenApiGenerator;
use Wwwision\TypesOpenApi\OpenApiGeneratorOptions;
use Wwwision\TypesOpenApi\Types\ServerObject;
use Wwwision\TypesOpenApi\Types\ServerObjects;

#[CoversClass(OpenApiGenerator::class)]
final class OpenApiGeneratorTest extends TestCase
{
    private OpenApiGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new OpenApiGenerator();
    }

    public function test_generate_throws_exception_if_specified_class_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->generator->generate('NonExistingClass');
    }

    public function test_generate_petStore(): void
    {
        $servers = [];
        foreach (['local' => 'http://localhost:8081', 'prod' => 'https://foo-bar.com/'] as $description => $url) {
            $servers[] = new ServerObject($url, $description);
        }
        $generator = new OpenApiGenerator(OpenApiGeneratorOptions::create(servers: new ServerObjects(...$servers), apiTitle: 'Overridden'));
        $schema = $generator->generate(Fixture\PetStoreApi::class);

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
        $schema = $this->generator->generate(Fixture\AnotherApi::class);
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
                        "summary": "Operation summary",
                        "description": "Operation description",
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
                        "discriminator": {
                            "mapping": {
                                "a": "#/components/schemas/ImplementationA",
                                "b": "#/components/schemas/ImplementationB"
                            },
                            "propertyName": "type"
                        },
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


    public function test_generate_apiWithParameters(): void
    {
        $schema = (new OpenApiGenerator())->generate(Fixture\ApiWithParameters::class);
        $expected = <<<'JSON'
        {
            "openapi": "3.0.3",
            "info": {
                "title": "",
                "version": "0.0.0"
            },
            "paths": {
                "\/required-params": {
                    "get": {
                        "operationId": "requiredParams",
                        "parameters": [
                            {
                                "name": "query-param",
                                "in": "query",
                                "required": true,
                                "schema": {
                                    "type": "string"
                                }
                            },
                            {
                                "name": "X-Header-Param",
                                "in": "header",
                                "required": true,
                                "schema": {
                                    "type": "integer"
                                }
                            },
                            {
                                "name": "Cookie-Param",
                                "in": "cookie",
                                "required": true,
                                "schema": {
                                    "type": "boolean"
                                }
                            }
                        ],
                        "responses": {
                            "400": {
                                "description": "Bad Request"
                            }
                        }
                    }
                },
                "\/optional-params": {
                    "get": {
                        "operationId": "optionalParams",
                        "parameters": [
                            {
                                "name": "query-param",
                                "in": "query",
                                "required": false,
                                "schema": {
                                    "type": "string",
                                    "default": "default"
                                }
                            },
                            {
                                "name": "X-Header-Param",
                                "in": "header",
                                "required": false,
                                "schema": {
                                    "type": "integer",
                                    "default": 123
                                }
                            },
                            {
                                "name": "Cookie-Param",
                                "in": "cookie",
                                "required": false,
                                "schema": {
                                    "type": "boolean",
                                    "default": true
                                }
                            }
                        ],
                        "responses": {
                            "400": {
                                "description": "Bad Request"
                            }
                        }
                    }
                }
            }
        }
        JSON;

        self::assertJsonStringEqualsJsonString($expected, json_encode($schema, JSON_THROW_ON_ERROR));
    }

    public function test_generate_apiWithEmptyObject(): void
    {
        $schema = (new OpenApiGenerator())->generate(Fixture\ApiWithEmptyObject::class);
        $expected = <<<'JSON'
        {
            "openapi": "3.0.3",
            "info": {
                "title": "",
                "version": "0.0.0"
            },
            "paths": {
                "\/empty-object": {
                    "post": {
                        "operationId": "emptyObject",
                        "requestBody": {
                            "content": {
                                "application\/json": {
                                    "schema": {
                                        "$ref": "#\/components\/schemas\/EmptyObject"
                                    }
                                }
                            },
                            "required": true
                        },
                        "responses": {
                            "400": {
                                "description": "Bad Request"
                            }
                        }
                    }
                }
            },
            "components": {
                "schemas": {
                    "EmptyObject": {
                        "type": "object",
                        "properties": {},
                        "additionalProperties": false
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
        $schema = $this->generator->generate($className);
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
        $this->generator->generate($className);
    }

}
