<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi;

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
use Wwwision\JsonSchema as Json;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Parser;
use Wwwision\Types\Schema as Types;
use Wwwision\Types\Schema\LiteralBooleanSchema;
use Wwwision\Types\Schema\LiteralIntegerSchema;
use Wwwision\Types\Schema\LiteralStringSchema;
use Wwwision\TypesJsonSchema\JsonSchemaGenerator;
use Wwwision\TypesJsonSchema\JsonSchemaGeneratorOptions;
use Wwwision\TypesOpenApi\Attributes\OpenApi;
use Wwwision\TypesOpenApi\Attributes\Operation;
use Wwwision\TypesOpenApi\Attributes\Parameter;
use Wwwision\TypesOpenApi\Exception\AmbiguousPathException;
use Wwwision\TypesOpenApi\Middleware\GeneratorMiddleware;
use Wwwision\TypesOpenApi\Response\OpenApiResponse;
use Wwwision\TypesOpenApi\Security\AuthenticationContext;
use Wwwision\TypesOpenApi\Types\ApiVersion;
use Wwwision\TypesOpenApi\Types\ComponentsObject;
use Wwwision\TypesOpenApi\Types\HttpMethod;
use Wwwision\TypesOpenApi\Types\HttpStatusCode;
use Wwwision\TypesOpenApi\Types\InfoObject;
use Wwwision\TypesOpenApi\Types\MediaTypeObject;
use Wwwision\TypesOpenApi\Types\MediaTypeObjectMap;
use Wwwision\TypesOpenApi\Types\MediaTypeRange;
use Wwwision\TypesOpenApi\Types\OpenApiObject;
use Wwwision\TypesOpenApi\Types\OpenApiVersion;
use Wwwision\TypesOpenApi\Types\OperationObject;
use Wwwision\TypesOpenApi\Types\ParameterLocation;
use Wwwision\TypesOpenApi\Types\ParameterObject;
use Wwwision\TypesOpenApi\Types\ParameterOrReferenceObjects;
use Wwwision\TypesOpenApi\Types\PathObject;
use Wwwision\TypesOpenApi\Types\PathsObject;
use Wwwision\TypesOpenApi\Types\RelativePath;
use Wwwision\TypesOpenApi\Types\RequestBodyObject;
use Wwwision\TypesOpenApi\Types\ResponseObject;
use Wwwision\TypesOpenApi\Types\ResponsesObject;
use Wwwision\TypesOpenApi\Types\SchemaObjectMap;

final class OpenApiGenerator
{
    private OpenApiGeneratorOptions $options;

    private JsonSchemaGenerator $jsonSchemaGenerator;

    private GeneratorMiddleware $middleware;

    public function __construct(OpenApiGeneratorOptions|null $options = null)
    {
        $this->options = $options ?? OpenApiGeneratorOptions::create();
        $this->middleware = new GeneratorMiddleware();
        $this->jsonSchemaGenerator = new JsonSchemaGenerator(JsonSchemaGeneratorOptions::create(includeDiscriminator: true)->withMiddleware($this->middleware));
    }


    public function generate(string $className): OpenApiObject
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
            $meta = [];
            $parameters = [];

            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameterReflectionType = $reflectionParameter->getType();
                Assert::isInstanceOf($parameterReflectionType, ReflectionNamedType::class);
                if (is_subclass_of($parameterReflectionType->getName(), AuthenticationContext::class)) {
                    if ($openApiAttributeInstance?->security === null && $operationAttributeInstance->security === null) {
                        throw new RuntimeException(sprintf('The argument "%s" of method "%s" is of type %s, but this operation does not require authentication', $reflectionParameter->name, $reflectionMethod->getName(), $parameterReflectionType->getName()), 1743579641);
                    }
                    $meta = [
                        'authContextParameterName' => $reflectionParameter->name,
                    ];
                    continue;
                }

