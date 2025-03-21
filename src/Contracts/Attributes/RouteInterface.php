<?php

declare(strict_types=1);

namespace Zaphyr\Router\Contracts\Attributes;

use Psr\Http\Server\MiddlewareInterface;
use Zaphyr\Router\Contracts\ContainerAwareInterface;
use Zaphyr\Router\Contracts\MiddlewareAwareInterface;
use Zaphyr\Router\Contracts\RouteConditionInterface;
use Zaphyr\Router\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface RouteInterface extends
    RouteConditionInterface,
    MiddlewareAwareInterface,
    MiddlewareInterface,
    ContainerAwareInterface
{
    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path): static;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string[] $methods
     *
     * @throws RouteException if the given HTTP method is invalid
     * @return $this
     */
    public function setMethods(array $methods): static;

    /**
     * @return string[]
     */
    public function getMethods(): array;

    /**
     * @param callable|string|array<string|object, string>|array{class-string, non-empty-string} $callable
     *
     * @return $this
     */
    public function setCallable(array|callable|string $callable): static;

    /**
     * @throws RouteException if the callable is not valid or not recognized by the container
     * @return mixed
     */
    public function getCallable(): mixed;

    /**
     * @throws RouteException if the callable is not valid
     * @return string
     */
    public function getCallableName(): string;

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): static;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param GroupInterface $group
     *
     * @return $this
     */
    public function setGroup(GroupInterface $group): static;

    /**
     * @return GroupInterface|null
     */
    public function getGroup(): ?GroupInterface;

    /**
     * @param array<string, string> $params
     *
     * @return $this
     */
    public function setParams(array $params): static;

    /**
     * @return array<string, string>
     */
    public function getParams(): array;
}
