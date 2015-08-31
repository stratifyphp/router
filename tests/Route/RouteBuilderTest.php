<?php

namespace Stratify\Router\Test\Route;

use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Exception\HttpMethodNotAllowed;
use Stratify\Router\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
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
            '/{foo}' => route(function (ServerRequestInterface $request, $response, $next) {
                return new HtmlResponse('Number ' . $request->getAttribute('foo'));
            })->pattern('foo', '\d+'),
        ];

        $router = new Router($routes);

        // Match number
        $response = $router->__invoke($this->request('/9'), new Response, $this->next());
        $this->assertEquals('Number 9', $response->getBody()->__toString());

        // Do not match anything else
        $response = $router->__invoke($this->request('/hello'), new Response, function () {
            return new HtmlResponse('Not found');
        });
        $this->assertEquals('Not found', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function configures_default_parameter_values()
    {
        $routes = [
            '/{foo}' => route(function (ServerRequestInterface $request, $response, $next) {
                return new HtmlResponse('Hello ' . $request->getAttribute('foo'));
            })->pattern('foo', '([^/]+)?') // optional parameter
            ->defaults([
                'foo' => 'bar',
            ]),
        ];

        $router = new Router($routes);

        // Default value
        $response = $router->__invoke($this->request('/'), new Response, $this->next());
        $this->assertEquals('Hello bar', $response->getBody()->__toString());

        // Request value
        $response = $router->__invoke($this->request('/john'), new Response, $this->next());
        $this->assertEquals('Hello john', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function configures_http_method()
    {
        $routes = [
            '/' => route(function (ServerRequestInterface $request, $response, $next) {
                return new HtmlResponse('Hello');
            })->method('POST'),
        ];

        $router = new Router($routes);

        $response = $router->__invoke($this->request('/', 'POST'), new Response, $this->next());
        $this->assertEquals('Hello', $response->getBody()->__toString());

        // Do not match anything else
        try {
            $router->__invoke($this->request('/'), new Response, $this->next());
            $this->fail('Exception not triggered');
        } catch (HttpMethodNotAllowed $e) {
            $this->assertEquals('HTTP method not allowed, allowed methods: POST', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function configures_http_methods()
    {
        $routes = [
            '/' => route(function (ServerRequestInterface $request, $response, $next) {
                return new HtmlResponse('Hello');
            })->methods(['POST', 'PUT']),
        ];

        $router = new Router($routes);

        $response = $router->__invoke($this->request('/', 'POST'), new Response, $this->next());
        $this->assertEquals('Hello', $response->getBody()->__toString());
        $response = $router->__invoke($this->request('/', 'PUT'), new Response, $this->next());
        $this->assertEquals('Hello', $response->getBody()->__toString());

        // Do not match anything else
        try {
            $router->__invoke($this->request('/'), new Response, $this->next());
            $this->fail('Exception not triggered');
        } catch (HttpMethodNotAllowed $e) {
            $this->assertEquals('HTTP method not allowed, allowed methods: POST, PUT', $e->getMessage());
        }
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
