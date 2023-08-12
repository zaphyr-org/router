<?php

declare(strict_types=1);

namespace Zaphyr\Router\Contracts\Attributes;

use Zaphyr\Router\Contracts\MiddlewareAwareInterface;
use Zaphyr\Router\Contracts\RouteCollectorInterface;
use Zaphyr\Router\Contracts\RouteConditionInterface;
use Zaphyr\Router\Contracts\RouterInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface GroupInterface extends RouteCollectorInterface, RouteConditionInterface, MiddlewareAwareInterface
{
    /**
     * @param callable $callable
     *
     * @return $this
     */
    public function setCallable(callable $callable): static;

    /**
     * @param RouterInterface $router
     *
     * @return $this
     */
    public function setRouter(RouterInterface $router): static;

    /**
     * @return void
     */
    public function __invoke(): void;
}
