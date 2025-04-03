<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Http\Exception;

use Exception;

final class NotFoundException extends Exception implements RequestException
{
    public static function getStatusCode(): int
    {
        return 404;
    }

    public static function getReasonPhrase(): string
    {
        return 'Not Found';
    }
}
