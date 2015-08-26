<?php

namespace Stratify\Router;

use Stratify\Router\Route\RouteBuilder;

if (! function_exists('Stratify\Router\route')) {
    function route($controller, string $name = null) : RouteBuilder
    {
        return new RouteBuilder($controller, null, $name);
    }
}
