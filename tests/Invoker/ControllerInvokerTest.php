<?php

namespace Stratify\Router\Test\Invoker;

use Interop\Container\ContainerInterface;
use Stratify\Router\Invoker\ControllerInvoker;
use Zend\Diactoros\ServerRequest;

class ControllerInvokerTest extends \PHPUnit_Framework_TestCase
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
        $next = function () {};
        $response = $invoker->invoke($middleware, $request, $next);

        $this->assertEquals('Hello world!', $response->getBody()->__toString());
    }
}
