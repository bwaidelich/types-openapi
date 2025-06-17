<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Tests\PHPUnit\Fixture;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Attributes\Discriminator;
use Wwwision\Types\Attributes\ListBased;
use Wwwision\Types\Attributes\StringBased;
use Wwwision\Types\Schema\StringTypeFormat;
use Wwwision\TypesOpenApi\Attributes\OpenApi;
use Wwwision\TypesOpenApi\Attributes\Operation;
use Wwwision\TypesOpenApi\Attributes\Parameter;
use Wwwision\TypesOpenApi\Response\NotFoundResponse;
use Wwwision\TypesOpenApi\Types\HttpMethod;
use Wwwision\TypesOpenApi\Types\ParameterLocation;

use function Wwwision\Types\instantiate;

final class Pet
{
    private function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly null|string $tag = null,
    ) {}
}

/**
 * @implements IteratorAggregate<Pet>
 */
#[ListBased(itemClassName: Pet::class, minCount: 1, maxCount: 10)]
final class Pets implements IteratorAggregate
{
    /**
     * @param array<Pet> $pets
     */
    private function __construct(private readonly array $pets) {}

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->pets);
    }
}

enum PetStatus
{
    case available;
    case pending;
    case sold;
}

#[OpenApi(apiTitle: 'Pet Store API', apiVersion: '1.0.0')]
final class PetStoreApi
{
    private Pets $pets;

    public function __construct()
    {
        $this->pets = instantiate(Pets::class, [
            ['id' => 1, 'name' => 'First'],
            ['id' => 2, 'name' => 'Second', 'tags' => ['some-tag']],
            ['id' => 3, 'name' => 'Third', 'tags' => ['some-other-tag']],
        ]);
    }

    #[Operation(path: '/pet/findByStatus', method: HttpMethod::GET)]
    #[Description('Operation description')]
    public function pets(): Pets
    {
        return $this->pets;
    }

    #[Operation(path: '/pet/{id}', method: HttpMethod::GET)]
    public function petById(int $id): Pet|NotFoundResponse
    {
        foreach ($this->pets as $pet) {
            if ($pet->id === $id) {
                return $pet;
            }
        }
        return new NotFoundResponse();
    }

}

final class AnotherApi
{
    #[Operation(path: '/some-interface', method: HttpMethod::PATCH, summary: 'Operation summary')]
    #[Description('Operation description')]
    public function someInterface(): SomeInterface
    {
        throw new InvalidArgumentException('Not implemented');
    }
}

final class ApiWithConcreteAndTemplatedPathsOverlap
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::GET)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/mine', method: HttpMethod::GET)]
    public function mine(): string
    {
        return 'mine';
    }
}

final class ApiWithTheSamePathsAndMethods
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::POST)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/{pet}', method: HttpMethod::POST)]
    public function foo(string $foo): string
    {
        return 'mine';
    }
}

final class ApiWithTheSamePathsButDifferentMethods
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::GET)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/{pet}', method: HttpMethod::POST)]
    public function foo(string $foo): string
    {
        return 'mine';
    }
}


final class ApiWithTheSamePathStructureAndMethods
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::GET)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/{foo}', method: HttpMethod::GET)]
    public function foo(string $foo): string
    {
        return 'mine';
    }
}

final class ApiWithTheSamePathStructureButDifferentMethods
{
    #[Operation(path: '/pets/{pet}', method: HttpMethod::GET)]
    public function pet(string $pet): string
    {
        return 'pet';
    }

    #[Operation(path: '/pets/{foo}', method: HttpMethod::POST)]
    public function foo(string $foo): string
    {
        return 'mine';
    }
}

final class ApiWithParameters
{
    #[Operation(path: '/required-params', method: HttpMethod::GET)]
    public function requiredParams(
        #[Parameter(in: ParameterLocation::query, name: 'query-param')]
        string $param1,
        #[Parameter(in: ParameterLocation::header, name: 'X-Header-Param')]
        int $param2,
        #[Parameter(in: ParameterLocation::cookie, name: 'Cookie-Param')]
        bool $param3,
    ): void {}

    #[Operation(path: '/optional-params', method: HttpMethod::GET)]
    public function optionalParams(
        #[Parameter(in: ParameterLocation::query, name: 'query-param')]
        string $param1 = 'default',
        #[Parameter(in: ParameterLocation::header, name: 'X-Header-Param')]
        int $param2 = 123,
        #[Parameter(in: ParameterLocation::cookie, name: 'Cookie-Param')]
        bool $param3 = true,
    ): void {}
}

#[Description('SomeInterface description')]
#[Discriminator(propertyName: 'type', mapping: ['a' => ImplementationA::class, 'b' => ImplementationB::class])]
interface SomeInterface
{
    #[Description('Custom description for "someMethod"')]
    public function someMethod(): string;
}

final class ImplementationA implements SomeInterface
{
    private function __construct(
        public readonly string $someString,
        public readonly EmailAddress $emailAddress,
    ) {}

    public function someMethod(): string
    {
        return 'A';
    }
}

final class ImplementationB implements SomeInterface
{
    private function __construct(
        public readonly bool $someBoolean,
        public readonly EmailAddress $emailAddress,
    ) {}

    public function someMethod(): string
    {
        return 'B';
    }
}

#[StringBased(format: StringTypeFormat::email)]
final class EmailAddress
{
    private function __construct(
        public readonly string $value,
    ) {}
}
