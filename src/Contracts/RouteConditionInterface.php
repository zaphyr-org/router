<?php

declare(strict_types=1);

namespace Zaphyr\Route\Contracts;

use Zaphyr\Route\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface RouteConditionInterface
{
    /**
     * @param string $scheme
     *
     * @throws RouteException
     * @return $this
     */
    public function setScheme(string $scheme): static;

    /**
     * @return string
     */
    public function getScheme(): string;

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost(string $host): static;

    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @param int $port
     *
     * @throws RouteException
     * @return $this
     */
    public function setPort(int $port): static;

    /**
     * @return int|null
     */
    public function getPort(): int|null;
}
