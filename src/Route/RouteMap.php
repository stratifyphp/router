<?php

namespace Stratify\Router\Route;

/**
 * Offers a user-friendly interface to define routes dynamically.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class RouteMap implements RouteProvider
{
    /**
     * @var RouteBuilder[]
     */
    private $routeBuilders = [];

    /**
     * @var bool
     */
    private $frozen = false;

    /**
     * Add a GET route.
     */
    public function get(string $path, $controller) : RouteBuilder
    {
        return $this->addRoute($path, ['GET'], $controller);
    }

    /**
     * Add a DELETE route.
     */
    public function delete(string $path, $controller) : RouteBuilder
    {
        return $this->addRoute($path, ['DELETE'], $controller);
    }

    /**
     * Add a HEAD route.
     */
    public function head(string $path, $controller) : RouteBuilder
    {
        return $this->addRoute($path, ['HEAD'], $controller);
    }

    /**
     * Add an OPTIONS route.
     */
    public function options(string $path, $controller) : RouteBuilder
    {
        return $this->addRoute($path, ['OPTIONS'], $controller);
    }

    /**
     * Add a PATCH route.
     */
    public function patch(string $path, $controller) : RouteBuilder
    {
        return $this->addRoute($path, ['PATCH'], $controller);
    }

    /**
     * Add a POST route.
     */
    public function post(string $path, $controller) : RouteBuilder
    {
        return $this->addRoute($path, ['POST'], $controller);
    }

    /**
     * Add a PUT route.
     */
    public function put(string $path, $controller) : RouteBuilder
    {
        return $this->addRoute($path, ['PUT'], $controller);
    }

    private function addRoute(string $path, array $httpMethods, $controller) : RouteBuilder
    {
        if ($this->frozen) {
            throw new \Exception('The router has already been initialized to use the routes, you cannot define new routes as they would be ignored by the router.');
        }

        $route = new RouteBuilder($path, $controller, $httpMethods);
        $this->routeBuilders[] = $route;
        return $route;
    }

    public function getRoutes() : array
    {
        $this->frozen = true;

        return array_map(function (RouteBuilder $routeBuilder) {
            return $routeBuilder->getRoute();
        }, $this->routeBuilders);
    }
}
