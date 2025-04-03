<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Response;

use Psr\Http\Message\StreamInterface;
use Wwwision\TypesOpenAPI\Types\HttpStatusCode;
use Wwwision\TypesOpenAPI\Types\MediaTypeRange;

interface OpenApiResponse
{
    public static function statusCode(): HttpStatusCode;

    public static function description(): string;

    public static function contentType(): MediaTypeRange|null;

    public function body(): string|StreamInterface|null;
}
