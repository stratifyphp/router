<?php

namespace Stratify\Router\Test;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Router\PrefixRouter;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class PrefixRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function routes_request_based_on_path_prefix()
    {
        $router = new PrefixRouter([
            '/api'    => function (ServerRequestInterface $request, ResponseInterface $response) {
                $response->getBody()->write('API');
                return $response;
            },
            '/admin' => function (ServerRequestInterface $request, ResponseInterface $response) {
                $response->getBody()->write('Admin');
                return $response;
            },
        ]);

        $response = $router->__invoke($this->request('/api/test'), new Response, $this->next());
        $this->assertEquals('API', $response->getBody()->__toString());
        $response = $router->__invoke($this->request('/admin'), new Response, $this->next());
        $this->assertEquals('Admin', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function takes_the_first_prefix_matching()
    {
        $router = new PrefixRouter([
            '/api'    => function (ServerRequestInterface $request, ResponseInterface $response) {
                $response->getBody()->write('API');
                return $response;
            },
            '/' => function (ServerRequestInterface $request, ResponseInterface $response) {
                $response->getBody()->write('Root');
                return $response;
            },
        ]);

        $response = $router->__invoke($this->request('/api/test'), new Response, $this->next());
        $this->assertEquals('API', $response->getBody()->__toString());
        $response = $router->__invoke($this->request('/'), new Response, $this->next());
        $this->assertEquals('Root', $response->getBody()->__toString());
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

        $router = new PrefixRouter([
            '/api'    => function (ServerRequestInterface $request, ResponseInterface $response) {
                return $response;
            },
        ]);

        $response = $router->__invoke($this->request('/'), new Response, $next);
        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function invokes_middleware_using_invoker()
    {
        $routes = [
            '/api' => 'controller',
        ];

        $invoker = $this->getMockForAbstractClass(MiddlewareInvoker::class);
        // Expect controller is invoked
        $invoker->expects($this->once())
            ->method('invoke')
            ->with('controller')
            ->willReturn(new Response);

        $router = new PrefixRouter($routes, $invoker);
        $router->__invoke($this->request('/api/test'), new Response, $this->next());
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
