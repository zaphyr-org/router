<?php

declare(strict_types=1);

namespace Zaphyr\Router\Contracts;

use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Router\Contracts\Attributes\RouteInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface DispatcherInterface extends MiddlewareAwareInterface, RequestHandlerInterface
{
    /**
     * @param RouteInterface $route
     *
     * @return $this
     */
    public function addRoute(RouteInterface $route): static;
}
