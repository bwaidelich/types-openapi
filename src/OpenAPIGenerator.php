<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;
use UnitEnum;
use Webmozart\Assert\Assert;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Parser;
use Wwwision\Types\Schema\ListSchema;
use Wwwision\Types\Schema\LiteralBooleanSchema;
use Wwwision\Types\Schema\LiteralIntegerSchema;
use Wwwision\Types\Schema\LiteralStringSchema;
use Wwwision\Types\Schema\Schema;
use Wwwision\TypesJSONSchema\JSONSchemaGenerator;
use Wwwision\TypesJSONSchema\Types as JSON;
use Wwwision\TypesOpenAPI\Attributes\OpenApi;
use Wwwision\TypesOpenAPI\Attributes\Operation;
use Wwwision\TypesOpenAPI\Exception\AmbiguousPathException;
use Wwwision\TypesOpenAPI\Response\OpenApiResponse;
use Wwwision\TypesOpenAPI\Security\AuthenticationContext;
use Wwwision\TypesOpenAPI\Types\ApiVersion;
use Wwwision\TypesOpenAPI\Types\ComponentsObject;
use Wwwision\TypesOpenAPI\Types\HttpMethod;
use Wwwision\TypesOpenAPI\Types\HttpStatusCode;
use Wwwision\TypesOpenAPI\Types\InfoObject;
use Wwwision\TypesOpenAPI\Types\MediaTypeObject;
use Wwwision\TypesOpenAPI\Types\MediaTypeObjectMap;
use Wwwision\TypesOpenAPI\Types\MediaTypeRange;
use Wwwision\TypesOpenAPI\Types\OpenAPIGeneratorOptions;
use Wwwision\TypesOpenAPI\Types\OpenAPIObject;
use Wwwision\TypesOpenAPI\Types\OpenApiVersion;
use Wwwision\TypesOpenAPI\Types\OperationObject;
use Wwwision\TypesOpenAPI\Types\ParameterLocation;
use Wwwision\TypesOpenAPI\Types\ParameterObject;
use Wwwision\TypesOpenAPI\Types\ParameterOrReferenceObjects;
use Wwwision\TypesOpenAPI\Types\PathObject;
use Wwwision\TypesOpenAPI\Types\PathsObject;
use Wwwision\TypesOpenAPI\Types\RelativePath;
use Wwwision\TypesOpenAPI\Types\RequestBodyObject;
use Wwwision\TypesOpenAPI\Types\ResponseObject;
use Wwwision\TypesOpenAPI\Types\ResponsesObject;
use Wwwision\TypesOpenAPI\Types\SchemaObjectMap;

final class OpenAPIGenerator
{
    /**
     * @var array<string, JSON\Schema>
     */
    private array $generatedJsonSchemas = [];

