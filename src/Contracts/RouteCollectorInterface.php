<?php

declare(strict_types=1);

namespace Zaphyr\Route\Contracts;

use Zaphyr\Route\Attributes\Route;
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
     * @throws RouteException
     * @return Route
     */
    public function add(string $path, array $methods, array|callable|string $callable): Route;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException
     * @return Route
     */
    public function any(string $path, array|callable|string $callable): Route;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException
     * @return Route
     */
    public function get(string $path, array|callable|string $callable): Route;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException
     * @return Route
     */
    public function post(string $path, array|callable|string $callable): Route;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException
     * @return Route
     */
    public function put(string $path, array|callable|string $callable): Route;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException
     * @return Route
     */
    public function patch(string $path, array|callable|string $callable): Route;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException
     * @return Route
     */
    public function delete(string $path, array|callable|string $callable): Route;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException
     * @return Route
     */
    public function head(string $path, array|callable|string $callable): Route;

    /**
     * @param string                                       $path
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException
     * @return Route
     */
    public function options(string $path, array|callable|string $callable): Route;
}
