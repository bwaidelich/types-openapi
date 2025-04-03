<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Response;

use Wwwision\TypesOpenAPI\Types\HttpStatusCode;

final class NotFoundResponse implements OpenApiResponse
{
    public function __construct(
        private readonly string|null $body = null,
    ) {}

    public static function statusCode(): HttpStatusCode
    {
        return HttpStatusCode::fromInteger(404);
    }

    public static function contentType(): null
    {
        return null;
    }

    public static function description(): string
    {
        return 'Not Found';
    }

    public function body(): string|null
    {
        return $this->body;
    }
}
