<?php

declare(strict_types=1);

namespace Zaphyr\Route\Contracts;

use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Route\Attributes\Group;
use Zaphyr\Route\Attributes\Route;
use Zaphyr\Route\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface RouterInterface extends RouteCollectorInterface, MiddlewareAwareInterface, RequestHandlerInterface
{
    /**
     * @param class-string[] $controllers
     *
     * @return void
     */
    public function setControllerRoutes(array $controllers): void;

    /**
     * @param string   $path
     * @param callable $callable
     *
     * @return Group
     */
    public function group(string $path, callable $callable): Group;

    /**
     * @param string $name
     *
     * @throws RouteException
     * @return Route
     */
    public function getNamedRoute(string $name): Route;
}
