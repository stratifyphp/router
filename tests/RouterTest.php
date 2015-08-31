<?php

namespace Stratify\Router\Test;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Router\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
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
            })->method('GET'),
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

    private function request($uri)
    {
        return new ServerRequest([], [], $uri, 'GET');
    }

    private function next()
    {
        return function () {
            throw new \Exception('No route matched');
        };
    }
}
