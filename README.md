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

$openApiObject = (new OpenApiGenerator())->generate(SomeApi::class);

assert($openApiObject instanceof OpenApiObject);
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

### Parameters

Arguments of the endpoint methods are automatically mapped to OpenAPI parameters.
All OpenAPI parameter types are supported (`query`, `path`, `header`, `cookie`).

#### Query parameters

By default, the parameter type is `query`:

```php
final class SomeApi {

    #[Operation(path: '/', method: 'GET')]
    public function someEndpoint(string $someParam, string|null $someOptionalParam = null): string {
        return $someParam;
    }
}
```

will accept requests like

```http request
GET /?someParam=foo HTTP/1.1
```
and
```http request
GET /?someParam=foo&someOptionalParam=bar HTTP/1.1
```

and will map the values to the corresponding method arguments.

#### Path parameters

Operations can also make use of [Path Templating](https://swagger.io/specification/#path-templating) in order to map method arguments from the query path:

```php
final class SomeApi {

    #[Operation(path: '/static/{param1}/{param2}', method: 'GET')]
    public function someEndpoint(string $param1, string $param2): string {
        // ...
    }
}
```

Path params cannot be optional and must be defined in the path template.

#### Header parameters

To define a header parameter, the `#[Parameter]` attribute can be used:

```php
final class SomeApi {

    #[Operation(path: '/', method: 'GET')]
    public function someEndpoint(#[Parameter(in: ParameterLocation::header, name: "X-HeaderName")] string $paramFromHeader): string {
        // ...
    }
}
```

#### Cookie parameters

Likewise, to define a cookie parameter, the `#[Parameter]` attribute can be used:

```php
final class SomeApi {

    #[Operation(path: '/', method: 'GET')]
    public function someEndpoint(#[Parameter(in: ParameterLocation::cookie, name: "CookieName")] string $paramFromCookie): string {
        // ...
    }
}
```

#### Complex types

Complex parameter types are supported as well as long as they follow the [wwwision/types best practices](https://github.com/bwaidelich/types?tab=readme-ov-file#best-practices):

```php
#[StringBased(minLength: 3)]
final readonly class Username {
    private function __construct(
        public string $value,
    ) {}
}

final class SomeApi {

    #[Operation(path: '/', method: 'GET')]
    public function someEndpoint(Username $username): string {
        return $username->value;
    }
}
```

This will validate and map the parameter and fail if it does not satisfy the constraints:

```json
{
  "type": "https://www.rfc-editor.org/rfc/rfc9110#name-400-bad-request",
  "title": "Bad Request",
  "issues": [
    {
      "code": "too_small",
      "message": "String must contain at least 3 character(s)",
      "path": [
        "query.username"
      ],
      "type": "string",
      "minimum": 3,
      "inclusive": true,
      "exact": false
    }
  ]
}
```

#### Example

The following example makes use of all parameter types:

```php
final class SomeApi {

    #[Operation(path: '/{paramFromPath}', method: 'GET')]
    public function someEndpoint(
        string $paramFromPath,
        #[Parameter(in: ParameterLocation::header, name: "X-Foo")]
        string $paramFromHeader,
        #[Parameter(in: ParameterLocation::cookie, name: "SomeCookie")]
        string $paramFromCookie,
        string $paramFromQuery,
    ): string {
        return json_encode(func_get_args());
    }
}
```

This will lead to an OpenAPI definition like this:

```json
{
  // ...
  "paths": {
    "/{paramFromPath}": {
      "get": {
        "operationId": "someEndpoint",
        "parameters": [
          {
            "name": "paramFromPath",
            "in": "path",
            "required": true,
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "X-Foo",
            "in": "header",
            "required": true,
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "SomeCookie",
            "in": "cookie",
            "required": true,
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "paramFromQuery",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        // ...
      }
    }
  }
}
```

And an HTTP request like:

```http request
GET /valueFromPath?paramFromQuery=valueFromQuery HTTP/1.1
Host: localhost:8000
X-Foo: valueFromHeader
Cookie: SomeCookie=valueFromCookie
```

...will result in the following response:

```json
["valueFromPath","valueFromHeader","valueFromCookie","valueFromQuery"]
```

### More Examples

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

    #[Operation(path: '/users', method: 'GET', summary: 'Get Users')]
    #[Description('Retrieves all users from the repository')]
    public function users(): Users
    {
        return $this->userRepository->findAll();
    }

    #[Operation(path: '/users/{username}', method: 'GET', summary: 'Get a single user by its username')]
    #[Description('Retrieves a single user or returns a 404 response if not found')]
    public function userByUsername(Username $username): User|NotFoundResponse
    {
        return $this->userRepository->findByUsername($username) ?: new NotFoundResponse();
    }

    #[Operation(path: '/users', method: 'POST', summary: 'Add a new user')]
    #[Description('Saves a new user to the repository')]
    public function addUser(AddUser $command): CreatedResponse
    {
        $this->userRepository->add(new User($command->username, $command->emailAddress));
        return new CreatedResponse();
    }
}

$generator = new OpenApiGenerator();
$openApiObject = $generator->generate(SomeApi::class, OpenApiGeneratorOptions::create());
assert($openApiObject instanceof OpenApiObject);
$expectedSchema = <<<'JSON'
{"openapi":"3.0.3","info":{"title":"Some API","version":"1.2.3"},"paths":{"\/users":{"get":{"summary":"Get Users","description":"Retrieves all users from the repository","operationId":"users","responses":{"200":{"description":"Default","content":{"application\/json":{"schema":{"$ref":"#\/components\/schemas\/Users"}}}}}},"post":{"summary":"Add a new user","description":"Saves a new user to the repository","operationId":"addUser","requestBody":{"content":{"application\/json":{"schema":{"$ref":"#\/components\/schemas\/AddUser"}}},"required":true},"responses":{"201":{"description":"Created"},"400":{"description":"Bad Request"}}}},"\/users\/{username}":{"get":{"summary":"Get a single user by its username","description":"Retrieves a single user or returns a 404 response if not found","operationId":"userByUsername","parameters":[{"name":"username","in":"path","required":true,"schema":{"$ref":"#\/components\/schemas\/Username"}}],"responses":{"200":{"description":"Default","content":{"application\/json":{"schema":{"$ref":"#\/components\/schemas\/User"}}}},"400":{"description":"Bad Request"},"404":{"description":"Not Found"}}}}},"components":{"schemas":{"Username":{"type":"string","description":"Unique handle for a user in the API","minLength":1,"maxLength":200},"EmailAddress":{"type":"string","description":"Email address of a user","format":"email"},"User":{"type":"object","properties":{"username":{"$ref":"#\/components\/schemas\/Username"},"emailAddress":{"$ref":"#\/components\/schemas\/EmailAddress"}},"additionalProperties":false,"required":["username","emailAddress"]},"Users":{"type":"array","description":"A set of users","items":{"$ref":"#\/components\/schemas\/User"}},"AddUser":{"type":"object","properties":{"username":{"$ref":"#\/components\/schemas\/Username"},"emailAddress":{"$ref":"#\/components\/schemas\/EmailAddress"}},"additionalProperties":false,"required":["username","emailAddress"]}},"securitySchemes":{"basicAuth":{"type":"http","description":"Basic authentication","scheme":"basic"}}}}
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