<?php

namespace Stratify\Router\Test;

use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Router\Route\RouteMap;
use Stratify\Router\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function routes_request_to_handler()
    {
        $map = new RouteMap();

        $calls = 0;
        $map->get('/', function () use (&$calls) {
            $calls++;
            return new Response;
        });

        $router = new Router($map);
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

        $router = new Router();
        $response = $router->__invoke($this->request('/'), new Response, $next);

        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function calls_controller_with_middleware_parameters()
    {
        $map = new RouteMap();
        $map->get('/', function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
            $response->getBody()->write('Hello world!');
            return $response;
        });
        $router = new Router($map);
        $response = $router->__invoke($this->request('/'), new Response, $this->next());

        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function accepts_routes_in_array_with_helper()
    {
        $routes = [
            '/' => \Stratify\Router\route('controller'),
        ];

        $invoker = $this->getMockForAbstractClass(InvokerInterface::class);
        // Expect controller is invoked
        $invoker->expects($this->once())
            ->method('call')
            ->with('controller')
            ->willReturn(new Response);

        $router = Router::fromArray($routes, $invoker);
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

        $invoker = $this->getMockForAbstractClass(InvokerInterface::class);
        // Expect controller is invoked
        $invoker->expects($this->once())
            ->method('call')
            ->with('controller')
            ->willReturn(new Response);

        $router = Router::fromArray($routes, $invoker);
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
