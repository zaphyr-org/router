<?php

declare(strict_types=1);

namespace Zaphyr\Router\Contracts;

use Psr\Container\ContainerInterface;
use Zaphyr\Router\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ContainerAwareInterface
{
    /**
     * @param ContainerInterface $container
     *
     * @throws RouteException if the class does not implement ContainerAwareInterface
     * @return $this
     */
    public function setContainer(ContainerInterface $container): static;

    /**
     * @return ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface;
}
