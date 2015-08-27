<?php

namespace Stratify\Router\Route;

use Aura\Router\Route;

/**
 * Handles routes defined in an array using helper functions.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class RouteArray implements RouteProvider
{
    /**
     * @var RouteBuilder[]
     */
    private $routes;

    /**
     * @param RouteBuilder[] $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function getRoutes() : array
    {
        $routes = [];

        foreach ($this->routes as $path => $route) {
            if ($route instanceof RouteBuilder) {
                $routes[] = $route->getRoute($path);
            } else {
                $controller = $route;

                $route = new Route();
                $route->path($path);
                $route->handler($controller);

                $routes[] = $route;
            }
        }

        return $routes;
    }
}
