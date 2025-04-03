<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Http\Exception;

use Throwable;

interface RequestException extends Throwable
{
    public static function getStatusCode(): int;
    public static function getReasonPhrase(): string;

}
