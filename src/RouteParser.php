<?php

declare(strict_types=1);

namespace Zaphyr\Router;

use FastRoute\RouteParser\Std as FastRouteParser;
use Zaphyr\Router\Contracts\RouteParserInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class RouteParser implements RouteParserInterface
{
    /**
     * @var FastRouteParser
     */
    protected FastRouteParser $routeParser;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->routeParser = new FastRouteParser();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $route): array
    {
        return $this->routeParser->parse($route);
    }
}
