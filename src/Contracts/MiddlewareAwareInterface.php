<?php

declare(strict_types=1);

namespace Zaphyr\Router\Contracts;

use Psr\Http\Server\MiddlewareInterface;
use Zaphyr\Router\Exceptions\MiddlewareException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface MiddlewareAwareInterface
{
    /**
     * @param MiddlewareInterface|class-string $middleware
     *
     * @return $this
     */
    public function addMiddleware(MiddlewareInterface|string $middleware): static;

    /**
     * @param MiddlewareInterface[]|class-string[] $middlewares
     *
     * @return $this
     */
    public function addMiddlewares(array $middlewares): static;

    /**
     * @return MiddlewareInterface[]|class-string[]
     */
    public function getMiddlewareStack(): array;

    /**
     * @throws MiddlewareException if the middleware is not callable
     * @return MiddlewareInterface
     */
    public function shiftMiddleware(): MiddlewareInterface;

    /**
     * @param MiddlewareInterface|class-string $middleware
     *
     * @throws MiddlewareException if the middleware could not be resolved
     * @return MiddlewareInterface
     */
    public function resolveMiddleware(MiddlewareInterface|string $middleware): MiddlewareInterface;
}
