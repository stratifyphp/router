<?php

namespace Stratify\Router\Test\Invoker;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Stratify\Http\Middleware\LastDelegate;
use Stratify\Router\Invoker\ControllerInvoker;
use Zend\Diactoros\ServerRequest;

class ControllerInvokerTest extends TestCase
{
    /**
     * @test
     */
    public function allows_controllers_to_return_string()
    {
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $invoker = new ControllerInvoker($container);

        $middleware = function () {
            return 'Hello world!';
        };

        $request = new ServerRequest([], [], '/', 'GET');
        $response = $invoker->invoke($middleware, $request, new LastDelegate);

        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }
}
