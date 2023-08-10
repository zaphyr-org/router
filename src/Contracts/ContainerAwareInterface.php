<?php

declare(strict_types=1);

namespace Zaphyr\Route\Contracts;

use Psr\Container\ContainerInterface;
use Zaphyr\Route\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ContainerAwareInterface
{
    /**
     * @param ContainerInterface $container
     *
     * @throws RouteException
     * @return $this
     */
    public function setContainer(ContainerInterface $container): static;

    /**
     * @return ContainerInterface|null
     */
    public function getContainer(): ContainerInterface|null;
}
