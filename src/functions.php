<?php

namespace Stratify\Router;

use Stratify\Router\Route\RouteBuilder;

if (! function_exists('Stratify\Router\route')) {
    function route(string $path, $controller) : RouteBuilder
    {
        return new RouteBuilder($path, $controller);
    }
}
