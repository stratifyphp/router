<?php

namespace Stratify\Router\Exception;

/**
 * The route is unknown.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class UnknownRoute extends \Exception
{
    public function __construct(string $routeName)
    {
        parent::__construct(sprintf('No route named "%s" was found', $routeName));
    }
}
