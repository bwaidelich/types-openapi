<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Middleware;

use Closure;
use Wwwision\JsonSchema as Json;
use Wwwision\Types\Schema as Types;
use Wwwision\Types\Schema\ListSchema;
use Wwwision\TypesJsonSchema\Middleware\SchemaGeneratorMiddleware;

final class GeneratorMiddleware implements SchemaGeneratorMiddleware
{
    private const SCHEMA_TYPES_TO_REFERENCE = [
        Types\EnumSchema::class,
        Types\FloatSchema::class,
        Types\IntegerSchema::class,
        Types\InterfaceSchema::class,
        Types\ListSchema::class,
        Types\ShapeSchema::class,
        Types\StringSchema::class,
    ];

    /**
     * @var array<string, Json\Schema>
     */
    public array $generatedJsonSchemas = [];

    public function __invoke(Types\Schema $schema, Closure $next): Json\Schema
    {
        $convertSchema = function (Types\Schema $schema) use ($next): Json\Schema {
            $jsonSchema = $next($schema);
            if ($jsonSchema instanceof Json\OneOfSchema && $jsonSchema->discriminator?->mapping !== null) {
                $jsonSchema = $jsonSchema->withDiscriminator(
                    new Json\Discriminator(
                        $jsonSchema->discriminator->propertyName,
                        array_map(static fn(string $className) => '#/components/schemas/' . substr($className, strrpos($className, '\\') + 1), $jsonSchema->discriminator->mapping),
                    ),
                );
            }
            return $jsonSchema;
        };
        if (in_array($schema::class, self::SCHEMA_TYPES_TO_REFERENCE, true)) {
            if (!array_key_exists($schema->getName(), $this->generatedJsonSchemas)) {
                $this->generatedJsonSchemas[$schema->getName()] = $convertSchema($schema);
            }
            return new Json\ReferenceSchema('#/components/schemas/' . $schema->getName());
        }
        if ($schema instanceof ListSchema) {
            return new Json\ArraySchema(
                description: $schema->getDescription(),
                items: $convertSchema($schema->itemSchema),
                minItems: $schema->minCount,
                maxItems: $schema->maxCount,
            );
        }
        return $convertSchema($schema);
    }
}
