<?php

namespace Stratify\Router\Test\Route;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Exception\HttpNotFound;
use Stratify\Http\Middleware\LastDelegate;
use Stratify\Http\Response\SimpleResponse;
use Stratify\Router\Router;
use Zend\Diactoros\ServerRequest;
use function Stratify\Router\route;

class RouteBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function configures_parameter_pattern()
    {
        $routes = [
            '/{foo}' => route(function (ServerRequestInterface $request) {
                return new SimpleResponse('Number ' . $request->getAttribute('foo'));
            })->pattern('foo', '\d+'),
        ];

        $router = new Router($routes);

        // Match number
        $response = $router->process($this->request('/9'), new LastDelegate);
        $this->assertEquals('Number 9', $response->getBody()->__toString());

        // Do not match anything else
        $this->expectException(HttpNotFound::class);
        $router->process($this->request('/hello'), new LastDelegate);
    }

    /**
     * @test
     */
    public function configures_optional_parameters()
    {
        $routes = [
            '/{foo}' => route(function (ServerRequestInterface $request) {
                return new SimpleResponse('Hello ' . $request->getAttribute('foo'));
            })->optional('foo', 'bar'),
        ];

        $router = new Router($routes);

        // Default value
        $response = $router->process($this->request('/'), new LastDelegate);
        $this->assertEquals('Hello bar', $response->getBody()->__toString());

        // Request value
        $response = $router->process($this->request('/john'), new LastDelegate);
        $this->assertEquals('Hello john', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function configures_http_methods()
    {
        $routes = [
            // Single method
            '/foo' => route(function () {
                return new SimpleResponse('Hello');
            })->method('POST'),
            // Multiple methods
            '/bar' => route(function () {
                return new SimpleResponse('Hello');
            })->method('POST', 'PUT'),
        ];

        $router = new Router($routes);
        $notFoundDelegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request) {
                return new SimpleResponse('Not found');
            }
        };

        // Single method
        $response = $router->process($this->request('/foo', 'POST'), new LastDelegate);
        $this->assertEquals('Hello', $response->getBody()->__toString());
        // Do not match anything else
        $response = $router->process($this->request('/foo'), $notFoundDelegate);
        $this->assertEquals('Not found', $response->getBody()->__toString());

        // Multiple methods
        $response = $router->process($this->request('/bar', 'POST'), new LastDelegate);
        $this->assertEquals('Hello', $response->getBody()->__toString());
        $response = $router->process($this->request('/bar', 'PUT'), new LastDelegate);
        $this->assertEquals('Hello', $response->getBody()->__toString());
        // Do not match anything else
        $response = $router->process($this->request('/bar'), $notFoundDelegate);
        $this->assertEquals('Not found', $response->getBody()->__toString());
    }

    private function request($uri, $method = 'GET')
    {
        return new ServerRequest([], [], $uri, $method);
    }
}
