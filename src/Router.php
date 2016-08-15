<?php

namespace Stratify\Router;

use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Interop\Container\ContainerInterface;
use Stratify\Http\Exception\HttpNotFound;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Http\Middleware\Middleware;
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
class Router implements Middleware
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
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) : ResponseInterface
    {
        $matcher = $this->routerContainer->getMatcher();

        $route = $matcher->match($request);

        if ($route === false) {
            // Call the next middleware
            return $next($request, $response);
        }

        foreach ($route->attributes as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }

        return $this->dispatch($route->handler, $request, $response);
    }

    public function getUrlGenerator() : UrlGenerator
    {
        if (! $this->urlGenerator) {
            $this->urlGenerator = new UrlGenerator($this->routerContainer->getGenerator());
        }

        return $this->urlGenerator;
    }

    private function dispatch(
        $handler,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $next = function () {
            throw new HttpNotFound;
        };

        $newResponse = $this->invoker->invoke($handler, $request, $response, $next);

        if (is_string($newResponse)) {
            // Allow direct string response
            $response->getBody()->write($newResponse);
            $newResponse = $response;
        } elseif (! $newResponse instanceof ResponseInterface) {
            throw new \RuntimeException(sprintf(
                'The controller did not return a response (expected %s, got %s)',
                ResponseInterface::class,
                is_object($newResponse) ? get_class($newResponse) : gettype($newResponse)
            ));
        }

        return $newResponse;
    }

    private function addRoutes(array $routes)
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
