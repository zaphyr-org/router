<?php

declare(strict_types=1);

namespace Zaphyr\Router\Traits;

use Psr\Container\ContainerInterface;
use Zaphyr\Router\Contracts\ContainerAwareInterface;
use Zaphyr\Router\Exceptions\RouteException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
trait ContainerAwareTrait
{
    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container = null;

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
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }
}
