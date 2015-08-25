<?php

namespace Stratify\Router\Route;

use Aura\Router\Route;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface RouteProvider
{
    /**
     * @return Route[]
     */
    public function getRoutes() : array;
}
