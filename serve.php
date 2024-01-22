<?php
declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wwwision\TypesOpenAPI\Http\OpenAPIMiddleware;

require __DIR__ . '/vendor/autoload.php';


final class MiddlewaresChain implements RequestHandlerInterface
{
    /**
     * @param array<MiddlewareInterface> $middlewares
     */
    private function __construct(
        private array $middlewares,
    )
    {
    }

    public static function create(MiddlewareInterface ...$middlewares): self
    {
        return new self($middlewares);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->middlewares === []) {
            return new Response();
        }
        /** @var MiddlewareInterface $middleware */
        $middleware = array_shift($this->middlewares);
        return $middleware->process($request, $this);
    }
}


$serverRequest = ServerRequest::fromGlobals();
$chain = MiddlewaresChain::create(
    new OpenAPIMiddleware(),
);
/** @var Response $response */
$response = $chain->handle($serverRequest);
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
echo $response->getBody()->getContents();