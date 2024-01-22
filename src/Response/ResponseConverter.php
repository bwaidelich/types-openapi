<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Response;

use Psr\Http\Message\ResponseInterface;

interface ResponseConverter
{
    public static function apply(mixed $methodInvocationResult, ResponseInterface $response): ResponseInterface;
}
