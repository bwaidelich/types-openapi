<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Response;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;

final class DefaultResponseConverter implements ResponseConverter
{
    public static function apply(mixed $methodInvocationResult, ResponseInterface $response): ResponseInterface
    {
        return $response->withBody(Utils::streamFor('Hello world'));
    }
}