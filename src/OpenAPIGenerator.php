<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use RuntimeException;
use UnitEnum;
use Webmozart\Assert\Assert;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Parser;
use Wwwision\Types\Schema\LiteralBooleanSchema;
use Wwwision\Types\Schema\LiteralIntegerSchema;
use Wwwision\Types\Schema\LiteralStringSchema;
use Wwwision\Types\Schema\Schema;
use Wwwision\TypesJSONSchema\JSONSchemaGenerator;
use Wwwision\TypesJSONSchema\Types as JSON;
use Wwwision\TypesOpenAPI\Attributes\Operation;
use Wwwision\TypesOpenAPI\Attributes\Response;
use Wwwision\TypesOpenAPI\Tests\PHPUnit\Pet;
use Wwwision\TypesOpenAPI\Tests\PHPUnit\Pets;
use Wwwision\TypesOpenAPI\Types\ComponentsObject;
use Wwwision\TypesOpenAPI\Types\HttpStatusCode;
use Wwwision\TypesOpenAPI\Types\InfoObject;
use Wwwision\TypesOpenAPI\Types\MediaTypeObject;
use Wwwision\TypesOpenAPI\Types\MediaTypeObjectMap;
use Wwwision\TypesOpenAPI\Types\MediaTypeRange;
use Wwwision\TypesOpenAPI\Types\OpenAPIObject;
use Wwwision\TypesOpenAPI\Types\OpenApiVersion;
use Wwwision\TypesOpenAPI\Types\OperationObject;
use Wwwision\TypesOpenAPI\Types\ParameterLocation;
use Wwwision\TypesOpenAPI\Types\ParameterObject;
use Wwwision\TypesOpenAPI\Types\ParameterOrReferenceObjects;
use Wwwision\TypesOpenAPI\Types\PathObject;
use Wwwision\TypesOpenAPI\Types\PathsObject;
use Wwwision\TypesOpenAPI\Types\RelativePath;
use Wwwision\TypesOpenAPI\Types\ResponseObject;
use Wwwision\TypesOpenAPI\Types\ResponsesObject;
use Wwwision\TypesOpenAPI\Types\SchemaObjectMap;
use Wwwision\TypesOpenAPI\Types\ServerObject;
use Wwwision\TypesOpenAPI\Types\ServerObjects;

final class OpenAPIGenerator
{

    /**
     * @var array<string, JSON\Schema>
     */
    private array $generatedJsonSchemas = [];

    public function __construct()
    {
    }

    public function generate(string $className): OpenAPIObject
    {
        Assert::classExists($className);
        $reflectionClass = new ReflectionClass($className);
        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        $paths = [];
        $schemas = [];

        #$responseAttributes = $reflectionClass->getAttributes(Response::class);

        foreach ($reflectionMethods as $reflectionMethod) {
            $operationAttribute = $reflectionMethod->getAttributes(Operation::class)[0] ?? null;
            if ($operationAttribute === null) {
                continue;
            }
            $operationAttributeInstance = $operationAttribute->newInstance();

            $parameters = [];
            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameterReflectionType = $reflectionParameter->getType();
                Assert::isInstanceOf($parameterReflectionType, ReflectionNamedType::class);
                $parameterSchema = self::reflectionTypeToSchema($parameterReflectionType);
                $parameterJSONSchema = JSONSchemaGenerator::fromSchema($parameterSchema);
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $defaultParameterValue = $reflectionParameter->getDefaultValue();
                    if ($defaultParameterValue instanceof UnitEnum) {
                        $defaultParameterValue = $defaultParameterValue->name;
                    }
                    $parameterJSONSchema = match ($parameterJSONSchema::class) {
                        JSON\ArraySchema::class,
                        JSON\BooleanSchema::class,
                        JSON\IntegerSchema::class,
                        JSON\NumberSchema::class,
                        JSON\ObjectSchema::class,
                        JSON\StringSchema::class => $parameterJSONSchema->with(default: $defaultParameterValue), // @phpstan-ignore-line
                        default => throw new RuntimeException(sprintf('For method "%s" the default value for parameter "%s" can^\'t be mapped. JSON Schema type: %s', $reflectionMethod->getName(), $reflectionParameter->getName(), get_debug_type($parameterJSONSchema)), 1705573795),
                    };
                }

                $parameters[] = new ParameterObject(
                    name: $reflectionParameter->getName(),
                    in: ParameterLocation::query,
                    description: self::getDescription($reflectionParameter),
                    required: !$reflectionParameter->isOptional(),
                    schema: $parameterJSONSchema,
                );
            }

