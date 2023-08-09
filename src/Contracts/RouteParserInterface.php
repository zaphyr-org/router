<?php

declare(strict_types=1);

namespace Zaphyr\Route\Contracts;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface RouteParserInterface
{
    /**
     * @param string $route
     *
     * @return array<int, array<int, string|int>>
     */
    public function parse(string $route): array;
}
