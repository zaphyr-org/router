<?php

declare(strict_types=1);

namespace Zaphyr\Router\Traits;

use Psr\Http\Server\MiddlewareInterface;
use Throwable;
use Zaphyr\Router\Exceptions\MiddlewareException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
trait MiddlewareAwareTrait
{
    use ContainerAwareTrait;

    /**
     * @var MiddlewareInterface[]|class-string<MiddlewareInterface>[]
     */
    protected array $middlewares = [];

    /**
     * {@inheritdoc}
     */
    public function setMiddleware(MiddlewareInterface|string|array $middleware): static
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];

        foreach ($middleware as $item) {
            $this->middlewares[] = $item;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlewares(array $middlewares): static
    {
        return $this->setMiddleware($middlewares);
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewareStack(): array
    {
        return $this->middlewares;
    }

    /**
     * {@inheritdoc}
     */
    public function shiftMiddleware(): MiddlewareInterface
    {
        $middleware = array_shift($this->middlewares);

        if ($middleware === null) {
            throw new MiddlewareException('End of middleware stack reached.');
        }

        if (is_string($middleware)) {
            $middleware = $this->resolveMiddleware($middleware);
        }

        return $middleware;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveMiddleware(MiddlewareInterface|string $middleware): MiddlewareInterface
    {
        if (is_string($middleware) && class_exists($middleware)) {
            $container = $this->getContainer();

            try {
                $middleware = $container !== null ? $container->get($middleware) : new $middleware();
            } catch (Throwable $exception) {
                throw new MiddlewareException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        $middlewareName = is_object($middleware) ? get_class($middleware) : $middleware;

        throw new MiddlewareException('Could not resolve middleware "' . $middlewareName . '"');
    }
}
