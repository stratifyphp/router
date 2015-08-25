<?php

namespace Stratify\Router;

use Aura\Router\RouterContainer;
use Invoker\InvokerInterface;
use Stratify\Http\Exception\HttpMethodNotAllowed;
use Stratify\Router\Invoker\SimpleInvoker;
use Stratify\Router\Route\RouteArray;
use Stratify\Router\Route\RouteBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Router\Route\RouteProvider;

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
class Router
{
    /**
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * @var RouterContainer
     */
    private $routerContainer;

    /**
     * @var RouteProvider|null
     */
    private $routes;

    /**
     * @var UrlGenerator|null
     */
    private $urlGenerator;

    public function __construct(RouteProvider $routes = null, InvokerInterface $invoker = null)
    {
        $this->invoker = $invoker ?: new SimpleInvoker();
        $this->routerContainer = new RouterContainer;
        $this->routes = $routes;
    }

    /**
     * Create a new router with the routes provided.
     */
    public static function fromArray(array $routes, InvokerInterface $invoker = null) : Router
    {
        return new self(new RouteArray($routes), $invoker);
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
        $this->init();

        $matcher = $this->routerContainer->getMatcher();

        $route = $matcher->match($request);

        if ($route === false) {
            $failedRoute = $matcher->getFailedRoute();

            // which matching rule failed?
            switch ($failedRoute->failedRule) {
                case 'Aura\Router\Rule\Allows':
                    // 405 Method not allowed
                    throw new HttpMethodNotAllowed($failedRoute->allows);
                    break;
                default:
                    // Call the next middleware
                    return $next($request, $response);
            }
        }

        foreach ($route->attributes as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }

        return $this->dispatch($route->handler, $request, $response, $route->attributes);
    }

    public function getUrlGenerator() : UrlGenerator
    {
        if (! $this->urlGenerator) {
            $this->init();
            $this->urlGenerator = new UrlGenerator($this->routerContainer->getGenerator());
        }

        return $this->urlGenerator;
    }

    private function dispatch(
        $handler,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $attributes
    ) : ResponseInterface
    {
        $parameters = $attributes;
        $parameters['request'] = $request;
        $parameters['response'] = $response;

        $newResponse = $this->invoker->call($handler, $parameters);

        if (is_string($newResponse)) {
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

        foreach ($routes as $index => $route) {
            if ($route instanceof RouteBuilder) {
                // Indexed by route name
                if (is_string($index)) {
                    $route->name($index);
                }

                $route = $route->getRoute();
            }

            $map->addRoute($route);
        }
    }

    private function init()
    {
        if ($this->routes) {
            // Add these lazily since routes can be registered between init and run
            $this->addRoutes($this->routes->getRoutes());
            // Clear it to avoid adding routes twice
            $this->routes = null;
        }
    }
}
