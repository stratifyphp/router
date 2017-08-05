<?php

namespace Stratify\Router;

use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Http\Middleware\LastDelegate;
use Stratify\Router\Invoker\ControllerInvoker;
use Stratify\Router\Route\RouteBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The router is implemented as a middleware because:
 *
 * - it's simpler to have one concept (middleware) instead of many
 * - that makes the application decoupled from the router
 * - multiple routers can be used in the same application
 *
 * If the router doesn't have a route matching the request, it simply calls the next middleware.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Router implements MiddlewareInterface
{
    /**
     * @var MiddlewareInvoker
     */
    private $invoker;

    /**
     * @var RouterContainer
     */
    private $routerContainer;

    /**
     * @var UrlGenerator|null
     */
    private $urlGenerator;

    public function __construct(array $routes, ContainerInterface $container = null)
    {
        $this->invoker = new ControllerInvoker($container);
        $this->routerContainer = new RouterContainer;
        $this->addRoutes($routes);
    }

    /**
     * Route the incoming request to its handler, or call the next middleware if no route was found.
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $matcher = $this->routerContainer->getMatcher();

        $route = $matcher->match($request);

        if ($route === false) {
            // Call the next middleware
            return $delegate->process($request);
        }

        foreach ($route->attributes as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }

        return $this->dispatch($route->handler, $request);
    }

    public function getUrlGenerator() : UrlGenerator
    {
        if (! $this->urlGenerator) {
            $this->urlGenerator = new UrlGenerator($this->routerContainer->getGenerator());
        }

        return $this->urlGenerator;
    }

    private function dispatch($handler, ServerRequestInterface $request) : ResponseInterface
    {
        return $this->invoker->invoke($handler, $request, new LastDelegate);
    }

    private function addRoutes(array $routes) : void
    {
        $map = $this->routerContainer->getMap();

        $newRoutes = [];
        foreach ($routes as $path => $route) {
            if (! is_string($path)) {
                throw new \Exception('The routes array must be indexed by URI paths, got an integer instead');
            }

            if ($route instanceof RouteBuilder) {
                $subRoutes = $route->getRoutes();
                foreach ($subRoutes as $subRoute) {
                    $newRoutes[] = $this->prepareRoute($subRoute, $path);
                }
            } else {
                $controller = $route;
                $route = new Route();
                $route->handler($controller);

                $newRoutes[] = $this->prepareRoute($route, $path);
            }
        }

        $map->setRoutes($newRoutes);
    }

    private function prepareRoute(Route $route, string $path) : Route
    {
        if (!$route->allows) {
            $route->allows('GET');
        }

        $route->path($path);

        return $route;
    }
}
