<?php

declare(strict_types=1);

namespace Zaphyr\Route\Traits;

use Zaphyr\Route\Attributes\Route;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
trait RouteCollectorTrait
{
    /**
     * {@inheritdoc}
     */
    abstract public function add(string $path, array $methods, array|callable|string $callable): Route;

    /**
     * {@inheritdoc}
     */
    public function any(string $path, array|callable|string $callable): Route
    {
        return $this->add(
            $path,
            ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
            $callable
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path, array|callable|string $callable): Route
    {
        return $this->add($path, ['GET'], $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $path, array|callable|string $callable): Route
    {
        return $this->add($path, ['POST'], $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, array|callable|string $callable): Route
    {
        return $this->add($path, ['PUT'], $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $path, array|callable|string $callable): Route
    {
        return $this->add($path, ['PATCH'], $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path, array|callable|string $callable): Route
    {
        return $this->add($path, ['DELETE'], $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function head(string $path, array|callable|string $callable): Route
    {
        return $this->add($path, ['HEAD'], $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function options(string $path, array|callable|string $callable): Route
    {
        return $this->add($path, ['OPTIONS'], $callable);
    }
}
