<?php

namespace Stratify\Router\Invoker;

use Invoker\InvokerInterface;

/**
 * Simple invoker that expect the callable to be actually callable and
 * that just pass $request and $response to controllers.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SimpleInvoker implements InvokerInterface
{
    public function call($callable, array $parameters = [])
    {
        if (! is_callable($callable)) {
            throw new \Exception('The controller is not callable');
        }

        if (!isset($parameters['request']) || !isset($parameters['response'])) {
            throw new \Exception('Expected request and response in parameters');
        }

        $request = $parameters['request'];
        $response = $parameters['response'];
        $next = $parameters['next'];

        return call_user_func($callable, $request, $response, $next);
    }
}