    public function generate(string $className, OpenAPIGeneratorOptions $options): OpenAPIObject
    {
        Assert::classExists($className);
        $reflectionClass = new ReflectionClass($className);

        /** @var OpenApi|null $openApiAttributeInstance */
        $openApiAttributeInstance = ($reflectionClass->getAttributes(OpenApi::class)[0] ?? null)?->newInstance();

        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $paths = [];

        foreach ($reflectionMethods as $reflectionMethod) {
            $operationAttribute = $reflectionMethod->getAttributes(Operation::class)[0] ?? null;
            if ($operationAttribute === null) {
                continue;
            }
            /** @var Operation $operationAttributeInstance */
            $operationAttributeInstance = $operationAttribute->newInstance();

            $requestBody = null;

            $parameters = [];

            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameterReflectionType = $reflectionParameter->getType();
                Assert::isInstanceOf($parameterReflectionType, ReflectionNamedType::class);
                if (is_subclass_of($parameterReflectionType->getName(), AuthenticationContext::class)) {
                    if ($openApiAttributeInstance?->security === null && $operationAttributeInstance->security === null) {
                        throw new RuntimeException(sprintf('The argument "%s" of method "%s" is of type %s, but this operation does not require authentication', $reflectionParameter->name, $reflectionMethod->getName(), $parameterReflectionType->getName()), 1743579641);
                    }
                    continue;
                }

                $parameterSchema = self::reflectionTypeToSchema($parameterReflectionType);
                $parameterJSONSchema = $this->schemaToJsonSchema($parameterSchema);
                $defaultParameterValue = null;
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $defaultParameterValue = $reflectionParameter->getDefaultValue();
                    if ($defaultParameterValue instanceof UnitEnum) {
                        $defaultParameterValue = $defaultParameterValue->name;
                    }
                }

                if (in_array($operationAttributeInstance->method, [HttpMethod::POST, HttpMethod::PUT, HttpMethod::PATCH], true)) {
                    if ($requestBody !== null) {
                        throw new RuntimeException(sprintf('Method "%s" contains multiple parameters, but for HTTP method of "%s" only a single parameter is supported currently', $reflectionMethod->getName(), $operationAttributeInstance->method->name), 1706538672);
                    }
                    $requestBody = new RequestBodyObject(
                        content: MediaTypeObjectMap::create()->with(MediaTypeRange::fromString('application/json'), new MediaTypeObject(
                            schema: $parameterJSONSchema,
                            meta: [
                                'schema' => $parameterSchema,
                            ],
                        )),
                        description: self::getDescription($reflectionParameter),
                        required: !$reflectionParameter->isOptional(),
                    );
                } else {
                    $parameters[$reflectionParameter->name] = new ParameterObject(
                        name: $reflectionParameter->getName(),
                        in: $operationAttributeInstance->path->containsPlaceholder($reflectionParameter->getName()) ? ParameterLocation::path : ParameterLocation::query,
                        description: self::getDescription($reflectionParameter),
                        required: !$reflectionParameter->isOptional(),
                        schema: $parameterJSONSchema,
                        default: $defaultParameterValue,
                        meta: [
                            'schema' => $parameterSchema,
                        ],
                    );
                }
            }

