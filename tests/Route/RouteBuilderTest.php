<?php

namespace Stratify\Router\Test\Route;

use Psr\Http\Message\ServerRequestInterface;
use Stratify\Router\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\TextResponse;
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
    public function configures_optional_parameters()
    {
        $routes = [
            '/{foo}' => route(function (ServerRequestInterface $request, $response, $next) {
                return new HtmlResponse('Hello ' . $request->getAttribute('foo'));
            })->optional('foo', 'bar'),
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
    public function configures_http_methods()
    {
        $routes = [
            // Single method
            '/foo' => route(function (ServerRequestInterface $request, $response, $next) {
                return new HtmlResponse('Hello');
            })->method('POST'),
            // Multiple methods
            '/bar' => route(function (ServerRequestInterface $request, $response, $next) {
                return new HtmlResponse('Hello');
            })->method('POST', 'PUT'),
        ];

        $router = new Router($routes);
        $next = function () {
            return new TextResponse('Not found');
        };

        // Single method
        $response = $router->__invoke($this->request('/foo', 'POST'), new Response, $this->next());
        $this->assertEquals('Hello', $response->getBody()->getContents());
        // Do not match anything else
        $response = $router->__invoke($this->request('/foo'), new Response, $next);
        $this->assertEquals('Not found', $response->getBody()->getContents());

        // Multiple methods
        $response = $router->__invoke($this->request('/bar', 'POST'), new Response, $this->next());
        $this->assertEquals('Hello', $response->getBody()->getContents());
        $response = $router->__invoke($this->request('/bar', 'PUT'), new Response, $this->next());
        $this->assertEquals('Hello', $response->getBody()->getContents());
        // Do not match anything else
        $response = $router->__invoke($this->request('/bar'), new Response, $next);
        $this->assertEquals('Not found', $response->getBody()->getContents());
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
