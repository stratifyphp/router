<?php

namespace Stratify\Router\Test\Route;

use Aura\Router\Route;
use Stratify\Router\Route\RouteBuilder;
use Stratify\Router\Route\RouteMap;

class RouteMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RouteMap
     */
    private $map;

    public function setUp()
    {
        $this->map = new RouteMap;
    }

    /**
     * @test
     */
    public function defines_get_requests()
    {
        $routeBuilder = $this->map->get('/', 'controller');
        $this->assertInstanceOf(RouteBuilder::class, $routeBuilder);
        $route = $this->getFirstRoute();
        $this->assertEquals(['GET'], $route->allows);
        $this->assertEquals('/', $route->path);
        $this->assertEquals('controller', $route->handler);
    }

    /**
     * @test
     */
    public function defines_post_requests()
    {
        $routeBuilder = $this->map->post('/', 'controller');
        $this->assertInstanceOf(RouteBuilder::class, $routeBuilder);
        $route = $this->getFirstRoute();
        $this->assertEquals(['POST'], $route->allows);
        $this->assertEquals('/', $route->path);
        $this->assertEquals('controller', $route->handler);
    }

    /**
     * @test
     */
    public function defines_delete_requests()
    {
        $routeBuilder = $this->map->delete('/', 'controller');
        $this->assertInstanceOf(RouteBuilder::class, $routeBuilder);
        $route = $this->getFirstRoute();
        $this->assertEquals(['DELETE'], $route->allows);
        $this->assertEquals('/', $route->path);
        $this->assertEquals('controller', $route->handler);
    }

    /**
     * @test
     */
    public function defines_patch_requests()
    {
        $routeBuilder = $this->map->patch('/', 'controller');
        $this->assertInstanceOf(RouteBuilder::class, $routeBuilder);
        $route = $this->getFirstRoute();
        $this->assertEquals(['PATCH'], $route->allows);
        $this->assertEquals('/', $route->path);
        $this->assertEquals('controller', $route->handler);
    }

    /**
     * @test
     */
    public function defines_options_requests()
    {
        $routeBuilder = $this->map->options('/', 'controller');
        $this->assertInstanceOf(RouteBuilder::class, $routeBuilder);
        $route = $this->getFirstRoute();
        $this->assertEquals(['OPTIONS'], $route->allows);
        $this->assertEquals('/', $route->path);
        $this->assertEquals('controller', $route->handler);
    }

    /**
     * @test
     */
    public function defines_head_requests()
    {
        $routeBuilder = $this->map->head('/', 'controller');
        $this->assertInstanceOf(RouteBuilder::class, $routeBuilder);
        $route = $this->getFirstRoute();
        $this->assertEquals(['HEAD'], $route->allows);
        $this->assertEquals('/', $route->path);
        $this->assertEquals('controller', $route->handler);
    }

    private function getFirstRoute() : Route
    {
        $routes = $this->map->getRoutes();
        return reset($routes);
    }
}
