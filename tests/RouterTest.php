<?php

namespace Stratify\Router\Test;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\LastDelegate;
use Stratify\Http\Response\SimpleResponse;
use Stratify\Router\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use function Stratify\Router\resource;
use function Stratify\Router\route;

class RouterTest extends TestCase
{
    /**
     * @test
     */
    public function routes_request_to_controller()
    {
        $calls = 0;
        $router = new Router([
            '/' => function () use (&$calls) {
                $calls++;
                return new Response;
            },
        ]);

        $router->process($this->request('/'), new LastDelegate);

        $this->assertEquals(1, $calls);
    }

    /**
     * @test
     */
    public function calls_next_middleware_if_no_route_matched()
    {
        $next = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request) {
                return new SimpleResponse('Hello world!');
            }
        };

        $router = new Router([]);
        $response = $router->process($this->request('/'), $next);

        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function calls_next_middleware_if_route_method_did_not_match()
    {
        $next = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request) {
                return new SimpleResponse('Hello world!');
            }
        };

        $router = new Router([
            '/' => 'foo', // only allows GET by default
        ]);
        $response = $router->process($this->request('/', 'POST'), $next);

        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function calls_controller_with_middleware_parameters()
    {
        $router = new Router([
            '/' => route(function (ServerRequestInterface $request, DelegateInterface $delegate) {
                return new SimpleResponse('Hello world!');
            }),
        ]);

        $response = $router->process($this->request('/'), new LastDelegate);

        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function invokes_controller_using_invoker()
    {
        $routes = [
            '/' => route('controller'),
        ];

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->with('controller')->willReturn(true);
        $container->expects($this->once())
            ->method('get')
            ->with('controller')
            ->willReturn(function (ServerRequestInterface $request) {
                return new SimpleResponse('Hello world!');
            });

        $router = new Router($routes, $container);
        $response = $router->process($this->request('/'), new LastDelegate);
        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function accepts_routes_in_array_as_shortcut()
    {
        $routes = [
            '/' => 'controller',
        ];

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->with('controller')->willReturn(true);
        $container->expects($this->once())
            ->method('get')
            ->with('controller')
            ->willReturn(function () {
                return new SimpleResponse('Hello world!');
            });

        $router = new Router($routes, $container);
        $response = $router->process($this->request('/'), new LastDelegate);
        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function routes_match_http_get_by_default_only()
    {
        $router = new Router([
            '/' => function () {
                return new SimpleResponse('Hello world!');
            },
        ]);

        // Match GET
        $response = $router->process($this->request('/', 'GET'), new LastDelegate);
        $this->assertEquals('Hello world!', $response->getBody()->__toString());

        // Don't match any other HTTP method
        $methods = ['POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'TRACE'];
        $next = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request) {
                return new SimpleResponse('Not found');
            }
        };
        foreach ($methods as $method) {
            $response = $router->process($this->request('/', $method), $next);
            $this->assertEquals('Not found', $response->getBody()->__toString());
        }
    }

    /**
     * @test
     */
    public function allows_different_controllers_for_different_http_methods()
    {
        $router = new Router([
            '/' => resource([
                'get' => function () {
                    return new SimpleResponse('GET');
                },
                'post' => function () {
                    return new SimpleResponse('POST');
                },
            ]),
        ]);

        $response = $router->process($this->request('/', 'GET'), new LastDelegate);
        $this->assertEquals('GET', $response->getBody()->__toString());

        $response = $router->process($this->request('/', 'POST'), new LastDelegate);
        $this->assertEquals('POST', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function supports_anonymous_classes_as_controllers()
    {
        $router = new Router([
            '/' => new class {
                public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate) {
                    return new SimpleResponse('Hello world!');
                }
            },
        ]);

        $response = $router->process($this->request('/'), new LastDelegate);
        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function supports_closures_as_controllers()
    {
        $router = new Router([
            '/' => function () {
                return new SimpleResponse('Hello world!');
            },
        ]);

        $response = $router->process($this->request('/'), new LastDelegate);
        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function supports_middlewares_as_controllers()
    {
        $router = new Router([
            '/' => new class implements MiddlewareInterface {
                public function process(ServerRequestInterface $request, DelegateInterface $delegate) {
                    return new SimpleResponse('Hello world!');
                }
            },
        ]);

        $response = $router->process($this->request('/'), new LastDelegate);
        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function allows_controllers_to_return_string()
    {
        $router = new Router([
            '/' => function () {
                return 'Hello world!';
            },
        ]);

        $response = $router->process($this->request('/'), new LastDelegate);
        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    private function request($uri, $method = 'GET')
    {
        return new ServerRequest([], [], $uri, $method);
    }

    private function next()
    {
        return function () {
            throw new \Exception('No route matched');
        };
    }
}
