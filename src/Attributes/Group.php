<?php

declare(strict_types=1);

namespace Zaphyr\Route\Attributes;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;
use Zaphyr\Route\Contracts\MiddlewareAwareInterface;
use Zaphyr\Route\Contracts\RouteCollectorInterface;
use Zaphyr\Route\Contracts\RouterInterface;
use Zaphyr\Route\Traits\MiddlewareAwareTrait;
use Zaphyr\Route\Traits\RouteCollectorTrait;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Group implements RouteCollectorInterface, MiddlewareAwareInterface
{
    use RouteCollectorTrait;
    use MiddlewareAwareTrait;

    /**
     * @var callable
     */
    protected $callable;

    /**
     * @var RouterInterface
     */
    protected RouterInterface $router;

    /**
     * @param string                               $path
     * @param MiddlewareInterface[]|class-string[] $middlewares
     */
    public function __construct(protected string $path, array $middlewares = [])
    {
        $this->path = '/' . trim($this->path, '/');
        $this->middlewares = $middlewares;
    }

    /**
     * @param callable $callable
     *
     * @return $this
     */
    public function setCallable(callable $callable): static
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * @param RouterInterface $router
     *
     * @return $this
     */
    public function setRouter(RouterInterface $router): static
    {
        $this->router = $router;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function add(
        string $path,
        array $methods,
        array|callable|string $callable
    ): Route {
        $route = $this->router->add($this->path . $path, $methods, $callable);
        $route->setGroup($this);

        return $route;
    }

    /**
     * @return void
     */
    public function __invoke(): void
    {
        call_user_func($this->callable, $this);
    }
}
