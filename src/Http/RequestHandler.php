<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Http;

use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Throwable;
use Wwwision\Types\Exception\CoerceException;
use Wwwision\Types\Normalizer\Normalizer;
use Wwwision\Types\Schema\Schema;
use Wwwision\TypesOpenAPI\Http\Exception\BadRequestException;
use Wwwision\TypesOpenAPI\Http\Exception\MethodNotAllowedException;
use Wwwision\TypesOpenAPI\Http\Exception\NotFoundException;
use Wwwision\TypesOpenAPI\Http\Exception\RequestException;
use Wwwision\TypesOpenAPI\Http\Exception\UnauthorizedException;
use Wwwision\TypesOpenAPI\OpenAPIGenerator;
use Wwwision\TypesOpenAPI\Response\OpenApiResponse;
use Wwwision\TypesOpenAPI\Response\ProblemResponseBuilder;
use Wwwision\TypesOpenAPI\Response\WithAddedHeaders;
use Wwwision\TypesOpenAPI\Security\AuthenticationContextProvider;
use Wwwision\TypesOpenAPI\Types\MediaTypeObject;
use Wwwision\TypesOpenAPI\Types\MediaTypeRange;
use Wwwision\TypesOpenAPI\Types\OpenAPIGeneratorOptions;
use Wwwision\TypesOpenAPI\Types\OpenAPIObject;
use Wwwision\TypesOpenAPI\Types\OperationObject;
use Wwwision\TypesOpenAPI\Types\ParameterLocation;
use Wwwision\TypesOpenAPI\Types\ParameterObject;
use Wwwision\TypesOpenAPI\Types\ParameterOrReferenceObjects;
use Wwwision\TypesOpenAPI\Types\PathObject;
use Wwwision\TypesOpenAPI\Types\RequestBodyObject;

final class RequestHandler
{
    private OpenAPIObject $openApiSchema;

    public function __construct(
        private readonly object $api,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly null|OpenAPIGeneratorOptions $options = null,
        private readonly null|AuthenticationContextProvider $authenticationContextProvider = null,
    ) {
        $this->openApiSchema = (new OpenAPIGenerator())->generate($this->api::class, $this->options ?? OpenAPIGeneratorOptions::create());
    }

    /**
     * @throws RequestException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $pathObject = $this->findPathObjectForUri($request->getUri(), $extractedVariables);
        if ($pathObject === null) {
            throw new NotFoundException();
        }
        $operationObject = $pathObject->{strtolower($request->getMethod())};
        /** @var OperationObject|null $operationObject */
        if ($operationObject === null) {
            throw new MethodNotAllowedException();
        }
        $methodName = $operationObject->meta['methodName'] ?? null;
        if (!is_string($methodName)) {
            throw new RuntimeException(sprintf('Failed to resolve method name for operation "%s"', $operationObject->operationId ?? '?'));
        }
        $authenticationContext = null;
        $securityRequirement = $operationObject->security ?? $this->openApiSchema->security;
        if ($securityRequirement !== null) {
            if ($this->authenticationContextProvider === null) {
                throw new RuntimeException(sprintf('Method "%s" requires authentication, but no AuthenticationContextProvider is configured', $methodName), 1743669473);
            }
            $authenticationContext = $this->authenticationContextProvider->getAuthenticationContext($request, $securityRequirement);
            if ($authenticationContext === null && !$securityRequirement->anonymousAccessAllowed) {
                throw new UnauthorizedException('Missing credentials');
            }
        }
        $parameters = [];
        if ($operationObject->requestBody !== null) {
            if (!$operationObject->requestBody instanceof RequestBodyObject) {
                // TODO support reference request bodies
                throw new RuntimeException('Reference request bodies are not yet supported');
            }
            $parameters[] = $this->parseRequestBody($operationObject->requestBody, $request);
        }
        if ($operationObject->parameters !== null) {
            try {
                $parsedParameters = $this->parseParameters($operationObject->parameters, $request, $extractedVariables);
            } catch (Throwable $e) {
                return $this->badRequestResponseFromException($e, title: 'Bad Query Parameters');
            }
            $parameters = [...$parameters, ...$parsedParameters];
        }
        if ($authenticationContext !== null) {
            $parameters[] = $authenticationContext;
        }

