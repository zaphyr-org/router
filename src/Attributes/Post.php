<?php

declare(strict_types=1);

namespace Zaphyr\Router\Attributes;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Post extends Route
{
    /**
     * @param string                                                    $path
     * @param string                                                    $name
     * @param MiddlewareInterface[]|class-string<MiddlewareInterface>[] $middlewares Will be renamed to "middleware" in v2.0.
     * @param string|null                                               $scheme
     * @param string|null                                               $host
     * @param int|null                                                  $port
     */
    public function __construct(
        string $path,
        string $name = '',
        array $middlewares = [],
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null
    ) {
        parent::__construct($path, ['POST'], $name, $middlewares, $scheme, $host, $port);
    }
}
