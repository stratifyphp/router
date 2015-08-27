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
     * @var Route
     */
    private $route;

    public function __construct($controller, string $path = null, string $name = null)
    {
        $this->route = new Route();
        $this->route->handler($controller);
        if ($path !== null) {
            $this->route->path($path);
        }
        if ($name !== null) {
            $this->route->name($name);
        }
    }

    /**
     * Set the pattern that a path parameter must match.
     */
    public function pattern(string $parameter, string $pattern) : RouteBuilder
    {
        $this->route->tokens([
            $parameter => $pattern,
        ]);
        return $this;
    }

    /**
     * Set HTTP method accepted for this route.
     */
    public function method(string $method) : RouteBuilder
    {
        $this->route->allows([$method]);
        return $this;
    }

    /**
     * Set HTTP methods accepted for this route.
     */
    public function methods(array $methods) : RouteBuilder
    {
        $this->route->allows($methods);
        return $this;
    }

    /**
     * Set default values for request attributes.
     *
     * Example for "/blog/article.{format}":
     *
     *     ->defaults([
     *         'format' => 'json'
     *     ])
     */
    public function defaults(array $defaults) : RouteBuilder
    {
        $this->route->defaults($defaults);
        return $this;
    }

    /**
     * Add a requirement on the HTTP host of the request.
     */
    public function host(string $host) : RouteBuilder
    {
        $this->route->host($host);
        return $this;
    }

    /**
     * Set whether or not the route must use HTTPS.
     *
     * If set to true, the request must be an HTTPS request;
     * if false, it must *not* be HTTPS.
     */
    public function secure(bool $secure = true) : RouteBuilder
    {
        $this->route->secure($secure);
        return $this;
    }

    /**
     * Set a wildcard parameter that will match any extra paths in the request URI.
     *
     * Example for route with path `/buy`:
     *
     *     ->wildcard('categories')
     *
     * For URI `/buy/food/frozen/pizza` the `categories` attribute will have value:
     *
     *     ['food', 'frozen', 'pizza']
     *
     * For URI `/buy` the `categories` attribute will be an empty array.
     */
    public function wildcard(string $wildcard) : RouteBuilder
    {
        $this->route->wildcard($wildcard);
        return $this;
    }

    public function getRoute() : Route
    {
        return $this->route;
    }
}
