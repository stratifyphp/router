<?php

namespace Stratify\Router;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Stratify\Router\Route\RouteBuilder;

if (! function_exists('Stratify\Router\route')) {

    /**
     * Create a route.
     *
     * @param MiddlewareInterface|callable|array|string $controller
     */
    function route($controller, string $name = null) : RouteBuilder
    {
        return RouteBuilder::singleRoute($controller, null, $name);
    }

    /**
     * Create routes for an HTTP resource.
     *
     *     resource([
     *         'get'  => function () { ... },
     *         'post' => function () { ... },
     *     ])
     */
    function resource(array $controllers) : RouteBuilder
    {
        return RouteBuilder::multipleRoutes($controllers);
    }

}
