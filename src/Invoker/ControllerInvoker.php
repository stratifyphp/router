<?php

namespace Stratify\Router\Invoker;

use Interop\Container\ContainerInterface;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stratify\Http\Middleware\Invoker\MiddlewareInvoker;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Invokes controllers with dependency injection features:
 *
 * - resolves them from the container if they aren't callable
 * - passes parameters based on the parameter names
 * - do dependency injection in parameters (based on type-hints)
 *
 * Additionally it allows controllers to return string response (which
 * will be written to the response).
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ControllerInvoker implements MiddlewareInvoker
{
    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @var Invoker|null
     */
    private $invoker;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function invoke($middleware, ServerRequestInterface $request, callable $next) : ResponseInterface
    {
        if (! $this->invoker) {
            $this->invoker = $this->createInvoker();
        }

        $parameters = $request->getAttributes();
        $parameters['request'] = $request;
        $parameters['next'] = $next;

        $newResponse = $this->invoker->call($middleware, $parameters);

        if (is_string($newResponse)) {
            // Allow direct string response
            $newResponse = new HtmlResponse($newResponse);
        } elseif (! $newResponse instanceof ResponseInterface) {
            throw new \RuntimeException(sprintf(
                'The controller did not return a response (expected %s, got %s)',
                ResponseInterface::class,
                is_object($newResponse) ? get_class($newResponse) : gettype($newResponse)
            ));
        }

        return $newResponse;
    }

    private function createInvoker() : InvokerInterface
    {
        if ($this->container) {
            $resolver = new ResolverChain([
                new AssociativeArrayResolver,
                new TypeHintContainerResolver($this->container),
            ]);
        } else {
            $resolver = new AssociativeArrayResolver;
        }

        return new Invoker($resolver, $this->container);
    }
}
