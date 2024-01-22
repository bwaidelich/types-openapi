<?php
declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesOpenAPI\Types\Directive;
use Wwwision\TypesOpenAPI\Types\Directives;
use Wwwision\TypesOpenAPI\Types\EnumTypeDefinition;
use Wwwision\TypesOpenAPI\Types\EnumValueDefinition;
use Wwwision\TypesOpenAPI\Types\EnumValueDefinitions;
use Wwwision\TypesOpenAPI\Types\FieldDefinition;
use Wwwision\TypesOpenAPI\Types\FieldDefinitions;
use Wwwision\TypesOpenAPI\Types\FieldType;
use Wwwision\TypesOpenAPI\Types\InterfaceDefinition;
use Wwwision\TypesOpenAPI\Types\ObjectTypeDefinition;
use Wwwision\TypesOpenAPI\Types\ScalarTypeDefinition;

#[CoversClass(InterfaceDefinition::class)]
#[CoversClass(Directives::class)]
#[CoversClass(Directive::class)]
#[CoversClass(FieldDefinitions::class)]
#[CoversClass(FieldDefinition::class)]
#[CoversClass(FieldType::class)]
final class InterfaceDefinitionTest extends TestCase
{

    public function test_simple(): void
    {
        $definition = new InterfaceDefinition(name: 'SomeInterface', fieldDefinitions: new FieldDefinitions(new FieldDefinition(name: 'foo', type: new FieldType('String', true))), description: 'Some interface description', directives: new Directives(new Directive(name: 'foo'), new Directive(name: 'bar')));

        $expected = <<<GRAPHQL
            """ Some interface description """
            interface SomeInterface @foo @bar {
              foo: String!
            }

            GRAPHQL;
        self::assertSame($expected, $definition->render());
    }

}
