<?php

namespace Stratify\Router\Test;

use Stratify\Router\RestRouter;
use Stratify\Router\Test\Mock\FakeRestController;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;

class RestRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function routes_get_list()
    {
        $router = new RestRouter([
            '/item' => FakeRestController::class,
        ]);
        $response = $router->__invoke($this->request('/item'), new Response, $this->next());

        $this->assertEquals('Index', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function routes_post_item()
    {
        $router = new RestRouter([
            '/item' => FakeRestController::class,
        ]);
        $response = $router->__invoke($this->request('/item', 'POST'), new Response, $this->next());

        $this->assertEquals('Post', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function routes_get_item()
    {
        $router = new RestRouter([
            '/item' => FakeRestController::class,
        ]);
        $response = $router->__invoke($this->request('/item/123'), new Response, $this->next());

        $this->assertEquals('GET 123', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function routes_put_item()
    {
        $router = new RestRouter([
            '/item' => FakeRestController::class,
        ]);
        $response = $router->__invoke($this->request('/item/123', 'PUT'), new Response, $this->next());

        $this->assertEquals('PUT 123', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function routes_delete_item()
    {
        $router = new RestRouter([
            '/item' => FakeRestController::class,
        ]);
        $response = $router->__invoke($this->request('/item/123', 'DELETE'), new Response, $this->next());

        $this->assertEquals('DELETE 123', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function calls_next_middleware_if_no_route_matched()
    {
        $router = new RestRouter([]);
        $response = $router->__invoke($this->request('/'), new Response, function () {
            return new HtmlResponse('Hello world!');
        });

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
