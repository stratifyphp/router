<?php

namespace Stratify\Router\Route;

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

        foreach ($this->routes as $path => $routeBuilder) {
            $routes[] = $routeBuilder->getRoute($path);
        }

        return $routes;
    }
}
