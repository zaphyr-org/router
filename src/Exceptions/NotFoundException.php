<?php

declare(strict_types=1);

namespace Zaphyr\Router\Exceptions;

use Exception;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class NotFoundException extends Exception
{
    /**
     * @var int
     */
    protected $code = 404;
}
