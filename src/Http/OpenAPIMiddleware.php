<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Http;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class OpenAPIMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = [
            ['id' => 1, 'name' => 'first'],
            ['id' => 2, 'name' => 'second', 'tag' => 'some-tag'],
            ['id' => 3, 'name' => 'third'],
        ];
        return new Response(headers: ['Content-Type' => 'application/json'], body: json_encode($body));
    }
}