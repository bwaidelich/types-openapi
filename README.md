# The easiest way to generate type-safe APIs with PHP...

...possibly... ;)

Integration for the [wwwision/types](https://github.com/bwaidelich/types) package that allows for generation of [OpenAPI](https://www.openapis.org/) schemas and APIs from PHP code

## Usage

This package can be installed via [composer](https://getcomposer.org):

```bash
composer require wwwision/types-openapi
```

### Simple Example

This is all that is required to generate an OpenAPI schema for a simple HTTP endpoint:

```php
final class SomeApi {

    #[Operation(path: '/', method: 'GET')]
    public function someEndpoint(): string {
        return '{"success":true}';
    }
}

$generator = new OpenAPIGenerator();
$openApiObject = $generator->generate(SomeApi::class, OpenAPIGeneratorOptions::create());

assert($openApiObject instanceof OpenAPIObject);
$expectedSchema = <<<JSON
{"openapi":"3.0.3","info":{"title":"","version":"0.0.0"},"paths":{"\/":{"get":{"operationId":"someEndpoint","responses":{"200":{"description":"Default","content":{"application\/json":{"schema":{"type":"string"}}}}}}}}}
JSON;
assert(json_encode($openApiObject) === $expectedSchema);
```

### Serve HTTP Requests

This package comes with a `RequestHandler` that allows for serving HTTP requests using the generated OpenAPI schema.
The `RequestHandler` is [PSR-7](https://www.php-fig.org/psr/psr-7/) compatible such that it can easily be integrated with a corresponding `psr/http-factory`/`psr/http-message` provider, e.g. `guzzlehttp/psr7`:

```php
// ... 

$api = new SomeApi();
$httpFactory = new HttpFactory();
$requestHandler = new RequestHandler($api, $httpFactory, $httpFactory);

$request = ServerRequest::fromGlobals();
try {
    $response = $requestHandler($request);
} catch (RequestException $e) {
    $response = $httpFactory->createResponse($e::getStatusCode(), $e::getReasonPhrase());
    $response->getBody()->write($e->getMessage());
}
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $k => $values) {
    foreach ($values as $v) {
        header(sprintf('%s: %s', $k, $v), false);
    }
}
echo $response->getBody();
```


<details>
<summary><b>More complex example</b></summary>

```php
#[Description('Unique handle for a user in the API')]
#[StringBased(minLength: 1, maxLength: 200)]
final class Username {
    private function __construct(
        public readonly string $value,
    ) {
    }

    public static function fromString(string $value): self {
        return instantiate(self::class, $value);
    }
}

#[Description('Email address of a user')]
#[StringBased(format: StringTypeFormat::email)]
final class EmailAddress {
    private function __construct(
        public readonly string $value,
    ) {
    }

    public static function fromString(string $value): self {
        return instantiate(self::class, $value);
    }
}

final class User {

    public function __construct(
        public readonly Username $username,
        public readonly EmailAddress $emailAddress,
    ) {
    }
}

/**
 * @implements IteratorAggregate<User>
 */
#[Description('A set of users')]
#[ListBased(itemClassName: User::class)]
final class Users implements IteratorAggregate
{
    /**
     * @param array<User> $users
     */
    private function __construct(private readonly array $users) {
    }

    public static function fromArray(array $users): self {
        return instantiate(self::class, $users);
    }

    public function getIterator(): Traversable {
        yield from $this->users;
    }
}

final class AddUser {
    public function __construct(
        public readonly Username $username,
        public readonly EmailAddress $emailAddress,
    ) {
    }
}

interface UserRepository {
    public function findAll(): Users;
    public function findByUsername(Username $username): User|null;
    public function add(User $user): void;
}

#[OpenApi(apiTitle: 'Some API', apiVersion: '1.2.3', openApiVersion: '3.0.3', securitySchemes: ['basicAuth' => ['type' => 'http', 'scheme' => 'basic', 'description' => 'Basic authentication']])]
final class SomeApi
{

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Operation(path: '/users', method: 'GET')]
    public function users(): Users
    {
        return $this->userRepository->findAll();
    }

    #[Operation(path: '/users/{username}', method: 'GET')]
    public function userByUsername(Username $username): User|NotFoundResponse
    {
        return $this->userRepository->findByUsername($username) ?: new NotFoundResponse();
    }

    #[Operation(path: '/users', method: 'POST')]
    public function addUser(AddUser $command): CreatedResponse
    {
        $this->userRepository->add(new User($command->username, $command->emailAddress));
        return new CreatedResponse();
    }
}

$generator = new OpenAPIGenerator();
$openApiObject = $generator->generate(SomeApi::class, OpenAPIGeneratorOptions::create());
assert($openApiObject instanceof OpenAPIObject);
$expectedSchema = <<<'JSON'
{"openapi":"3.0.3","info":{"title":"Some API","version":"1.2.3"},"paths":{"\/users":{"get":{"operationId":"users","responses":{"200":{"description":"Default","content":{"application\/json":{"schema":{"$ref":"#\/components\/schemas\/Users"}}}}}},"post":{"operationId":"addUser","requestBody":{"content":{"application\/json":{"schema":{"$ref":"#\/components\/schemas\/AddUser"}}},"required":true},"responses":{"201":{"description":"Created"},"400":{"description":"Bad Request"}}}},"\/users\/{username}":{"get":{"operationId":"userByUsername","parameters":[{"name":"username","in":"path","required":true,"schema":{"$ref":"#\/components\/schemas\/Username"}}],"responses":{"200":{"description":"Default","content":{"application\/json":{"schema":{"$ref":"#\/components\/schemas\/User"}}}},"400":{"description":"Bad Request"},"404":{"description":"Not Found"}}}}},"components":{"schemas":{"Username":{"type":"string","description":"Unique handle for a user in the API","minLength":1,"maxLength":200},"EmailAddress":{"type":"string","description":"Email address of a user","format":"email"},"User":{"type":"object","properties":{"username":{"$ref":"#\/components\/schemas\/Username"},"emailAddress":{"$ref":"#\/components\/schemas\/EmailAddress"}},"additionalProperties":false,"required":["username","emailAddress"]},"Users":{"type":"array","description":"A set of users","items":{"$ref":"#\/components\/schemas\/User"}},"AddUser":{"type":"object","properties":{"username":{"$ref":"#\/components\/schemas\/Username"},"emailAddress":{"$ref":"#\/components\/schemas\/EmailAddress"}},"additionalProperties":false,"required":["username","emailAddress"]}},"securitySchemes":{"basicAuth":{"type":"http","description":"Basic authentication","scheme":"basic"}}}}
JSON;
assert(json_encode($openApiObject) === $expectedSchema);
```

```php
// ...
final class FakeUserRepository implements UserRepository {

    /**
     * @var array<string, User>
     */
    private array $usersByUsername;

    public function __construct()
    {
        $this->usersByUsername = [
            'john.doe' => new User(Username::fromString('john.doe'), EmailAddress::fromString('john.doe@example.com')),
            'jane.doe' => new User(Username::fromString('jane.doe'), EmailAddress::fromString('jane.doe@example.com')),
        ];
    }

    public function findAll(): Users {
        return Users::fromArray(array_values($this->usersByUsername));
    }

    public function findByUsername(Username $username): User|null
    {
        return $this->usersByUsername[$username->value] ?? null;
    }

    public function add(User $user): void
    {
        $this->usersByUsername[$user->username->value] = $user;
    }
}

$api = new SomeApi(new FakeUserRepository());
$httpFactory = new HttpFactory();
$requestHandler = new RequestHandler($api, $httpFactory, $httpFactory);

$request = ServerRequest::fromGlobals();
try {
    $response = $requestHandler($request);
} catch (RequestException $e) {
    $response = $httpFactory->createResponse($e::getStatusCode(), $e::getReasonPhrase());
    $response->getBody()->write($e->getMessage());
}
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $k => $values) {
    foreach ($values as $v) {
        header(sprintf('%s: %s', $k, $v), false);
    }
}
echo $response->getBody();
```

</details>

## Contribution

Contributions in the form of [issues](https://github.com/bwaidelich/types-openapi/issues) or [pull requests](https://github.com/bwaidelich/types-openapi/pulls) are highly appreciated

## License

See [LICENSE](./LICENSE)