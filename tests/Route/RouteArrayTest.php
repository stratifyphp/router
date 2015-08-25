<?php

namespace Stratify\Router\Test\Route;

use Aura\Router\Route;
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
            'home' => route('/', 'controller'),
        ]);
        $routes = $provider->getRoutes();

        $this->assertCount(1, $routes);

        /** @var Route $route */
        $route = reset($routes);
        $this->assertEquals('home', $route->name);
        $this->assertEquals('/', $route->path);
        $this->assertEquals('controller', $route->handler);
    }
}
