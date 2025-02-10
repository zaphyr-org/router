<?php

declare(strict_types=1);

namespace Zaphyr\Router\Contracts;

use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Router\Contracts\Attributes\GroupInterface;
use Zaphyr\Router\Contracts\Attributes\RouteInterface;
use Zaphyr\Router\Exceptions\RouteException;

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
     * @template T of object
     *
     * @param class-string<T>[] $controllers
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
     * @return RouteInterface[]
     */
    public function getRoutes(): array;

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
