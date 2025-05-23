<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Response;

use Wwwision\TypesOpenApi\Types\HttpStatusCode;
use Wwwision\TypesOpenApi\Types\MediaTypeRange;

final class ProblemInternalServerErrorResponse implements OpenApiResponse
{
    public static function statusCode(): HttpStatusCode
    {
        return HttpStatusCode::fromInteger(500);
    }

    public static function contentType(): MediaTypeRange
    {
        return MediaTypeRange::fromString('application/problem+json');
    }

    public static function description(): string
    {
        return 'Internal Server Error';
    }

    public function body(): string
    {
        return ProblemResponseBuilder::createBody(
            statusCode: 500,
            reasonPhrase: 'Internal Server Error',
        );
    }
}
