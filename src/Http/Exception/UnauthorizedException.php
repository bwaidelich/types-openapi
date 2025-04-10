<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Http\Exception;

use Exception;

final class UnauthorizedException extends Exception implements RequestException
{
    public static function getStatusCode(): int
    {
        return 401;
    }

    public static function getReasonPhrase(): string
    {
        return 'Unauthorized';
    }
}