                $parameterSchema = self::reflectionTypeToSchema($parameterReflectionType);
                $parameterJsonSchema = $this->jsonSchemaGenerator->fromSchema($parameterSchema);
                $defaultParameterValue = null;
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $defaultParameterValue = $reflectionParameter->getDefaultValue();
                    if ($defaultParameterValue instanceof UnitEnum) {
                        $defaultParameterValue = $defaultParameterValue->name;
                    }
                }
                /** @var Parameter|null $parameterAttributeInstance */
                $parameterAttributeInstance = ($reflectionParameter->getAttributes(Parameter::class)[0] ?? null)?->newInstance();
                if ($parameterAttributeInstance !== null) {
                    if ($parameterAttributeInstance->in === ParameterLocation::path && $parameterAttributeInstance->name !== null) {
                        throw new RuntimeException(sprintf('The parameter "%s" of method "%s" contains a custom name, but for path parameters the name is derived from the path template', $reflectionParameter->getName(), $reflectionMethod->getName()), 1744296152);
                    }
                    $parameters[] = new ParameterObject(
                        name: $parameterAttributeInstance->name ?? $reflectionParameter->getName(),
                        in: $parameterAttributeInstance->in,
                        description: self::getDescription($reflectionParameter),
                        required: !$reflectionParameter->isOptional(),
                        schema: $parameterJsonSchema,
                        default: $defaultParameterValue,
                        meta: [
                            'schema' => $parameterSchema,
                            'parameterName' => $reflectionParameter->name,
                        ],
                    );
                    continue;
                }
                if ($operationAttributeInstance->path->containsPlaceholder($reflectionParameter->getName())) {
                    $parameters[] = new ParameterObject(
                        name: $reflectionParameter->getName(),
                        in: ParameterLocation::path,
                        description: self::getDescription($reflectionParameter),
                        required: !$reflectionParameter->isOptional(),
                        schema: $parameterJsonSchema,
                        default: $defaultParameterValue,
                        meta: [
                            'schema' => $parameterSchema,
                            'parameterName' => $reflectionParameter->name,
                        ],
                    );
                    continue;
                }

                if ($requestBody === null && in_array($operationAttributeInstance->method, [HttpMethod::POST, HttpMethod::PUT, HttpMethod::PATCH], true)) {
                    $requestBody = new RequestBodyObject(
                        content: MediaTypeObjectMap::create()->with(MediaTypeRange::fromString('application/json'), new MediaTypeObject(
                            schema: $parameterJsonSchema,
                            meta: [
                                'schema' => $parameterSchema,
                            ],
                        )),
                        description: self::getDescription($reflectionParameter),
                        required: !$reflectionParameter->isOptional(),
                        meta: [
                            'parameterName' => $reflectionParameter->name,
                        ],
                    );
                } else {
                    $parameters[] = new ParameterObject(
                        name: $reflectionParameter->getName(),
                        in: ParameterLocation::query,
                        description: self::getDescription($reflectionParameter),
                        required: !$reflectionParameter->isOptional(),
                        schema: $parameterJsonSchema,
                        default: $defaultParameterValue,
                        meta: [
                            'schema' => $parameterSchema,
                            'parameterName' => $reflectionParameter->name,
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
            $meta['methodName'] = $reflectionMethod->getName();
            $paths[$operationAttributeInstance->path->value][$operationAttributeInstance->method->name] = new OperationObject(
                summary: $operationAttributeInstance->summary,
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

        if ($this->middleware->generatedJsonSchemas !== [] || $openApiAttributeInstance?->securitySchemes !== null) {
            $componentsObject = new ComponentsObject(
                schemas: $this->middleware->generatedJsonSchemas !== [] ? new SchemaObjectMap(...$this->middleware->generatedJsonSchemas) : null,
                securitySchemes: $openApiAttributeInstance?->securitySchemes,
            );
        } else {
            $componentsObject = null;
        }

        return new OpenApiObject(
            openapi: $openApiAttributeInstance->openApiVersion ?? OpenApiVersion::current(),
            info: new InfoObject(
                title: $openApiAttributeInstance->apiTitle ?? '',
                version: $openApiAttributeInstance->apiVersion ?? ApiVersion::default(),
            ),
            servers: $this->options->servers,
            paths: $pathObjects,
            components: $componentsObject,
            security: $openApiAttributeInstance?->security,
        );
    }

    private static function reflectionTypeToSchema(ReflectionNamedType $reflectionType): Types\Schema
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
        if (!interface_exists($typeClassName)) {
            Assert::classExists($typeClassName);
        }
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

    private function returnTypeToSchema(ReflectionType $reflectionType): Json\Schema
    {
        Assert::isInstanceOfAny($reflectionType, [ReflectionIntersectionType::class, ReflectionUnionType::class, ReflectionNamedType::class]);
        /** @var ReflectionIntersectionType|ReflectionUnionType|ReflectionNamedType $reflectionType **/
        if ($reflectionType instanceof ReflectionIntersectionType) {
            return Json\AllOfSchema::create(
                ...array_map(
                    $this->returnTypeToSchema(...),
                    $reflectionType->getTypes(),
                ),
            );
        }
        if ($reflectionType instanceof ReflectionUnionType) {
            return Json\AnyOfSchema::create(...array_map($this->returnTypeToSchema(...), $reflectionType->getTypes()));
        }
        $schema = self::reflectionTypeToSchema($reflectionType);
        return $this->jsonSchemaGenerator->fromSchema($schema);
    }
}
