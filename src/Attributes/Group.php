<?php

declare(strict_types=1);

namespace Zaphyr\Router\Attributes;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;
use Zaphyr\Router\Contracts\Attributes\GroupInterface;
use Zaphyr\Router\Contracts\Attributes\RouteInterface;
use Zaphyr\Router\Contracts\RouterInterface;
use Zaphyr\Router\Traits\MiddlewareAwareTrait;
use Zaphyr\Router\Traits\RouteCollectorTrait;
use Zaphyr\Router\Traits\RouteConditionTrait;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Group implements GroupInterface
{
    use RouteCollectorTrait;
    use RouteConditionTrait;
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
     * @param string                               $scheme
     * @param string                               $host
     * @param int|null                             $port
     */
    public function __construct(
        protected string $path,
        array $middlewares = [],
        string $scheme = '',
        string $host = '',
        int|null $port = null
    ) {
        $this->path = '/' . trim($this->path, '/');
        $this->middlewares = $middlewares;
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * {@inheritdoc}
     */
    public function setCallable(callable $callable): static
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRouter(RouterInterface $router): static
    {
        $this->router = $router;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $path, array $methods, array|callable|string $callable): RouteInterface
    {
        $route = $this->router->add($this->path . $path, $methods, $callable);
        $route->setGroup($this);

        if ($this->getScheme() !== '') {
            $route->setScheme($this->getScheme());
        }

        if ($this->getHost() !== '') {
            $route->setHost($this->getHost());
        }

        if ($this->getPort() !== null) {
            $route->setPort($this->getPort());
        }

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(): void
    {
        call_user_func($this->callable, $this);
    }
}
