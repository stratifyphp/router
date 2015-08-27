<?php

namespace Stratify\Router\Test\Invoker;

use Stratify\Router\Invoker\SimpleInvoker;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class SimpleInvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function passes_request_and_response()
    {
        $request = new ServerRequest;
        $response = new Response;

        $calls = 0;
        $callable = function () use (&$calls, $request, $response) {
            $calls++;

            $args = func_get_args();

            $this->assertCount(3, $args);
            $this->assertSame($request, $args[0]);
            $this->assertSame($response, $args[1]);
            $this->assertTrue(is_callable($args[2]));
        };

        $invoker = new SimpleInvoker;
        $invoker->call($callable, [
            'request'  => $request,
            'response' => $response,
            'next'     => function () {},
            'foo'      => 'bar', // extra param that will be ignored
        ]);

        $this->assertEquals(1, $calls);
    }
}
