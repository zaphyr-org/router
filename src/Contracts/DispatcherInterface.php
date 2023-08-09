<?php

declare(strict_types=1);

namespace Zaphyr\Route\Contracts;

use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Route\Attributes\Route;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface DispatcherInterface extends MiddlewareAwareInterface, RequestHandlerInterface
{
    /**
     * @param Route $route
     *
     * @return $this
     */
    public function addRoute(Route $route): static;
}
