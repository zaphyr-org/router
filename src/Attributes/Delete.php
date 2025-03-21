<?php

declare(strict_types=1);

namespace Zaphyr\Router\Attributes;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Delete extends Route
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
        ?string $scheme = null,
        ?string $host = null,
        ?int $port = null
    ) {
        parent::__construct($path, ['DELETE'], $name, $middlewares, $scheme, $host, $port);
    }
}
