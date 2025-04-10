<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Response;

use Wwwision\TypesOpenApi\Types\HttpStatusCode;

final class CreatedResponse implements OpenApiResponse
{
    public function __construct(
        private readonly string|null $body = null,
    ) {}

    public static function statusCode(): HttpStatusCode
    {
        return HttpStatusCode::fromInteger(201);
    }

    public static function contentType(): null
    {
        return null;
    }

    public static function description(): string
    {
        return 'Created';
    }

    public function body(): string|null
    {
        return $this->body;
    }
}
