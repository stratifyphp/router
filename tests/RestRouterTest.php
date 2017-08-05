<?php

namespace Stratify\Router\Test;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\LastDelegate;
use Stratify\Http\Response\SimpleResponse;
use Stratify\Router\RestRouter;
use Stratify\Router\Test\Mock\FakeRestController;
use Zend\Diactoros\ServerRequest;

class RestRouterTest extends TestCase
{
    /**
     * @test
     */
    public function routes_get_list()
    {
        $router = new RestRouter([
            '/item' => FakeRestController::class,
        ]);
        $response = $router->process($this->request('/item'), new LastDelegate);

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
        $response = $router->process($this->request('/item', 'POST'), new LastDelegate);

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
        $response = $router->process($this->request('/item/123'), new LastDelegate);

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
        $response = $router->process($this->request('/item/123', 'PUT'), new LastDelegate);

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
        $response = $router->process($this->request('/item/123', 'DELETE'), new LastDelegate);

        $this->assertEquals('DELETE 123', $response->getBody()->__toString());
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

        $router = new RestRouter([]);
        $response = $router->process($this->request('/'), $delegate);

        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }

    private function request($uri, $method = 'GET')
    {
        return new ServerRequest([], [], $uri, $method);
    }
}
