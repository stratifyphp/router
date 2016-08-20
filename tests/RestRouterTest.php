<?php

namespace Stratify\Router\Test;

use Stratify\Http\Response\SimpleResponse;
use Stratify\Router\RestRouter;
use Stratify\Router\Test\Mock\FakeRestController;
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
        $response = $router->__invoke($this->request('/item'), $this->next());

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
        $response = $router->__invoke($this->request('/item', 'POST'), $this->next());

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
        $response = $router->__invoke($this->request('/item/123'), $this->next());

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
        $response = $router->__invoke($this->request('/item/123', 'PUT'), $this->next());

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
        $response = $router->__invoke($this->request('/item/123', 'DELETE'), $this->next());

        $this->assertEquals('DELETE 123', $response->getBody()->__toString());
    }

    /**
     * @test
     */
    public function calls_next_middleware_if_no_route_matched()
    {
        $router = new RestRouter([]);
        $response = $router->__invoke($this->request('/'), function () {
            return new SimpleResponse('Hello world!');
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