            if (isset($paths[$operationAttributeInstance->path->value][$operationAttributeInstance->method->name])) {
                throw new AmbiguousPathException(sprintf('The path "%s" is duplicated for HTTP method "%s"', $operationAttributeInstance->path->value, $operationAttributeInstance->method->name), 1712233883);
            }
            $returnType = $reflectionMethod->getReturnType();
            Assert::notNull($returnType, sprintf('Return type of method "%s" is missing', $reflectionMethod->getName()));
            Assert::isInstanceOfAny($returnType, [ReflectionNamedType::class, ReflectionUnionType::class], sprintf('Return type of method "%s" was expected to be of type %%2$s. Got: %%s', $reflectionMethod->getName()));
            /** @var ReflectionNamedType|ReflectionUnionType $returnType */
            $responsesObject = $this->responsesObjectFromReturnType($returnType, $operationAttributeInstance);
            if (($requestBody !== null || $parameters !== []) && !$responsesObject->hasResponseForStatusCode(400)) {
                $responsesObject = $responsesObject->with(HttpStatusCode::fromInteger(400), new ResponseObject('Bad Request'));
            }
            $meta = [
                'methodName' => $reflectionMethod->getName(),
            ];
            $paths[$operationAttributeInstance->path->value][$operationAttributeInstance->method->name] = new OperationObject(
                description: self::getDescription($reflectionMethod),
                operationId: $reflectionMethod->getName(),
                parameters: $parameters !== [] ? new ParameterOrReferenceObjects(...$parameters) : null,
                requestBody: $requestBody,
                responses: $responsesObject,
                security: $operationAttributeInstance->security,
                meta: $meta,
            );
        }
        $pathObjects = PathsObject::create();
        foreach ($paths as $path => $methods) {
            $operationObjects = [];
            foreach ($methods as $method => $operation) {
                $operationObjects[$method] = $operation;
            }
            $pathObject = new PathObject(
                get: $operationObjects['GET'] ?? null,
                put: $operationObjects['PUT'] ?? null,
                post: $operationObjects['POST'] ?? null,
                delete: $operationObjects['DELETE'] ?? null,
                options: $operationObjects['OPTIONS'] ?? null,
                head: $operationObjects['HEAD'] ?? null,
                patch: $operationObjects['PATCH'] ?? null,
                trace: $operationObjects['TRACE'] ?? null,
            );
            $pathObjects = $pathObjects->with(RelativePath::fromString($path), $pathObject);
        }

        if ($this->generatedJsonSchemas !== [] || $openApiAttributeInstance?->securitySchemes !== null) {
            $componentsObject = new ComponentsObject(
                schemas: $this->generatedJsonSchemas !== [] ? new SchemaObjectMap(...$this->generatedJsonSchemas) : null,
                securitySchemes: $openApiAttributeInstance?->securitySchemes,
            );
        } else {
            $componentsObject = null;
        }

        return new OpenAPIObject(
            openapi: $openApiAttributeInstance->openApiVersion ?? OpenApiVersion::current(),
            info: new InfoObject(
                title: $openApiAttributeInstance->apiTitle ?? '',
                version: $openApiAttributeInstance->apiVersion ?? ApiVersion::default(),
            ),
            servers: $options->servers,
            paths: $pathObjects,
            components: $componentsObject,
            security: $openApiAttributeInstance?->security,
        );
    }



    private function schemaToJsonSchema(Schema $schema): Json\Schema
    {
        if ($schema instanceof ListSchema) {
            $jsonSchema = new Json\ArraySchema(
                description: $schema->getDescription(),
                items: $this->schemaToJsonSchema($schema->itemSchema),
                minItems: $schema->minCount,
                maxItems: $schema->maxCount,
            );
        } else {
            $jsonSchema = JSONSchemaGenerator::fromSchema($schema);
        }
        if (in_array($schema::class, [LiteralStringSchema::class, LiteralBooleanSchema::class, LiteralIntegerSchema::class], true)) {
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

    private static function getDescription(ReflectionMethod|ReflectionParameter $reflection): null|string
    {
        $descriptionAttributes = $reflection->getAttributes(Description::class);
        if (!isset($descriptionAttributes[0])) {
            return null;
        }
        /** @var Description $instance */
        $instance = $descriptionAttributes[0]->newInstance();
        return $instance->value;
    }

    private function responsesObjectFromReturnType(ReflectionNamedType|ReflectionUnionType $returnType, Operation $operation): ResponsesObject
    {
        $returnTypes = $returnType instanceof ReflectionUnionType ? $returnType->getTypes() : [$returnType];
        Assert::allIsInstanceOf($returnTypes, ReflectionNamedType::class);
        $responsesObject = ResponsesObject::create();
        foreach ($returnTypes as $type) {
            // return type is `void` or `null` or `null|SomeOtherType`
            if ($type->allowsNull() || ($type->isBuiltin() && $type->getName() === 'void')) {
                if ($type->getName() === 'void' || $type->getName() === 'null') {
                    continue;
                }
            }
            if (is_subclass_of($type->getName(), OpenApiResponse::class)) {
                /** @var class-string<OpenApiResponse> $responseClass */
                $responseClass = $type->getName();
                $responsesObject = $responsesObject->with($responseClass::statusCode(), new ResponseObject(
                    description: $responseClass::description(),
                ));
                continue;
            }
            $returnTypeSchema = $type->isBuiltin() && $type->getName() === 'void' ? null : $this->returnTypeToSchema($type);
            $responsesObject = $responsesObject->with(HttpStatusCode::fromInteger(200), new ResponseObject(
                description: 'Default',
                content: MediaTypeObjectMap::create()->with(
                    MediaTypeRange::fromString('application/json'),
                    new MediaTypeObject(
                        schema: $returnTypeSchema,
                    ),
                ),
            ));
        }
        return $responsesObject;
    }

    private function returnTypeToSchema(ReflectionType $reflectionType): JSON\Schema
    {
        Assert::isInstanceOfAny($reflectionType, [ReflectionIntersectionType::class, ReflectionUnionType::class, ReflectionNamedType::class]);
        /** @var ReflectionIntersectionType|ReflectionUnionType|ReflectionNamedType $reflectionType **/
        if ($reflectionType instanceof ReflectionIntersectionType) {
            return JSON\AllOfSchema::create(
                ...array_map(
                    $this->returnTypeToSchema(...),
                    $reflectionType->getTypes(),
                ),
            );
        }
        if ($reflectionType instanceof ReflectionUnionType) {
            return JSON\AnyOfSchema::create(...array_map($this->returnTypeToSchema(...), $reflectionType->getTypes()));
        }
        $schema = self::reflectionTypeToSchema($reflectionType);
        return $this->schemaToJsonSchema($schema);
    }
}
