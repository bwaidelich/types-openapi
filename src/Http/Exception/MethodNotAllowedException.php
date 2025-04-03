<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Http\Exception;

use Exception;

final class MethodNotAllowedException extends Exception implements RequestException
{
    public static function getStatusCode(): int
    {
        return 405;
    }

    public static function getReasonPhrase(): string
    {
        return 'Method Not Allowed';
    }
}
