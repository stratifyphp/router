<?php

namespace Stratify\Router;

use Aura\Router\Exception\RouteNotFound;
use Aura\Router\Generator;
use Stratify\Router\Exception\UnknownRoute;

/**
 * Generates URLs for routes registered in the application.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class UrlGenerator
{
    /**
     * @var Generator
     */
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @throws UnknownRoute if the route name did not match any route.
     */
    public function generate(string $routeName, array $parameters = []) : string
    {
        try {
            $url = $this->generator->generate($routeName, $parameters);
        } catch (RouteNotFound $e) {
            throw new UnknownRoute($routeName);
        }

        if ($url === false) {
            throw new UnknownRoute($routeName);
        }

        return $url;
    }
}
