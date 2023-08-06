<?php

declare(strict_types=1);

namespace Zaphyr\Route\Attributes;

use Attribute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Route\Contracts\MiddlewareAwareInterface;
use Zaphyr\Route\Exceptions\RouteException;
use Zaphyr\Route\Traits\MiddlewareAwareTrait;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route implements MiddlewareAwareInterface, MiddlewareInterface
{
    use MiddlewareAwareTrait;

    /**
     * @var callable|string|array<string|object, string>
     */
    protected $callable;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var Group|null
     */
    protected Group|null $group = null;

    /**
     * @param string                               $path
     * @param string[]                             $methods
     * @param string                               $name
     * @param MiddlewareInterface[]|class-string[] $middlewares
     */
    public function __construct(
        protected string $path,
        protected array $methods = ['GET'],
        string $name = '',
        array $middlewares = [],
    ) {
        $this->path = '/' . trim($this->path, '/');
        $this->name = $name;
        $this->middlewares = $middlewares;
    }

    /**
     * @param callable|string|array<string|object, string> $callable
     *
     * @return $this
     */
    public function setCallable(array|callable|string $callable): static
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Group $group
     *
     * @return $this
     */
    public function setGroup(Group $group): static
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Group|null
     */
    public function getGroup(): Group|null
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RouteException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $callback = $this->resolveCallable($this->callable);

        return $callback($request);
    }

    /**
     * @param callable|string|array<string|object, string> $callable
     *
     * @throws RouteException
     * @return mixed
     */
    protected function resolveCallable(array|callable|string $callable): mixed
    {
        if (is_callable($callable)) {
            return $callable;
        }

        if (is_string($callable) && method_exists($callable, '__invoke')) {
            return new $callable();
        }

        if (is_string($callable) && str_contains($callable, '@')) {
            $callable = explode('@', $callable);
        }

        if (is_array($callable) && isset($callable[0])) {
            return [new $callable[0](), $callable[1]];
        }

        throw new RouteException(
            'Could not resolve a callable for route "' . $this->getPath() . '" with methods "'
            . implode(', ', $this->getMethods()) . '"'
        );
    }
}
