<?php

namespace Stratify\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\HasSubMiddlewares;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Http\Middleware\Invoker\SimpleInvoker;
use Stratify\Http\Middleware\Middleware;

/**
 * Routes requests by matching prefixes on the URI.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class PrefixRouter implements Middleware, HasSubMiddlewares
{
    /**
     * @var array[]
     */
    private $routes;

    /**
     * @var MiddlewareInvoker
     */
    private $invoker;

    public function __construct(array $routes, MiddlewareInvoker $invoker = null)
    {
        $this->invoker = $invoker ?: new SimpleInvoker();
        $this->routes = $routes;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) : ResponseInterface
    {
        $path = $request->getUri()->getPath();

        foreach ($this->routes as $pathPrefix => $middleware) {
            if (substr($path, 0, strlen($pathPrefix)) === $pathPrefix) {
                return $this->invoker->invoke($middleware, $request, $response, $next);
            }
        }

        return $next($request, $response);
    }

    public function getSubMiddlewares() : array
    {
        return array_values($this->routes);
    }
}
