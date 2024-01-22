<?php
declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesOpenAPI\Types\ArgumentDefinition;
use Wwwision\TypesOpenAPI\Types\ArgumentDefinitions;
use Wwwision\TypesOpenAPI\Types\ArgumentValue;
use Wwwision\TypesOpenAPI\Types\FieldDefinition;
use Wwwision\TypesOpenAPI\Types\FieldDefinitions;
use Wwwision\TypesOpenAPI\Types\FieldType;
use Wwwision\TypesOpenAPI\Types\ObjectTypeDefinition;
use Wwwision\TypesOpenAPI\Types\ScalarTypeDefinition;

#[CoversClass(ArgumentDefinition::class)]
#[CoversClass(ArgumentDefinitions::class)]
#[CoversClass(ArgumentValue::class)]
#[CoversClass(FieldDefinition::class)]
#[CoversClass(FieldDefinitions::class)]
#[CoversClass(FieldType::class)]
#[CoversClass(ObjectTypeDefinition::class)]
final class ObjectTypeDefinitionTest extends TestCase
{

    public function test_simple(): void
    {
        $definition = new ObjectTypeDefinition(name: 'SomeObject', fieldDefinitions: new FieldDefinitions(new FieldDefinition(name: 'foo', type: new FieldType('String', true))));

        $expected = <<<GRAPHQL
            type SomeObject {
              foo: String!
            }

            GRAPHQL;
        self::assertSame($expected, $definition->render());
    }

    public function test_with_description_and_arguments(): void
    {
        $idFieldDefinition = new FieldDefinition(name: 'id', type: new FieldType('ID', true));
        $nameFieldDefinition = new FieldDefinition(name: 'name', type: new FieldType('String', true), description: 'Some field description');
        $lengthFieldDefinition = new FieldDefinition(name: 'length', type: new FieldType('Float'), argumentDefinitions: new ArgumentDefinitions(new ArgumentDefinition(name: 'unit', type: new FieldType('LengthUnit', false), defaultValue: new ArgumentValue(new class {function __toString() { return 'METER';}}))));

        $definition = new ObjectTypeDefinition(name: 'Starship', fieldDefinitions: new FieldDefinitions($idFieldDefinition, $nameFieldDefinition, $lengthFieldDefinition), description: 'Some object description');

        $expected = <<<GRAPHQL
            """ Some object description """
            type Starship {
              id: ID!
              """ Some field description """
              name: String!
              length(unit: LengthUnit = METER): Float
            }

            GRAPHQL;
        self::assertSame($expected, $definition->render());
    }

    public function test_input_type(): void
    {
        $starsFieldDefinition = new FieldDefinition(name: 'stars', type: new FieldType('Int', true));
        $commentaryFieldDefinition = new FieldDefinition(name: 'commentary', type: new FieldType('String'));

        $definition = new ObjectTypeDefinition(name: 'ReviewInput', fieldDefinitions: new FieldDefinitions($starsFieldDefinition, $commentaryFieldDefinition), isInputType: true);

        $expected = <<<GRAPHQL
            input ReviewInput {
              stars: Int!
              commentary: String
            }

            GRAPHQL;
        self::assertSame($expected, $definition->render());
    }

}
