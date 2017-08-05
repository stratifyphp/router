<?php

namespace Stratify\Router;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Http\Middleware\Invoker\SimpleInvoker;

/**
 * Routes requests by matching prefixes on the URI.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class PrefixRouter implements MiddlewareInterface
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

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $path = $request->getUri()->getPath();

        foreach ($this->routes as $pathPrefix => $middleware) {
            if (strpos($path, $pathPrefix) === 0) {
                return $this->invoker->invoke($middleware, $request, $delegate);
            }
        }

        return $delegate->process($request);
    }
}
