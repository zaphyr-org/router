<?php

declare(strict_types=1);

namespace Zaphyr\Route\Contracts;

use Zaphyr\Route\Contracts\Attributes\RouteInterface;
use Zaphyr\Route\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface RouteCollectorInterface
{
    /**
     * @param string                                       $path
     * @param string[]                                     $methods
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return RouteInterface
     */
    public function add(string $path, array $methods, array|callable|string $callable): RouteInterface;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return RouteInterface
     */
    public function any(string $path, array|callable|string $callable): RouteInterface;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return RouteInterface
     */
    public function get(string $path, array|callable|string $callable): RouteInterface;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return RouteInterface
     */
    public function post(string $path, array|callable|string $callable): RouteInterface;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return RouteInterface
     */
    public function put(string $path, array|callable|string $callable): RouteInterface;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return RouteInterface
     */
    public function patch(string $path, array|callable|string $callable): RouteInterface;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return RouteInterface
     */
    public function delete(string $path, array|callable|string $callable): RouteInterface;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return RouteInterface
     */
    public function head(string $path, array|callable|string $callable): RouteInterface;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return RouteInterface
     */
    public function options(string $path, array|callable|string $callable): RouteInterface;
}
