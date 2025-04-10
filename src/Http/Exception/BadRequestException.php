<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Http\Exception;

use Exception;

final class BadRequestException extends Exception implements RequestException
{
    public static function getStatusCode(): int
    {
        return 400;
    }

    public static function getReasonPhrase(): string
    {
        return 'Bad Request';
    }

}
