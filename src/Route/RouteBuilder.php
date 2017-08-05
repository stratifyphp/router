<?php

namespace Stratify\Router\Route;

use Aura\Router\Route;

/**
 * Helps building a route with a fluent interface.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class RouteBuilder
{
    /**
     * @var Route[]
     */
    private $routes = [];

    private function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public static function singleRoute($controller, string $path = null, string $name = null) : RouteBuilder
    {
        $route = new Route();
        $route->handler($controller);
        if ($path !== null) {
            $route->path($path);
        }
        if ($name !== null) {
            $route->name($name);
        }

        return new static([$route]);
    }

    public static function multipleRoutes(array $controllers) : RouteBuilder
    {
        $routes = [];
        foreach ($controllers as $method => $controller) {
            $route = new Route();
            $route->handler($controller);
            $route->allows(strtoupper($method));
            $routes[] = $route;
        }

        return new static($routes);
    }

    /**
     * Set the pattern (regular expression) that a path parameter must match.
     *
     * Example for "/article/{id}":
     *
     *     ->pattern('id', '\d+')
     */
    public function pattern(string $parameter, string $pattern) : RouteBuilder
    {
        foreach ($this->routes as $route) {
            $route->tokens([
                $parameter => $pattern,
            ]);
        }
        return $this;
    }

    /**
     * Set HTTP methods accepted for this route.
     *
     * Example:
     *
     *     ->method('GET')
     *     ->method('GET', 'POST')
     */
    public function method(string ...$methods) : RouteBuilder
    {
        foreach ($this->routes as $route) {
            $route->allows($methods);
        }
        return $this;
    }

    /**
     * Mark a path parameter as optional.
     *
     * - sets the following pattern for the parameter: `([^/]+)?`
     * - sets the provided default value
     *
     * **Warning:** if you set a custom pattern for the parameter, it will be replaced
     * to be `([^/]+)?`.
     *
     * Example for "/blog/article.{format}":
     *
     *     ->optional('format', 'json')
     */
    public function optional(string $parameter, string $defaultValue) : RouteBuilder
    {
        foreach ($this->routes as $route) {
            $route->tokens([
                $parameter => '([^/]+)?',
            ]);
            $route->defaults([
                $parameter => $defaultValue,
            ]);
        }
        return $this;
    }

    /**
     * Set whether or not the route must use HTTPS.
     *
     * If set to true, the request must be an HTTPS request;
     * if false, it must *not* be HTTPS.
     *
     * Example:
     *
     *     ->secure()
     */
    public function secure(bool $secure = true) : RouteBuilder
    {
        foreach ($this->routes as $route) {
            $route->secure($secure);
        }
        return $this;
    }

    /**
     * @return Route[]
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }
}
