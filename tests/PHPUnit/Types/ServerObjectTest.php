<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Tests\PHPUnit\Types;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesOpenAPI\Types\ServerObject;
use Wwwision\TypesOpenAPI\Types\ServerVariableObject;
use Wwwision\TypesOpenAPI\Types\ServerVariableObjects;

#[CoversClass(ServerObject::class)]
final class ServerObjectTest extends TestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function invalid_matches_provider(): iterable
    {
        yield 'invalid path1' => ['serverUrl' => '/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost'];
        yield 'invalid path2' => ['serverUrl' => '/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost/api/v2/foo'];
        yield 'invalid schema1' => ['serverUrl' => 'https://localhost/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost'];
        yield 'invalid schema2' => ['serverUrl' => 'https://localhost/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost/api/v3/foo'];
        yield 'invalid schema3' => ['serverUrl' => 'https://localhost/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost/api/v2/foo'];
        yield 'invalid schema4' => ['serverUrl' => 'https://localhost/api/v3', 'variables' => [], 'requestUrl' => 'http://some-domain.tld/api/v3/foos/bar'];
        yield 'invalid schema5' => ['serverUrl' => 'http://localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'https://bar.localhost:1234/api/v3/foo'];
        yield 'invalid host1' => ['serverUrl' => 'https://localhost/api/v3', 'variables' => [], 'requestUrl' => 'https://bar.localhost:1234/api/v3/foo'];
        yield 'invalid host2' => ['serverUrl' => 'http://localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'http://some-domain.tld/api/v3/foos/bar'];
        yield 'invalid host3' => ['serverUrl' => '//localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'https://bar.localhost:1234/api/v3/foo'];
        yield 'invalid host4' => ['serverUrl' => '//localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'http://some-domain.tld/api/v3/foos/bar'];
        yield 'invalid host5' => ['serverUrl' => '//{user}.localhost:{port}/api/v3', 'variables' => ['user' => 'demo', 'port' => '1234'], 'requestUrl' => 'http://localhost'];
        yield 'invalid host6' => ['serverUrl' => '//{user}.localhost:{port}/api/v3', 'variables' => ['user' => 'demo', 'port' => '1234'], 'requestUrl' => 'http://localhost/api/v3/foo'];
        yield 'invalid host7' => ['serverUrl' => '//{user}.localhost:{port}/api/v3', 'variables' => ['user' => 'demo', 'port' => '1234'], 'requestUrl' => 'http://localhost/api/v2/foo'];
        yield 'invalid host8' => ['serverUrl' => '//{user}.localhost:{port}/api/v3', 'variables' => ['user' => 'demo', 'port' => '1234'], 'requestUrl' => 'http://some-domain.tld/api/v3/foos/bar'];
        yield 'invalid host9' => ['serverUrl' => '//{user}.localhost:{port}/api/v3', 'variables' => ['user' => 'demo', 'port' => '1234'], 'requestUrl' => 'https://demos.localhost:1234/api/v3'];
        yield 'invalid port1' => ['serverUrl' => 'http://localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost'];
        yield 'invalid port2' => ['serverUrl' => 'http://localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost/api/v3/foo'];
        yield 'invalid port3' => ['serverUrl' => 'http://localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost/api/v2/foo'];
        yield 'invalid port4' => ['serverUrl' => '//localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost'];
        yield 'invalid port5' => ['serverUrl' => '//localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost/api/v3/foo'];
        yield 'invalid port6' => ['serverUrl' => '//localhost:1234/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost/api/v2/foo'];
    }

    /**
     * @param array<mixed> $variables
     */
    #[DataProvider('invalid_matches_provider')]
    public function test_invalid_matches(string $serverUrl, array $variables, string $requestUrl): void
    {
        $variablesObjects = [];
        foreach ($variables as $name => $default) {
            self::assertIsString($default);
            $variablesObjects[$name] = new ServerVariableObject($default);
        }
        $serverObject = new ServerObject(url: $serverUrl, variables: new ServerVariableObjects(...$variablesObjects));
        self::assertFalse($serverObject->matches(new Uri($requestUrl)));
    }

    /**
     * @return iterable<mixed>
     */
    public static function valid_matches_provider(): iterable
    {
        yield ['serverUrl' => '/api/v3', 'variables' => [], 'requestUrl' => 'http://localhost/api/v3/foo', 'expectedRemainingPath' => '/foo'];
        yield ['serverUrl' => '/api/v3', 'variables' => [], 'requestUrl' => 'https://bar.localhost:1234/api/v3/foo', 'expectedRemainingPath' => '/foo'];
        yield ['serverUrl' => '/api/v3', 'variables' => [], 'requestUrl' => 'http://some-domain.tld/api/v3/foos/bar', 'expectedRemainingPath' => '/foos/bar'];
        yield ['serverUrl' => '//{user}.localhost:{port}/api/v3', 'variables' => ['user' => 'demo', 'port' => '1234'], 'requestUrl' => 'https://demo.localhost:1234/api/v3/foo', 'expectedRemainingPath' => '/foo'];
    }

    /**
     * @param array<mixed> $variables
     */
    #[DataProvider('valid_matches_provider')]
    public function test_valid_matches(string $serverUrl, array $variables, string $requestUrl, string $expectedRemainingPath): void
    {
        $variablesObjects = [];
        foreach ($variables as $name => $default) {
            self::assertIsString($default);
            $variablesObjects[$name] = new ServerVariableObject($default);
        }
        $serverObject = new ServerObject(url: $serverUrl, variables: new ServerVariableObjects(...$variablesObjects));
        self::assertSame($expectedRemainingPath, $serverObject->matches(new Uri($requestUrl)));
    }
}
