<?php
declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesOpenAPI\Types\Directive;
use Wwwision\TypesOpenAPI\Types\Directives;
use Wwwision\TypesOpenAPI\Types\ScalarTypeDefinition;

#[CoversClass(ScalarTypeDefinition::class)]
#[CoversClass(Directive::class)]
#[CoversClass(Directives::class)]
final class ScalarTypeDefinitionTest extends TestCase
{

    public function test_just_name(): void
    {
        $definition = new ScalarTypeDefinition(name: 'SomeScalar');

        $expected = <<<GRAPHQL
            scalar SomeScalar

            GRAPHQL;
        self::assertSame($expected, $definition->render());
    }

    public function test_name_and_description(): void
    {
        $definition = new ScalarTypeDefinition(name: 'SomeScalar', description: 'Some description');

        $expected = <<<GRAPHQL
            """
            Some description
            """
            scalar SomeScalar

            GRAPHQL;
        self::assertSame($expected, $definition->render());
    }

    public function test_name_and_description_and_directives(): void
    {
        $definition = new ScalarTypeDefinition(name: 'SomeScalar', description: 'Some description', directives: new Directives(new Directive(name: 'foo'), new Directive(name: 'bar')));

        $expected = <<<GRAPHQL
            """
            Some description
            """
            scalar SomeScalar @foo @bar

            GRAPHQL;
        self::assertSame($expected, $definition->render());
    }

}
