<?php

declare(strict_types=1);

namespace Zaphyr\Route\Attributes;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Any extends Route
{
    /**
     * @param string                               $path
     * @param string                               $name
     * @param MiddlewareInterface[]|class-string[] $middlewares
     */
    public function __construct(string $path, string $name = '', array $middlewares = [])
    {
        parent::__construct($path, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], $name, $middlewares);
    }
}
