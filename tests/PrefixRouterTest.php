<?php

namespace Stratify\Router\Test;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Stratify\Http\Middleware\LastDelegate;
use Stratify\Http\Response\SimpleResponse;
use Stratify\Router\PrefixRouter;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class PrefixRouterTest extends TestCase
{
    /**
     * @test
     */
    public function routes_request_based_on_path_prefix()
    {
        $router = new PrefixRouter([
            '/api' => function () {
                return new SimpleResponse('API');
            },
            '/admin' => function () {
                return new SimpleResponse('Admin');
            },
        ]);

        $response = $router->process($this->request('/api/test'), new LastDelegate);
        $this->assertEquals('API', $response->getBody()->__toString());
        $response = $router->process($this->request('/admin'), new LastDelegate);
        $this->assertEquals('Admin', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function takes_the_first_prefix_matching()
    {
        $router = new PrefixRouter([
            '/api' => function () {
                return new SimpleResponse('API');
            },
            '/' => function () {
                return new SimpleResponse('Root');
            },
        ]);

        $response = $router->process($this->request('/api/test'), new LastDelegate);
        $this->assertEquals('API', $response->getBody()->__toString());
        $response = $router->process($this->request('/'), new LastDelegate);
        $this->assertEquals('Root', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function calls_next_middleware_if_no_route_matched()
    {
        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request) {
                return new SimpleResponse('Hello world!');
            }
        };

        $router = new PrefixRouter([
            '/api' => function () {
                return new SimpleResponse('');
            },
        ]);

        $response = $router->process($this->request('/'), $delegate);
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
        $router->process($this->request('/api/test'), new LastDelegate);
    }

    private function request($uri)
    {
        return new ServerRequest([], [], $uri, 'GET');
    }
}
