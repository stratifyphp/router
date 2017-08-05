<?php

namespace Stratify\Router\Test;

use PHPUnit\Framework\TestCase;
use function Stratify\Router\resource;
use function Stratify\Router\route;

class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function does_not_error_if_required_twice()
    {
        require __DIR__ . '/../src/functions.php';
        require __DIR__ . '/../src/functions.php';

        self::assertTrue(true);
    }

    /**
     * @test
     */
    public function route_returns_a_route_builder()
    {
        $builder = route('controller', 'home');

        $route = $builder->getRoutes()[0];

        $this->assertEquals('controller', $route->handler);
        $this->assertEquals('home', $route->name);
    }

    /**
     * @test
     */
    public function resource_returns_a_route_builder()
    {
        $builder = resource([
            'get'  => 'get-controller',
            'post' => 'post-controller',
        ]);

        $routes = $builder->getRoutes();

        $this->assertEquals('get-controller', $routes[0]->handler);
        $this->assertEquals(['GET'], $routes[0]->allows);

        $this->assertEquals('post-controller', $routes[1]->handler);
        $this->assertEquals(['POST'], $routes[1]->allows);
    }
}
