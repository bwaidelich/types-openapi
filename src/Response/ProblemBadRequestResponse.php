<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Response;

use Wwwision\Types\Exception\CoerceException;
use Wwwision\TypesOpenAPI\Types\HttpStatusCode;
use Wwwision\TypesOpenAPI\Types\MediaTypeRange;

final class ProblemBadRequestResponse implements OpenApiResponse
{
    public function __construct(
        private readonly CoerceException $exception,
    ) {}

    public static function statusCode(): HttpStatusCode
    {
        return HttpStatusCode::fromInteger(400);
    }

    public static function contentType(): MediaTypeRange
    {
        return MediaTypeRange::fromString('application/problem+json');
    }

    public static function description(): string
    {
        return 'Bad Request';
    }

    public function body(): string
    {
        return ProblemResponseBuilder::createBody(
            statusCode: 400,
            reasonPhrase: 'Bad Request',
            additionalData: ['issues' => $this->exception->issues],
        );
    }
}
