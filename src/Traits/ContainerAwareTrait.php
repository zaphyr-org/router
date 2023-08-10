<?php

declare(strict_types=1);

namespace Zaphyr\Route\Traits;

use Psr\Container\ContainerInterface;
use Zaphyr\Route\Contracts\ContainerAwareInterface;
use Zaphyr\Route\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
trait ContainerAwareTrait
{
    /**
     * @var ContainerInterface|null
     */
    protected ContainerInterface|null $container = null;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container): static
    {
        $this->container = $container;

        if ($this instanceof ContainerAwareInterface) {
            return $this;
        }

        throw new RouteException(
            'Attempt to use "' . ContainerAwareTrait::class . '" without implementing "' .
            ContainerAwareInterface::class . '"'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ContainerInterface|null
    {
        return $this->container;
    }
}
