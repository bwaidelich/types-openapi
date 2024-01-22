<?php
declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Traversable;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Attributes\ListBased;
use Wwwision\TypesOpenAPI\Attributes\Operation;
use Wwwision\TypesOpenAPI\Attributes\Parameter;
use Wwwision\TypesOpenAPI\Attributes\Response;
use Wwwision\TypesOpenAPI\OpenAPIGenerator;
use Wwwision\TypesOpenAPI\Response\DefaultResponseConverter;
use Wwwision\TypesOpenAPI\Types\HttpMethod;
use Wwwision\TypesOpenAPI\Types\HttpStatusCode;
use Wwwision\TypesOpenAPI\Types\ParameterLocation;
use function Wwwision\Types\instantiate;

#[CoversClass(OpenAPIGenerator::class)]
final class OpenAPIGeneratorTest extends TestCase
{

    private OpenAPIGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new OpenAPIGenerator();
    }

//    public function test_generate_throws_exception_if_specified_class_does_not_exist(): void
//    {
//        $this->expectException(InvalidArgumentException::class);
//        $this->generator->generate('NonExistingClass');
//    }

    public function test_generate(): void
    {
        $schema = $this->generator->generate(PetStoreApi::class);

        $expected = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'todo',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' =>  'http://localhost:8081/'],
            ],
            'paths' => [
                '/pet/findByStatus' => [
                    'get' => [
                        #'description' => 'Operation description',
                        'parameters' => [
                            [
                                'name' => 'status',
                                'in' => 'query',
                                #'description' => 'Parameter description',
                                'required' => false,
                                'schema' => [
                                    'type' => 'string',
                                    'default' => 'available',
                                    'enum' => ['available', 'pending', 'sold'],
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'successful operation',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => [
                                                '$ref' => '#/components/schemas/Pet',
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ],
                ]
            ],
            'components' => [
                'schemas' => [
                    'Pet' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                            ],
                            'name' => [
                                'type' => 'string',
                            ],
                            'tag' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => ['id', 'name'],
                    ]
                ]
            ]
        ];
        self::assertJsonStringEqualsJsonString(json_encode($expected), json_encode($schema));

        echo PHP_EOL;
        echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    }

}

final class Pet {
    private function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $tag = null,
    ) {
    }
}

/**
 * @implements IteratorAggregate<Pet>
 */
#[ListBased(itemClassName: Pet::class)]
final class Pets implements IteratorAggregate
{
    private function __construct(private readonly array $pets) {}

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->pets);
    }
}

enum PetStatus {
    case available;
    case pending;
    case sold;
}

#[Response('foo', responseConverterClass: DefaultResponseConverter::class)]
final class PetStoreApi {

    #[Operation(path: '/pet/findByStatus', method: HttpMethod::GET, description: 'Operation description')]
    #[Response('default response', statusCode: 200, responseConverterClass: DefaultResponseConverter::class)]
    public function petsByStatus(
        PetStatus $status = PetStatus::available,
    ): Pets
    {
        return instantiate(Pets::class, [
            ['id' => 1, 'name' => 'First'],
            ['id' => 2, 'name' => 'Second', 'tags' => ['some-tag']],
            ['id' => 3, 'name' => 'Third', 'tags' => ['some-other-tag']],
        ]);
    }

    public function addPet(Pet $pet): void
    {

    }


}