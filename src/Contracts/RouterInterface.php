<?php

declare(strict_types=1);

namespace Zaphyr\Route\Contracts;

use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Route\Contracts\Attributes\GroupInterface;
use Zaphyr\Route\Contracts\Attributes\RouteInterface;
use Zaphyr\Route\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface RouterInterface extends
    RouteCollectorInterface,
    MiddlewareAwareInterface,
    ContainerAwareInterface,
    RequestHandlerInterface
{
    /**
     * @param class-string[] $controllers
     *
     * @return void
     */
    public function setControllerRoutes(array $controllers): void;

    /**
     * @param array<string, string> $routePatterns
     *
     * @return void
     */
    public function setRoutePatterns(array $routePatterns): void;

    /**
     * @param string   $path
     * @param callable $callable
     *
     * @return GroupInterface
     */
    public function group(string $path, callable $callable): GroupInterface;

    /**
     * @param string $name
     *
     * @throws RouteException if route name does not exist
     * @return RouteInterface
     */
    public function getNamedRoute(string $name): RouteInterface;

    /**
     * @param string               $name
     * @param array<string, mixed> $params
     *
     * @throws RouteException if params are missing for URL
     * @return string
     */
    public function getPathFromName(string $name, array $params = []): string;
}
