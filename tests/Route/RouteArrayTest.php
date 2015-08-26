<?php

namespace Stratify\Router\Test\Route;

use Stratify\Router\Route\RouteArray;
use function Stratify\Router\route;

class RouteArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function provides_routes()
    {
        $provider = new RouteArray([
            '/' => route('controller'),
        ]);
        $routes = $provider->getRoutes();

        $this->assertCount(1, $routes);

        $route = $routes[0];
        $this->assertEquals('/', $route->path);
        $this->assertEquals('controller', $route->handler);
    }
}
