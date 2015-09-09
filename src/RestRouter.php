<?php

namespace Stratify\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Http\Middleware\Middleware;

/**
 * Routes requests in a REST fashion.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class RestRouter implements Middleware
{
    /**
     * @var Router
     */
    private $router;

    public function __construct(array $resources, MiddlewareInvoker $invoker = null)
    {
        $routes = $this->createRoutes($resources);
        $this->router = new Router($routes, $invoker);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) : ResponseInterface
    {
        $router = $this->router;

        return $router($request, $response, $next);
    }

    private function createRoutes(array $resources) : array
    {
        $routes = [];

        foreach ($resources as $path => $controller) {
            $routes[$path] = resource([
                'get'  => [$controller, 'index'],
                'post' => [$controller, 'post'],
            ]);

            $routes[$path . '/{id}'] = resource([
                'get'    => [$controller, 'get'],
                'put'    => [$controller, 'put'],
                'delete' => [$controller, 'delete'],
            ]);
        }

        return $routes;
    }
}
