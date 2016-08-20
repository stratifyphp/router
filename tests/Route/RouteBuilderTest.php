<?php

namespace Stratify\Router\Test\Route;

use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Response\SimpleResponse;
use Stratify\Router\Router;
use Zend\Diactoros\ServerRequest;
use function Stratify\Router\route;

class RouteBuilderTest extends \PHPUnit_Framework_TestCase
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
        $response = $router->__invoke($this->request('/9'), $this->next());
        $this->assertEquals('Number 9', $response->getBody()->__toString());

        // Do not match anything else
        $response = $router->__invoke($this->request('/hello'), function () {
            return new SimpleResponse('Not found');
        });
        $this->assertEquals('Not found', $response->getBody()->__toString());
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
        $response = $router->__invoke($this->request('/'), $this->next());
        $this->assertEquals('Hello bar', $response->getBody()->__toString());

        // Request value
        $response = $router->__invoke($this->request('/john'), $this->next());
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
        $next = function () {
            return new SimpleResponse('Not found');
        };

        // Single method
        $response = $router->__invoke($this->request('/foo', 'POST'), $this->next());
        $this->assertEquals('Hello', $response->getBody()->__toString());
        // Do not match anything else
        $response = $router->__invoke($this->request('/foo'), $next);
        $this->assertEquals('Not found', $response->getBody()->__toString());

        // Multiple methods
        $response = $router->__invoke($this->request('/bar', 'POST'), $this->next());
        $this->assertEquals('Hello', $response->getBody()->__toString());
        $response = $router->__invoke($this->request('/bar', 'PUT'), $this->next());
        $this->assertEquals('Hello', $response->getBody()->__toString());
        // Do not match anything else
        $response = $router->__invoke($this->request('/bar'), $next);
        $this->assertEquals('Not found', $response->getBody()->__toString());
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
