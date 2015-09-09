<?php

namespace Stratify\Router\Test\Mock;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * We have to use static functions because the "SimpleInvoker" doesn't do auto-instantiation
 * of controllers.
 */
class FakeRestController
{
    public static function index(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('Index');
    }

    public static function post(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('Post');
    }

    public static function get(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('GET '.$request->getAttribute('id'));
    }

    public static function put(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('PUT '.$request->getAttribute('id'));
    }

    public static function delete(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('DELETE '.$request->getAttribute('id'));
    }
}
