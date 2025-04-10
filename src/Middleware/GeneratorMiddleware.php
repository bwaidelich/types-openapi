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
    /**
     * @var array<string, Json\Schema>
     */
    public array $generatedJsonSchemas = [];

    public function __invoke(Types\Schema $schema, Closure $next): Json\Schema
    {
        $schemaTypesToReference = [
            Types\EnumSchema::class,
            Types\FloatSchema::class,
            Types\IntegerSchema::class,
            Types\InterfaceSchema::class,
            Types\ListSchema::class,
            Types\ShapeSchema::class,
            Types\StringSchema::class,
        ];
        if (in_array($schema::class, $schemaTypesToReference, true)) {
            if (!array_key_exists($schema->getName(), $this->generatedJsonSchemas)) {
                $this->generatedJsonSchemas[$schema->getName()] = $next($schema);
            }
            return new Json\ReferenceSchema('#/components/schemas/' . $schema->getName());
        }
        if ($schema instanceof ListSchema) {
            return new Json\ArraySchema(
                description: $schema->getDescription(),
                items: $next($schema->itemSchema),
                minItems: $schema->minCount,
                maxItems: $schema->maxCount,
            );
        }
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
    }
}
