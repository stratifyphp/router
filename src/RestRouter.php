<?php

namespace Stratify\Router;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Routes requests in a REST fashion.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class RestRouter implements MiddlewareInterface
{
    /**
     * @var Router
     */
    private $router;

    public function __construct(array $resources, ContainerInterface $container = null)
    {
        $routes = $this->createRoutes($resources);
        $this->router = new Router($routes, $container);
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        return $this->router->process($request, $delegate);
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
