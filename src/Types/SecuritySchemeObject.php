<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Types;

use JsonSerializable;
use Webmozart\Assert\Assert;

/**
 * @see https://swagger.io/specification/#security-scheme-object
 */
final class SecuritySchemeObject implements JsonSerializable
{
    public function __construct(
        public readonly SecuritySchemeType $type,
        public readonly null|string $description = null,
        public readonly null|string $name = null,
        public readonly null|SecuritySchemeApiKeyLocation $in = null,
        public readonly null|string $scheme = null,
        public readonly null|string $bearerFormat = null,
        public readonly null|OAuthFlowsObject $flows = null,
        public readonly null|string $openIdConnectUrl = null,
    ) {
        if ($type === SecuritySchemeType::apiKey) {
            Assert::notNull($name, '"name" is required for type "apiKey"');
            Assert::notNull($in, '"in" is required for type "apiKey"');
        } else {
            Assert::null($name, '"name" only applies to type "apiKey"');
            Assert::null($in, '"in"  only applies to "apiKey"');
        }
        if ($type === SecuritySchemeType::http) {
            Assert::notNull($scheme, '"scheme" is required for type "http"');
        } else {
            Assert::null($scheme, '"scheme" only applies to type "http"');
            Assert::null($bearerFormat, '"bearerFormat" only applies to type "http"');
        }
        if ($type === SecuritySchemeType::oauth2) {
            Assert::notNull($flows, '"flows" is required for type "oauth2"');
        } else {
            Assert::null($flows, '"flows" only applies to type "oauth2"');
        }
        if ($type === SecuritySchemeType::openIdConnect) {
            Assert::notNull($openIdConnectUrl, '"openIdConnectUrl" is required for type "openIdConnect"');
        } else {
            Assert::null($openIdConnectUrl, '"openIdConnectUrl" only applies to type "openIdConnect"');
        }
    }

    public static function apiKey(string $name, SecuritySchemeApiKeyLocation $in, string|null $description = null): self
    {
        return new self(
            type: SecuritySchemeType::apiKey,
            description: $description,
            name: $name,
            in: $in,
        );
    }

    public static function http(string $scheme, string|null $bearerFormat = null, string|null $description = null): self
    {
        return new self(
            type: SecuritySchemeType::http,
            description: $description,
            scheme: $scheme,
            bearerFormat: $bearerFormat,
        );
    }

    public static function mutualTLS(string|null $description = null): self
    {
        return new self(
            type: SecuritySchemeType::mutualTLS,
            description: $description,
        );
    }

    public static function oauth2(OAuthFlowsObject $flows, string|null $description = null): self
    {
        return new self(
            type: SecuritySchemeType::oauth2,
            description: $description,
            flows: $flows,
        );
    }

    public static function openIdConnect(OAuthFlowsObject $flows, string|null $openIdConnectUrl = null, string|null $description = null): self
    {
        return new self(
            type: SecuritySchemeType::openIdConnect,
            description: $description,
            openIdConnectUrl: $openIdConnectUrl,
        );
    }


    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), static fn($i) => $i !== null);
    }
}
