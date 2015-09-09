<?php

namespace Stratify\Router\Test\Mock;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class FakeRestController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('Index');
    }

    public function post(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('Post');
    }

    public function get(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('GET '.$request->getAttribute('id'));
    }

    public function put(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('PUT '.$request->getAttribute('id'));
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return new HtmlResponse('DELETE '.$request->getAttribute('id'));
    }
}
