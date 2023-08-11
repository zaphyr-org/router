<?php

declare(strict_types=1);

namespace Zaphyr\Route\Contracts\Attributes;

use Zaphyr\Route\Contracts\MiddlewareAwareInterface;
use Zaphyr\Route\Contracts\RouteCollectorInterface;
use Zaphyr\Route\Contracts\RouteConditionInterface;
use Zaphyr\Route\Contracts\RouterInterface;

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
