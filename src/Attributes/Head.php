<?php

declare(strict_types=1);

namespace Zaphyr\Router\Attributes;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Head extends Route
{
    /**
     * @param string                               $path
     * @param string                               $name
     * @param MiddlewareInterface[]|class-string[] $middlewares
     * @param string                               $scheme
     * @param string                               $host
     * @param int|null                             $port
     */
    public function __construct(
        string $path,
        string $name = '',
        array $middlewares = [],
        string $scheme = '',
        string $host = '',
        int|null $port = null
    ) {
        parent::__construct($path, ['HEAD'], $name, $middlewares, $scheme, $host, $port);
    }
}
