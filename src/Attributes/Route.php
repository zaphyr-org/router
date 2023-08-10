<?php

declare(strict_types=1);

namespace Zaphyr\Route\Attributes;

use Attribute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Zaphyr\Route\Contracts\ContainerAwareInterface;
use Zaphyr\Route\Contracts\MiddlewareAwareInterface;
use Zaphyr\Route\Contracts\RouteConditionInterface;
use Zaphyr\Route\Exceptions\RouteException;
use Zaphyr\Route\Traits\ContainerAwareTrait;
use Zaphyr\Route\Traits\MiddlewareAwareTrait;
use Zaphyr\Route\Traits\RouteConditionTrait;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route implements RouteConditionInterface, MiddlewareAwareInterface, MiddlewareInterface, ContainerAwareInterface
{
    use RouteConditionTrait;
    use MiddlewareAwareTrait;
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected string $path;

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
     * @var array<string, string>
     */
    protected array $params = [];

    /**
     * @param string                               $path
     * @param string[]                             $methods
     * @param string                               $name
     * @param MiddlewareInterface[]|class-string[] $middlewares
     * @param string                               $scheme
     * @param string                               $host
     * @param int|null                             $port
     *
     * @throws RouteException
     */
    public function __construct(
        string $path,
        protected array $methods = ['GET'],
        string $name = '',
        array $middlewares = [],
        string $scheme = '',
        string $host = '',
        int|null $port = null
    ) {
        $this->setPath($path);
        $this->methods = $this->sanitizeMethods($this->methods);
        $this->name = $name;
        $this->middlewares = $middlewares;
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @param string[] $methods
     *
     * @throws RouteException
     * @return string[]
     */
    protected function sanitizeMethods(array $methods): array
    {
        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];

        if (!array_intersect($methods, $allowedMethods)) {
            throw new RouteException(
                'Invalid HTTP method(s) "' . implode(', ', $methods) . '" provided.' .
                'Allowed methods are: ' . implode(', ', $allowedMethods) . '.'
            );
        }

        return array_map('strtoupper', $methods);
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
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path): static
    {
        $this->path = '/' . trim($path, '/');

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
     * @param array<string, string> $params
     *
     * @return $this
     */
    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RouteException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $callback = $this->resolveCallable();

        return $callback($request, $this->getParams());
    }

    /**
     * @throws RouteException
     * @return mixed
     */
    protected function resolveCallable(): mixed
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
}
