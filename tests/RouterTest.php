<?php

namespace Stratify\Router\Test;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Exception\HttpMethodNotAllowed;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Router\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;
use function Stratify\Router\resource;
use function Stratify\Router\route;

class RouterTest extends \PHPUnit_Framework_TestCase
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

        $router->__invoke($this->request('/'), new Response, $this->next());

        $this->assertEquals(1, $calls);
    }

    /**
     * @test
     */
    public function calls_next_middleware_if_no_route_matched()
    {
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write('Hello world!');
            return $response;
        };

        $router = new Router([]);
        $response = $router->__invoke($this->request('/'), new Response, $next);

        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function calls_controller_with_middleware_parameters()
    {
        $router = new Router([
            '/' => route(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                $response->getBody()->write('Hello world!');
                return $response;
            }),
        ]);

        $response = $router->__invoke($this->request('/'), new Response, $this->next());

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

        $invoker = $this->getMockForAbstractClass(MiddlewareInvoker::class);
        // Expect controller is invoked
        $invoker->expects($this->once())
            ->method('invoke')
            ->with('controller')
            ->willReturn(new Response);

        $router = new Router($routes, $invoker);
        $router->__invoke($this->request('/'), new Response, $this->next());
    }

    /**
     * @test
     */
    public function accepts_routes_in_array_as_shortcut()
    {
        $routes = [
            '/' => 'controller',
        ];

        $invoker = $this->getMockForAbstractClass(MiddlewareInvoker::class);
        // Expect controller is invoked
        $invoker->expects($this->once())
            ->method('invoke')
            ->with('controller')
            ->willReturn(new Response);

        $router = new Router($routes, $invoker);
        $router->__invoke($this->request('/'), new Response, $this->next());
    }

    /**
     * @test
     */
    public function routes_match_http_get_by_default_only()
    {
        $router = new Router([
            '/' => function ($request, ResponseInterface $response, $next) {
                $response->getBody()->write('Hello world!');
                return $response;
            },
        ]);

        // Match GET
        $response = $router->__invoke($this->request('/', 'GET'), new Response, $this->next());
        $this->assertEquals('Hello world!', $response->getBody()->__toString());
        // Don't match any other HTTP method
        $methods = ['POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'TRACE'];
        foreach ($methods as $method) {
            try {
                $router->__invoke($this->request('/', $method), new Response, $this->next());
                $this->fail('Expected exception HttpMethodNotAllowed');
            } catch (HttpMethodNotAllowed $e) {
            }
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
                    return new HtmlResponse('GET');
                },
                'post' => function () {
                    return new HtmlResponse('POST');
                },
            ]),
        ]);

        $response = $router->__invoke($this->request('/', 'GET'), new Response, $this->next());
        $this->assertEquals('GET', $response->getBody()->__toString());

        $response = $router->__invoke($this->request('/', 'POST'), new Response, $this->next());
        $this->assertEquals('POST', $response->getBody()->__toString());
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