            if (isset($paths[$operationAttributeInstance->path->value][$operationAttributeInstance->method->name])) {
                // TODO throw exception
            }
            $returnType = $reflectionMethod->getReturnType();
            Assert::notNull($returnType, sprintf('Return type of method "%s" is missing', $reflectionMethod->getName()));
            Assert::isInstanceOf($returnType, ReflectionNamedType::class, sprintf('Return type of method "%s" was expected to be of type %%2$s. Got: %%s', $reflectionMethod->getName()));
            $returnTypeSchema = $this->returnTypeToSchema($returnType);

            $responsesObject = ResponsesObject::create()
                ->with(HttpStatusCode::fromInteger(200), new ResponseObject(
                    description: 'successful operation',
                    content: MediaTypeObjectMap::create()->with(
                        MediaTypeRange::fromString('application/json'),
                        new MediaTypeObject(
                            schema: $returnTypeSchema
                        )
                    )
                ));
            $paths[$operationAttributeInstance->path->value][$operationAttributeInstance->method->name] = new OperationObject(
                description: self::getDescription($reflectionMethod),
                parameters: $parameters !== [] ? new ParameterOrReferenceObjects(...$parameters) : null,
                responses: $responsesObject,
            );
        }
        $pathObjects = PathsObject::create();
        foreach ($paths as $path => $methods) {
            foreach ($methods as $operation) {
                $pathObject = new PathObject(get: $operation);
                $pathObjects = $pathObjects->with(RelativePath::fromString($path), $pathObject);
            }
        }

        return new OpenAPIObject(
            openapi: OpenApiVersion::fromString('3.0.3'),
            info: new InfoObject(
                title: 'todo',
                version: '1.0.0',
            ),
            servers: new ServerObjects(
                new ServerObject(
                    'http://localhost:8081/',
                )
            ),
            paths: $pathObjects,
            components: new ComponentsObject(
                schemas: new SchemaObjectMap(...$this->generatedJsonSchemas),
            )
        );
    }

    private function returnTypeToSchema(ReflectionIntersectionType|ReflectionUnionType|ReflectionNamedType $reflectionType): JSON\Schema
    {
        if ($reflectionType instanceof ReflectionIntersectionType) {
            return JSON\AllOfSchema::create(...array_map($this->returnTypeToSchema(...), $reflectionType->getTypes()));
        }
        if ($reflectionType instanceof ReflectionUnionType) {
            return JSON\AnyOfSchema::create(...array_map($this->returnTypeToSchema(...), $reflectionType->getTypes()));
        }
        $schema = self::reflectionTypeToSchema($reflectionType);
        $jsonSchema = JSONSchemaGenerator::fromSchema($schema);
        if ($jsonSchema instanceof JSON\ArraySchema) {
            return $jsonSchema;
        }
        $this->generatedJsonSchemas[$schema->getName()] = $jsonSchema;
        return new JSON\ReferenceSchema('#/components/schemas/' . $schema->getName());
    }

    private static function reflectionTypeToSchema(ReflectionNamedType $reflectionType): Schema
    {
        if ($reflectionType->isBuiltin()) {
            return match ($reflectionType->getName()) {
                'bool' => new LiteralBooleanSchema(null),
                'int' => new LiteralIntegerSchema(null),
                'string' => new LiteralStringSchema(null),
                default => throw new InvalidArgumentException(sprintf('No support for type %s', $reflectionType->getName())),
            };
        }
        $typeClassName = $reflectionType->getName();
        Assert::classExists($typeClassName);
        return Parser::getSchema($typeClassName);
    }

    private static function getDescription(ReflectionMethod|ReflectionParameter $reflection): ?string
    {
        $descriptionAttributes = $reflection->getAttributes(Description::class);
        if (!isset($descriptionAttributes[0])) {
            return null;
        }
        /** @var Description $instance */
        $instance = $descriptionAttributes[0]->newInstance();
        return $instance->value;
    }
}
