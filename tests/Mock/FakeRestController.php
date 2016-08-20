<?php

namespace Stratify\Router\Test\Mock;

use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Response\SimpleResponse;

/**
 * We have to use static functions because the "SimpleInvoker" doesn't do auto-instantiation
 * of controllers.
 */
class FakeRestController
{
    public static function index(ServerRequestInterface $request, callable $next)
    {
        return new SimpleResponse('Index');
    }

    public static function post(ServerRequestInterface $request, callable $next)
    {
        return new SimpleResponse('Post');
    }

    public static function get(ServerRequestInterface $request, callable $next)
    {
        return new SimpleResponse('GET '.$request->getAttribute('id'));
    }

    public static function put(ServerRequestInterface $request, callable $next)
    {
        return new SimpleResponse('PUT '.$request->getAttribute('id'));
    }

    public static function delete(ServerRequestInterface $request, callable $next)
    {
        return new SimpleResponse('DELETE '.$request->getAttribute('id'));
    }
}
