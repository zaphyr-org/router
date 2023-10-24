<?php

declare(strict_types=1);

namespace Zaphyr\Router\Contracts;

use Zaphyr\Router\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface RouteConditionInterface
{
    /**
     * @param string $scheme
     *
     * @throws RouteException If the URI scheme is not allowed
     * @return $this
     */
    public function setScheme(string $scheme): static;

    /**
     * @return string|null
     */
    public function getScheme(): string|null;

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost(string $host): static;

    /**
     * @return string|null
     */
    public function getHost(): string|null;

    /**
     * @param int $port
     *
     * @throws RouteException if the port is not valid
     * @return $this
     */
    public function setPort(int $port): static;

    /**
     * @return int|null
     */
    public function getPort(): int|null;
}
