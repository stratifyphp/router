<?php

namespace Stratify\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

    public function __construct(array $resources, ContainerInterface $container = null)
    {
        $routes = $this->createRoutes($resources);
        $this->router = new Router($routes, $container);
    }

    public function __invoke(ServerRequestInterface $request, callable $next) : ResponseInterface
    {
        return ($this->router)($request, $next);
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