        if (!method_exists($this->api, $methodName)) {
            throw new RuntimeException(sprintf('Method "%s" does not exist in object of type %s', $methodName, $this->api::class));
        }
        $result = $this->api->{$methodName}(...$parameters);
        if ($result instanceof OpenApiResponse) {
            return $this->response($result::statusCode()->value, $result::contentType()?->value, $result->body(), $result instanceof WithAddedHeaders ? $result->getAddedHeaders() : []);
        }
        if (is_object($result)) {
            return $this->response(200, 'application/json', (new Normalizer())->toJson($result));
        }
        if ($result === null || is_string($result)) {
            return $this->response(body: (string) $result);
        }
        throw new RuntimeException(sprintf('The result of method "%s" of type %s is not supported', $methodName, get_debug_type($result)), 1743671737);
    }

    /**
     * @throws BadRequestException
     */
    private function parseRequestBody(RequestBodyObject $requestBodyObject, ServerRequestInterface $request): mixed
    {
        if (!$request->hasHeader('Content-Type')) {
            throw new BadRequestException('Missing Content-Type header');
        }
        try {
            $requestMediaType = MediaTypeRange::fromString($request->getHeaderLine('Content-Type'));
        } catch (InvalidArgumentException $e) {
            throw new BadRequestException(sprintf('Invalid Content-Type header: "%s"', $request->getHeaderLine('Content-Type')), 1742469093, $e);
        }
        $mediaTypeObject = $this->findMatchingMediaTypeObject($requestBodyObject, $requestMediaType);
        if ($mediaTypeObject === null) {
            throw new BadRequestException('No matching media type found for request body');
        }

        if ($requestMediaType->subtype !== 'json') {
            throw new BadRequestException(sprintf('%s currently only supports JSON request bodies, given: "%s"', __CLASS__, $requestMediaType->value));
        }
        try {
            $parsedRequestBody = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new BadRequestException('Failed to parse JSON request body');
        }
        if (isset($mediaTypeObject->meta['schema'])) {
            $mediaTypeSchema = $mediaTypeObject->meta['schema'];
            if (!$mediaTypeSchema instanceof Schema) {
                throw new RuntimeException(sprintf('Media type schema is not a Schema object: %s', get_debug_type($mediaTypeSchema)));
            }
            try {
                $parsedRequestBody = $mediaTypeSchema->instantiate($parsedRequestBody);
            } catch (CoerceException $e) {
                throw new BadRequestException(previous: $e);
            }
        }
        return $parsedRequestBody;
    }

    private function findMatchingMediaTypeObject(RequestBodyObject $requestBodyObject, MediaTypeRange $requestMediaType): MediaTypeObject|null
    {
        $candidate = null;
        foreach ($requestBodyObject->content as $mediaTypeRange => $mediaTypeObject) {
            if (!is_string($mediaTypeRange)) {
                throw new RuntimeException(sprintf('Media type range is not a string: %s', get_debug_type($mediaTypeRange)));
            }
            $mediaTypeRangeVo = MediaTypeRange::fromString($mediaTypeRange);
            if ($mediaTypeRangeVo->matches($requestMediaType)) {
                if ($mediaTypeRangeVo->subtype !== '*') {
                    return $mediaTypeObject;
                }
                $candidate = $mediaTypeObject;
            }
        }
        return $candidate;
    }


    /**
     * @param array<string,string>|null $extractedVariables
     */
    private function findPathObjectForUri(UriInterface $uri, array|null &$extractedVariables = null): PathObject|null
    {
        if ($this->openApiSchema->paths === null) {
            return null;
        }
        foreach ($this->openApiSchema->paths as $uriTemplate => $pathObject) {
            if (!is_string($uriTemplate)) {
                throw new RuntimeException(sprintf('Path template is not a string: %s', get_debug_type($uriTemplate)));
            }
            if ($uriTemplate === $uri->getPath() && !str_contains('{', $uriTemplate)) {
                return $pathObject;
            }
            $pattern = preg_replace('/\{([^\/]+)}/', '(?<$1>([^\/]+))', str_replace('/', '\/', $uriTemplate));
            if (preg_match('/^' . $pattern . '$/i', $uri->getPath(), $matches)) {
                $extractedVariables = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $pathObject;
            }
        }
        return null;
    }



    /**
     * @param array<string, string>|null $extractedVariables
     * @return array<mixed>
     * @throws CoerceException
     */
    private function parseParameters(ParameterOrReferenceObjects $parameterOrReferenceObjects, ServerRequestInterface $request, array|null $extractedVariables): array
    {
        $parameters = [];
        foreach ($parameterOrReferenceObjects as $parameterObject) {
            if (!$parameterObject instanceof ParameterObject) {
                // TODO support reference parameters
                continue;
            }
            $parameterValue = match ($parameterObject->in) {
                ParameterLocation::query => $request->getQueryParams()[$parameterObject->name] ?? null,
                ParameterLocation::path => $extractedVariables[$parameterObject->name] ?? null,
                ParameterLocation::cookie => throw new RuntimeException('ParameterLocation::cookie is not yet supported'), // TODO
                ParameterLocation::header => throw new RuntimeException('ParameterLocation::header is not yet supported'), // TODO
            };
            if (isset($parameterObject->meta['schema'])) {
                $parameterSchema = $parameterObject->meta['schema'];
                if (!$parameterSchema instanceof Schema) {
                    throw new RuntimeException(sprintf('Parameter schema is not a Schema object: %s', get_debug_type($parameterSchema)));
                }
                $parameterValue = $parameterSchema->instantiate($parameterValue);
            }
            $parameters[$parameterObject->name] = $parameterValue;
        }
        return array_values($parameters);
    }


    private function badRequestResponseFromException(Throwable $e, string $title): ResponseInterface
    {
        $detail = '';
        $additionalData = [];
        if ($e instanceof JsonException) {
            $detail = 'Failed to parse JSON request body';
        } elseif ($e instanceof CoerceException) {
            $additionalData['issues'] = $e->issues;
        } else {
            $detail = $e->getMessage();
        }
        return $this->response(
            status: 400,
            contentType: 'application/problem+json',
            body: ProblemResponseBuilder::createBody(statusCode: 400, reasonPhrase: 'Bad Request', detail: $detail, additionalData: $additionalData),
        );
    }

    /**
     * @param array<string, string|array<string>> $addedHeaders
     */
    private function response(int $status = 200, string|null $contentType = null, string|StreamInterface|null $body = null, array $addedHeaders = []): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        if ($contentType !== null) {
            $response = $response->withAddedHeader('Content-Type', $contentType);
        }
        foreach ($addedHeaders as $headerName => $headerValue) {
            $response = $response->withAddedHeader($headerName, $headerValue);
        }
        if ($body instanceof StreamInterface) {
            $response = $response->withBody($body);
        } elseif (is_string($body)) {
            $response = $response->withBody($this->streamFactory->createStream($body));
        }
        return $response;
    }

}
