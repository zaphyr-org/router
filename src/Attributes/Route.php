<?php

declare(strict_types=1);

namespace Zaphyr\Router\Attributes;

use Attribute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Zaphyr\Router\Contracts\Attributes\GroupInterface;
use Zaphyr\Router\Contracts\Attributes\RouteInterface;
use Zaphyr\Router\Exceptions\RouteException;
use Zaphyr\Router\Traits\ContainerAwareTrait;
use Zaphyr\Router\Traits\MiddlewareAwareTrait;
use Zaphyr\Router\Traits\RouteConditionTrait;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route implements RouteInterface
{
    use RouteConditionTrait;
    use MiddlewareAwareTrait;
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected string $path;

    /**
     * @var string[]
     */
    protected array $methods;

    /**
     * @var callable|string|array<string|object, string>
     */
    protected $callable;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var GroupInterface|null
     */
    protected GroupInterface|null $group = null;

    /**
     * @var array<string, string>
     */
    protected array $params = [];

    /**
     * @param string                               $path
     * @param string[]                             $methods
     * @param string                               $name
     * @param MiddlewareInterface[]|class-string[] $middlewares Will be renamed to "middleware" in v2.0.
     * @param string                               $scheme
     * @param string                               $host
     * @param int|null                             $port
     *
     * @throws RouteException if the given HTTP method is invalid
     */
    public function __construct(
        string $path,
        array $methods = ['GET'],
        string $name = '',
        array $middlewares = [],
        string $scheme = '',
        string $host = '',
        int|null $port = null
    ) {
        $this->setPath($path);
        $this->setMethods($methods);
        $this->name = $name;
        $this->middlewares = $middlewares;
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(string $path): static
    {
        $this->path = '/' . trim($path, '/');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function setMethods(array $methods): static
    {
        $this->methods = $this->sanitizeMethods($methods);

        return $this;
    }

    /**
     * @param string[] $methods
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return string[]
     */
    protected function sanitizeMethods(array $methods): array
    {
        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
        $methods = array_map('strtoupper', $methods);

        if (!array_intersect($methods, $allowedMethods)) {
            throw new RouteException(
                'Invalid HTTP method(s) "' . implode(', ', $methods) . '" provided. ' .
                'Allowed methods are: ' . implode(', ', $allowedMethods) . '.'
            );
        }

        return $methods;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * {@inheritdoc}
     */
    public function setCallable(array|callable|string $callable): static
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallable(): mixed
    {
        $callable = $this->callable;
        $container = $this->getContainer();

        if (is_callable($callable)) {
            return $callable;
        }

        if (is_string($callable) && method_exists($callable, '__invoke')) {
            try {
                return $container !== null ? $container->get($callable) : new $callable();
            } catch (Throwable $exception) {
                throw new RouteException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }

        if (is_string($callable) && str_contains($callable, '@')) {
            $callable = explode('@', $callable);
        }

        if (is_array($callable) && isset($callable[0])) {
            try {
                $class = $container !== null ? $container->get($callable[0]) : new $callable[0]();
            } catch (Throwable $exception) {
                throw new RouteException($exception->getMessage(), $exception->getCode(), $exception);
            }

            return [$class, $callable[1]];
        }

        throw new RouteException(
            'Could not resolve a callable for route "' . $this->getPath() . '" with methods "'
            . implode(', ', $this->getMethods()) . '"'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup(GroupInterface $group): static
    {
        $this->group = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup(): GroupInterface|null
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RouteException if the route callable is not valid or not resolvable by the container
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $callback = $this->getCallable();

        return $callback($request, $this->getParams());
    }
}
