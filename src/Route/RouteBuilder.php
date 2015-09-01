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
     * Set the pattern (regular expression) that a path parameter must match.
     *
     * Example for "/article/{id}":
     *
     *     ->pattern('id', '\d+')
     */
    public function pattern(string $parameter, string $pattern) : RouteBuilder
    {
        $this->route->tokens([
            $parameter => $pattern,
        ]);
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
        $this->route->allows($methods);
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
        $this->route->tokens([
            $parameter => '([^/]+)?',
        ]);
        $this->route->defaults([
            $parameter => $defaultValue,
        ]);
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
        $this->route->secure($secure);
        return $this;
    }

    public function getRoute() : Route
    {
        return $this->route;
    }
}
