<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Attributes;

use Attribute;
use Wwwision\TypesOpenAPI\Response\DefaultResponseConverter;
use Wwwision\TypesOpenAPI\Response\ResponseConverter;
use Wwwision\TypesOpenAPI\Types\HttpStatusCode;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Response
{
    /**
     * @param class-string<ResponseConverter> $responseConverterClass
     */
    public function __construct(
        public readonly string $description,
        public readonly HttpStatusCode|int $statusCode = 200,
        public readonly string $responseConverterClass = DefaultResponseConverter::class,
    ) {
    }
}
